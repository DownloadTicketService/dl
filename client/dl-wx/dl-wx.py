#!/usr/bin/env python
import configobj
import validate
import argparse
import os.path
import copy
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
        self.PostCreate(self.xrc.LoadDialog(None, 'preferences'))
        self.Bind(wx.EVT_CLOSE, self.on_close)
        self.url = xrc.XRCCTRL(self, 'url')
        self.username = xrc.XRCCTRL(self, 'username')
        self.password = xrc.XRCCTRL(self, 'password')
        self.verify = xrc.XRCCTRL(self, 'verify')
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
            error = "The REST URL is mandatory"
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
        self.PostCreate(self.xrc.LoadDialog(None, 'upload'))
        self.Bind(wx.EVT_CLOSE, self.on_cancel)
        self.file = xrc.XRCCTRL(self, 'file')
        self.gauge = xrc.XRCCTRL(self, 'progress')
        self.status = xrc.XRCCTRL(self, 'status')
        self.request = dl.new_ticket(file, params, async=True,
                                     complete_fn=self.completed,
                                     failed_fn=self.failed,
                                     progress_fn=self.progress)
        self.file.SetLabel(os.path.basename(file))
        self.action = xrc.XRCCTRL(self, 'action')
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
            error = str(ex)
            self.status.SetLabel(error)
            self.action.SetLabel("Close")
            self.action.Bind(wx.EVT_BUTTON, self.on_close)
            self.Bind(wx.EVT_CLOSE, self.on_close)
            self.Fit()
            wx.MessageBox(error, 'Upload error', wx.OK | wx.ICON_ERROR)

    def failed(self, ex):
        wx.CallAfter(self.on_failed, ex)

    def on_copy(self, evt=None):
        wx.TheClipboard.Open()
        wx.TheClipboard.SetData(wx.TextDataObject(self.url))
        wx.TheClipboard.Close()
        self.Destroy()


class NewTicket(wx.Dialog):
    def __init__(self, dl, ticket_params, change_fn):
        self.dl = dl
        self.def_ticket_params = ticket_params
        self.change_fn = change_fn
        self.xrc = xrc.XmlResource('newticket.xrc')
        self.PostCreate(self.xrc.LoadDialog(None, 'newticket'))
        self.Bind(wx.EVT_CLOSE, self.on_close)
        self.file = xrc.XRCCTRL(self, 'file')
        self.perm = xrc.XRCCTRL(self, 'perm')
        self.perm.Bind(wx.EVT_CHECKBOX, self.on_perm)
        self.total_days = xrc.XRCCTRL(self, 'total_days')
        self.hours_after_dl = xrc.XRCCTRL(self, 'hours_after_dl')
        self.downloads = xrc.XRCCTRL(self, 'downloads')
        self.upload = xrc.XRCCTRL(self, 'upload')
        self.upload.Bind(wx.EVT_BUTTON, self.on_upload)
        self.set_defaults = xrc.XRCCTRL(self, 'set_defaults')
        self.set_defaults.Bind(wx.EVT_BUTTON, self.on_set_defaults)
        self.ticket_params = copy.copy(self.def_ticket_params)
        self.set_ticket_params(self.ticket_params)
        self.Show()

    def on_perm(self, evt=None):
        enable = not self.perm.GetValue()
        self.total_days.Enable(enable)
        self.hours_after_dl.Enable(enable)
        self.downloads.Enable(enable)

    def set_ticket_params(self, ticket_params):
        self.perm.SetValue(ticket_params.perm)
        self.total_days.SetValue(ticket_params.total_days)
        self.hours_after_dl.SetValue(ticket_params.hours_after_dl)
        self.downloads.SetValue(ticket_params.downloads)
        self.on_perm()

    def get_ticket_params(self, ticket_params):
        ticket_params.perm = self.perm.GetValue()
        ticket_params.total_days = self.total_days.GetValue()
        ticket_params.hours_after_dl = self.hours_after_dl.GetValue()
        ticket_params.downloads = self.downloads.GetValue()

    def on_set_defaults(self, evt):
        self.get_ticket_params(self.def_ticket_params)
        self.change_fn()

    def on_upload(self, evt):
        path = self.file.GetPath().encode('utf8')
        if not len(path):
            wx.MessageBox('Please select a file!', 'New Ticket', wx.OK | wx.ICON_ERROR)
        else:
            self.get_ticket_params(self.ticket_params)
            Upload(path, self.dl, self.ticket_params)
            self.on_close()

    def on_close(self, evt=None):
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

        self.ticket_params = TicketParams()
        self.ticket_params.perm = v.check('boolean', self.cfg.get('perm', False))
        self.ticket_params.total_days = v.check('integer', self.cfg.get('total_days', 7))
        self.ticket_params.hours_after_dl = v.check('integer', self.cfg.get('hours_after_dl', 24))
        self.ticket_params.downloads = v.check('integer', self.cfg.get('downloads', 0))

    def save_prefs(self):
        self.cfg['url'] = self.dl.service.url
        self.cfg['user'] = self.dl.service.username
        self.cfg['pass'] = self.dl.service.password
        self.cfg['verify'] = self.dl.service.verify
        self.cfg['perm'] = self.ticket_params.perm
        self.cfg['total_days'] = self.ticket_params.total_days
        self.cfg['hours_after_dl'] = self.ticket_params.hours_after_dl
        self.cfg['downloads'] = self.ticket_params.downloads
        self.cfg.write()

    def express_ticket(self, evt=None):
        path = wx.FileSelector(flags=wx.FILE_MUST_EXIST).encode('utf8')
        if len(path):
            Upload(path, self.dl, self.ticket_params)

    def new_ticket(self, evt=None):
        NewTicket(self.dl, self.ticket_params, self.save_prefs)

    def show_prefs(self, evt=None):
        self.prefs.Show()

    def quit(self, evt=None):
        self.ExitMainLoop()


if __name__ == '__main__':
    main = DLApp()
    main.MainLoop()
