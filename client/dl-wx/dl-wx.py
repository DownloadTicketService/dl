#!/usr/bin/env python
import configobj
import validate
import argparse
import os.path
import wx
import wx.xrc as xrc
from dl import *

DL_VERSION = "0.10"
DL_AGENT = "dl-wx/" + DL_VERSION
DL_DESCRIPTION = "Download Ticket Service"
DL_ICON = "dl-icon.ico"
DL_RC = "~/.dl.rc"


def create_menu_item(menu, label, func, id=wx.ID_ANY):
    item = wx.MenuItem(menu, id, label)
    menu.Bind(wx.EVT_MENU, func, item)
    menu.AppendItem(item)
    return item


class TaskBarIcon(wx.TaskBarIcon):
    def __init__(self, dlapp):
        super(TaskBarIcon, self).__init__()
        self.dlapp = dlapp
        self.SetIcon(wx.IconFromBitmap(wx.Bitmap(DL_ICON)), DL_DESCRIPTION)
        self.Bind(wx.EVT_TASKBAR_LEFT_UP, self.dlapp.express_ticket)

    def CreatePopupMenu(self):
        menu = wx.Menu()
        create_menu_item(menu, "New Ticket", self.dlapp.new_ticket, wx.ID_NEW)
        create_menu_item(menu, "Preferences", self.dlapp.show_prefs, wx.ID_PREFERENCES)
        menu.AppendSeparator()
        create_menu_item(menu, "Quit", self.dlapp.quit, wx.ID_EXIT)
        return menu


class Prefs(wx.Dialog):
    def __init__(self, service, change_fn):
        self.service = service
        self.change_fn = change_fn
        self.xrc = xrc.XmlResource('preferences.xrc')
        self.prefs = self.xrc.LoadDialog(None, 'preferences')
        self.url = xrc.XRCCTRL(self.prefs, 'url')
        self.username = xrc.XRCCTRL(self.prefs, 'username')
        self.password = xrc.XRCCTRL(self.prefs, 'password')
        self.verify = xrc.XRCCTRL(self.prefs, 'verify')
        self.PostCreate(self.prefs)
        self.Bind(wx.EVT_CLOSE, self.on_close)
        self.set_service()

    def set_service(self):
        self.url.SetValue(self.service.url)
        self.username.SetValue(self.service.username)
        self.password.SetValue(self.service.password)
        self.verify.SetValue(self.service.verify)

    def on_close(self, evt):
        service = Service()
        service.url = self.url.GetValue().encode('utf8')
        service.username = self.username.GetValue().encode('utf8')
        service.password = self.password.GetValue().encode('utf8')
        service.verify = self.verify.GetValue()

        error = None
        if not len(service.url):
            error = "The REST URL is mandatory)"
        elif not len(service.username):
            error = "The username is mandatory"
        elif not len(service.password):
            error = "The password is mandatory"
        if error is not None:
            wx.MessageBox(error, 'Preferences', wx.OK | wx.ICON_ERROR)
        else:
            self.Hide()
            self.service.url = service.url
            self.service.username = service.username
            self.service.password = service.password
            self.service.verify = service.verify
            self.change_fn()


class Upload(wx.Dialog):
    def __init__(self, file, dl, params):
        self.xrc = xrc.XmlResource('upload.xrc')
        self.upload = self.xrc.LoadDialog(None, 'upload')
        self.file = xrc.XRCCTRL(self.upload, 'file')
        self.gauge = xrc.XRCCTRL(self.upload, 'progress')
        self.status = xrc.XRCCTRL(self.upload, 'status')
        self.action = xrc.XRCCTRL(self.upload, 'action')
        self.PostCreate(self.upload)
        self.Bind(wx.EVT_CLOSE, self.on_cancel)
        self.request = dl.new_ticket(file, TicketParams(), async=True,
                                     complete_fn=self.completed,
                                     failed_fn=self.failed,
                                     progress_fn=self.progress)
        self.file.SetLabel(os.path.basename(file))
        self.action.SetLabel("Cancel")
        self.action.Bind(wx.EVT_BUTTON, self.on_cancel)
        self.Fit()
        self.Show()
        self.request.start()

    def on_cancel(self, evt):
        self.status.SetLabel("Cancelling upload ...")
        self.request.cancel()

    def on_close(self, evt=None):
        self.Destroy()

    def on_progress(self, upload_t, upload_d, upload_s):
        prc = upload_d * 100 / upload_t
        ks = upload_s / 1024
        self.gauge.SetValue(prc)
        self.status.SetLabel("Uploading ({:.1f}%, {:.1f}KiB/s) ...".format(prc, ks))

    def progress(self, download_t, download_d, download_s, upload_t, upload_d, upload_s):
        if upload_d > 0:
            wx.CallAfter(self.on_progress, upload_t, upload_d, upload_s)

    def on_completed(self, ret):
        self.url = ret['url']
        self.status.SetLabel(self.url)
        self.action.SetLabel("Copy")
        self.action.Bind(wx.EVT_BUTTON, self.on_copy)
        self.Bind(wx.EVT_CLOSE, self.on_close)
        self.Fit()

    def completed(self, ret):
        wx.CallAfter(self.on_completed, ret)

    def on_failed(self, ex):
        if ex is None:
            self.on_close()
        else:
            self.status.SetLabel(str(ex))
            self.action.SetLabel("Close")
            self.action.Bind(wx.EVT_BUTTON, self.on_close)
            self.Bind(wx.EVT_CLOSE, self.on_close)

    def failed(self, ex):
        wx.CallAfter(self.on_failed, ex)

    def on_copy(self, evt=None):
        wx.TheClipboard.Open()
        wx.TheClipboard.SetData(wx.TextDataObject(self.url))
        wx.TheClipboard.Close()
        self.Destroy()


class DLApp(wx.App):
    def OnInit(self):
        self.dl = DL()

        self.load_prefs()
        self.prefs = Prefs(self.dl.service, self.save_prefs)
        if not len(self.dl.service.url):
            wx.MessageBox('This is the first time you run ' + DL_DESCRIPTION +
                          '. You need to configure it first.',
                          'Preferences', wx.OK | wx.ICON_INFORMATION)
            self.prefs.ShowModal()

        self.tbi = TaskBarIcon(self)
        return True

    def load_prefs(self):
        cfgpath = os.path.expanduser(DL_RC)
        self.cfg = configobj.ConfigObj(cfgpath)

        v = validate.Validator()
        self.dl.service.url = v.check('string', self.cfg.get('url', ''))
        self.dl.service.username = v.check('string', self.cfg.get('user', ''))
        self.dl.service.password = v.check('string', self.cfg.get('pass', ''))
        self.dl.service.verify = v.check('boolean', self.cfg.get('verify', True))
        self.dl.service.agent = DL_AGENT

    def save_prefs(self):
        self.cfg['url'] = self.dl.service.url;
        self.cfg['user'] = self.dl.service.username;
        self.cfg['pass'] = self.dl.service.password;
        self.cfg['verify'] = self.dl.service.verify;
        self.cfg.write()

    def express_ticket(self, evt=None):
        path = wx.FileSelector(flags=wx.FILE_MUST_EXIST)
        if len(path):
            path = path.encode('utf8')
            Upload(path, self.dl, TicketParams())

    def new_ticket(self, evt=None):
        # TODO: this should prompt for ticket parameters
        self.express_ticket()

    def show_prefs(self, evt=None):
        self.prefs.Show()

    def quit(self, evt=None):
        self.ExitMainLoop()


if __name__ == '__main__':
    main = DLApp()
    main.MainLoop()
