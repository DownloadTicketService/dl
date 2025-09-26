#!/usr/bin/env python3
from __future__ import unicode_literals, print_function, generators, absolute_import

import configobj
import validate
import os.path
import copy
import time
import sys
import wx
import wx.adv as adv
import wx.xrc as xrc
from dl import *

DL_VERSION = "0.18"
DL_AGENT = "dl-wx/" + DL_VERSION
DL_DESCRIPTION = "Download Ticket Service"
DL_URL = "https://www.thregr.org/~wavexx/software/dl/"
DL_ICON = "dl-icon.ico"
DL_RC = "~/.dl.rc"

# This is rather ugly for *NIX (should use pkg_resources)
if '_MEIPASS2' in os.environ:
    RC_PATH = os.environ['_MEIPASS2']
else:
    RC_PATH = os.path.dirname(sys.argv[0])


def create_menu_item(menu, label, func, id=wx.ID_ANY):
    item = wx.MenuItem(menu, id, label)
    menu.Bind(wx.EVT_MENU, func, item)
    menu.Append(item)  # Phoenix: AppendItem removed
    return item


class TaskBarIcon(adv.TaskBarIcon):
    def __init__(self, dlapp):
        super(TaskBarIcon, self).__init__()
        self.dlapp = dlapp
        img = wx.Bitmap(os.path.join(RC_PATH, DL_ICON))
        icon = wx.Icon()
        icon.CopyFromBitmap(img)  # Phoenix replacement for IconFromBitmap
        self.SetIcon(icon, DL_DESCRIPTION)
        #self.Bind(wx.EVT_TASKBAR_LEFT_DOWN, self.dlapp.express_ticket)
        event = getattr(adv, "EVT_TASKBAR_LEFT_DOWN",
        getattr(adv, "EVT_TASKBAR_LEFT_UP", None))
        if event:
         self.Bind(event, self.dlapp.express_ticket)
        if hasattr(adv, "EVT_TASKBAR_LEFT_DCLICK"):
         self.Bind(adv.EVT_TASKBAR_LEFT_DCLICK, self.dlapp.express_ticket)

    def CreatePopupMenu(self):
        menu = wx.Menu()
        create_menu_item(menu, "New Ticket", self.dlapp.new_ticket, wx.ID_NEW)
        create_menu_item(menu, "New Grant", self.dlapp.new_grant)
        create_menu_item(menu, "Preferences", self.dlapp.show_prefs, wx.ID_PREFERENCES)
        menu.AppendSeparator()
        create_menu_item(menu, "About", self.dlapp.show_about, wx.ID_ABOUT)
        create_menu_item(menu, "Quit", self.dlapp.quit, wx.ID_EXIT)
        return menu


class Preferences(object):
    def __init__(self, service=None, ticket_params=None,
                 grant_params=None, email=None):
        self.service = Service() if service is None else service
        self.ticket_params = TicketParams() if ticket_params is None else ticket_params
        self.grant_params = GrantParams() if grant_params is None else grant_params
        self.email = email


# ---------- Dialog wrappers (composition, no subclassing) ----------

class PrefDialog(object):
    def __init__(self, prefs, change_fn):
        self.prefs = prefs
        self.change_fn = change_fn
        self.xrc = xrc.XmlResource(os.path.join(RC_PATH, 'preferences.xrc'))
        self.dlg = self.xrc.LoadDialog(None, 'preferences')

        # Controls
        self.url = xrc.XRCCTRL(self.dlg, 'url')
        self.username = xrc.XRCCTRL(self.dlg, 'username')
        self.password = xrc.XRCCTRL(self.dlg, 'password')
        self.verify = xrc.XRCCTRL(self.dlg, 'verify')
        self.email = xrc.XRCCTRL(self.dlg, 'email')
        self.cancel = xrc.XRCCTRL(self.dlg, 'cancel')
        self.save = xrc.XRCCTRL(self.dlg, 'save')

        # Events
        self.dlg.Bind(wx.EVT_SHOW, self.on_show)
        self.cancel.Bind(wx.EVT_BUTTON, self.on_close)
        self.save.Bind(wx.EVT_BUTTON, self.on_save)

        if prefs.service.url:
            self.cancel.Show()
            self.dlg.Bind(wx.EVT_CLOSE, self.on_close)
        else:
            self.cancel.Hide()
            self.dlg.Bind(wx.EVT_CLOSE, self.on_save)

        self.dlg.Fit()

    # delegate helpers
    def Show(self): return self.dlg.Show()
    def ShowModal(self): return self.dlg.ShowModal()
    def Hide(self): return self.dlg.Hide()
    def Bind(self, evt, fn): return self.dlg.Bind(evt, fn)
    def Destroy(self): return self.dlg.Destroy()

    def on_show(self, evt):
        self.set_prefs()

    def set_prefs(self):
        self.url.SetValue(self.prefs.service.url or "")
        self.username.SetValue(self.prefs.service.username or "")
        self.password.SetValue(self.prefs.service.password or "")
        self.verify.SetValue(bool(self.prefs.service.verify))
        self.email.SetValue(self.prefs.email or "")

    def get_prefs(self, prefs):
        prefs.service.url = self.url.GetValue()
        prefs.service.username = self.username.GetValue()
        prefs.service.password = self.password.GetValue()
        prefs.service.verify = self.verify.GetValue()
        prefs.email = self.email.GetValue()

    def on_close(self, evt=None):
        self.Hide()

    def on_save(self, evt):
        prefs = Preferences()
        self.get_prefs(prefs)
        error = None
        if not prefs.service.url:
            error = "The REST URL is mandatory"
        elif not prefs.service.username:
            error = "The username is mandatory"
        elif not prefs.service.password:
            error = "The password is mandatory"
        elif not prefs.email:
            error = "The e-mail is mandatory"
        if error is not None:
            wx.MessageBox(error, 'Preferences', wx.OK | wx.ICON_ERROR)
        else:
            self.get_prefs(self.prefs)
            self.change_fn()
            self.on_close()


class Upload(object):
    def __init__(self, file, dl, params):
        self.xrc = xrc.XmlResource(os.path.join(RC_PATH, 'upload.xrc'))
        self.dlg = self.xrc.LoadDialog(None, 'upload')

        self.dlg.Bind(wx.EVT_CLOSE, self.on_cancel)
        self.descr = xrc.XRCCTRL(self.dlg, 'descr')
        self.gauge = xrc.XRCCTRL(self.dlg, 'gauge')
        self.status = xrc.XRCCTRL(self.dlg, 'status')
        self.action = xrc.XRCCTRL(self.dlg, 'action')

        self.status.SetLabel("Starting upload ...")
        self.descr.SetLabel(os.path.basename(file))
        self.action.SetLabel("Cancel")
        self.action.Bind(wx.EVT_BUTTON, self.on_cancel)

        self.stamp = time.time()
        self.timer = wx.Timer(self.dlg)  # parented to dialog
        self.timer.Bind(wx.EVT_TIMER, lambda _: self.gauge.Pulse())
        self.timer.Start(100)

        # Start async request
        self.request = dl.new_ticket(file, params, asynchronous=True,
                                     complete_fn=self.completed,
                                     failed_fn=self.failed,
                                     progress_fn=self.progress)

        self.dlg.Fit()
        self.dlg.Show()
        self.request.start()

    # delegate helpers
    def Show(self): return self.dlg.Show()
    def Hide(self): return self.dlg.Hide()
    def Destroy(self): return self.dlg.Destroy()
    def Bind(self, evt, fn): return self.dlg.Bind(evt, fn)

    def on_cancel(self, evt):
        self.status.SetLabel("Cancelling upload ...")
        self.request.cancel()
        if not self.timer.IsRunning():
            self.timer.Start()

    def on_close(self, evt=None):
        if self.timer.IsRunning():
            self.timer.Stop()
        self.Destroy()

    def on_progress(self, upload_t, upload_d, upload_s):
        if not upload_t:
            return
        prc = int(upload_d * 100 / upload_t)
        ks = upload_s / 1024.0
        if self.timer.IsRunning():
            self.timer.Stop()
            self.gauge.SetRange(100)
        self.gauge.SetValue(prc)
        stamp = time.time()
        if stamp - self.stamp >= 1:
            self.stamp = stamp
            self.status.SetLabel("Uploading ({:.1f}%, {:.1f}KiB/s) ...".format(prc, ks))

    def progress(self, download_t, download_d, download_s, upload_t, upload_d, upload_s):
        if upload_d > 0:
            wx.CallAfter(self.on_progress, upload_t, upload_d, upload_s)

    def on_completed(self, ret):
        self.url = ret['url']
        self.status.SetLabel(self.url)
        self.action.SetLabel("Copy")
        self.action.Bind(wx.EVT_BUTTON, self.on_copy)
        if self.timer.IsRunning():
            self.timer.Stop()
        self.gauge.SetRange(100)
        self.gauge.SetValue(100)
        self.dlg.Bind(wx.EVT_CLOSE, self.on_close)
        self.dlg.Fit()

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
            if self.timer.IsRunning():
                self.timer.Stop()
            self.dlg.Bind(wx.EVT_CLOSE, self.on_close)
            self.dlg.Fit()
            wx.MessageBox(error, 'Upload error', wx.OK | wx.ICON_ERROR)

    def failed(self, ex):
        wx.CallAfter(self.on_failed, ex)

    def on_copy(self, evt=None):
        if wx.TheClipboard.Open():
            try:
                wx.TheClipboard.SetData(wx.TextDataObject(self.url))
            finally:
                wx.TheClipboard.Close()
        self.on_close()


class Grant(object):
    def __init__(self, email, dl, params):
        self.xrc = xrc.XmlResource(os.path.join(RC_PATH, 'grant.xrc'))
        self.dlg = self.xrc.LoadDialog(None, 'grant')

        self.dlg.Bind(wx.EVT_CLOSE, self.on_cancel)
        self.status = xrc.XRCCTRL(self.dlg, 'status')
        self.action = xrc.XRCCTRL(self.dlg, 'action')

        self.status.SetLabel("Generating grant ...")
        self.action.SetLabel("Cancel")
        self.action.Bind(wx.EVT_BUTTON, self.on_cancel)

        self.request = dl.new_grant(email, params, asynchronous=True,
                                    complete_fn=self.completed,
                                    failed_fn=self.failed)

        self.dlg.Fit()
        self.dlg.Show()
        self.request.start()

    def Show(self): return self.dlg.Show()
    def Hide(self): return self.dlg.Hide()
    def Destroy(self): return self.dlg.Destroy()
    def Bind(self, evt, fn): return self.dlg.Bind(evt, fn)

    def on_cancel(self, evt):
        self.status.SetLabel("Cancelling ...")
        self.request.cancel()

    def on_close(self, evt=None):
        self.Destroy()

    def on_completed(self, ret):
        self.url = ret['url']
        self.status.SetLabel(self.url)
        self.action.SetLabel("Copy")
        self.action.Bind(wx.EVT_BUTTON, self.on_copy)
        self.dlg.Bind(wx.EVT_CLOSE, self.on_close)
        self.dlg.Fit()

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
            self.dlg.Bind(wx.EVT_CLOSE, self.on_close)
            self.dlg.Fit()
            wx.MessageBox(error, 'Upload error', wx.OK | wx.ICON_ERROR)

    def failed(self, ex):
        wx.CallAfter(self.on_failed, ex)

    def on_copy(self, evt=None):
        if wx.TheClipboard.Open():
            try:
                wx.TheClipboard.SetData(wx.TextDataObject(self.url))
            finally:
                wx.TheClipboard.Close()
        self.on_close()


class NewTicket(object):
    def __init__(self, dl, prefs, change_fn):
        self.dl = dl
        self.prefs = prefs
        self.change_fn = change_fn
        self.xrc = xrc.XmlResource(os.path.join(RC_PATH, 'newticket.xrc'))
        self.dlg = self.xrc.LoadDialog(None, 'newticket')

        self.dlg.Bind(wx.EVT_SHOW, self.on_show)
        self.dlg.Bind(wx.EVT_CLOSE, self.on_close)
        self.file = xrc.XRCCTRL(self.dlg, 'file')
        self.perm = xrc.XRCCTRL(self.dlg, 'perm')
        self.perm.Bind(wx.EVT_CHECKBOX, self.on_perm)
        self.total_days = xrc.XRCCTRL(self.dlg, 'total_days')
        self.days_after_dl = xrc.XRCCTRL(self.dlg, 'days_after_dl')
        self.downloads = xrc.XRCCTRL(self.dlg, 'downloads')
        self.upload = xrc.XRCCTRL(self.dlg, 'upload')
        self.upload.Bind(wx.EVT_BUTTON, self.on_upload)
        self.set_defaults = xrc.XRCCTRL(self.dlg, 'set_defaults')
        self.set_defaults.Bind(wx.EVT_BUTTON, self.on_set_defaults)
        self.ticket_params = copy.copy(self.prefs.ticket_params)
        self.set_ticket_params(self.ticket_params)
        self.dlg.Fit()

    def Show(self): return self.dlg.Show()
    def Hide(self): return self.dlg.Hide()
    def Bind(self, evt, fn): return self.dlg.Bind(evt, fn)

    def on_perm(self, evt=None):
        enable = not self.perm.GetValue()
        self.total_days.Enable(enable)
        self.days_after_dl.Enable(enable)
        self.downloads.Enable(enable)

    def set_ticket_params(self, ticket_params):
        self.perm.SetValue(ticket_params.permanent)
        self.total_days.SetValue(int(ticket_params.total / (3600 * 24)))
        self.days_after_dl.SetValue(int(ticket_params.lastdl / (3600 * 24)))
        self.downloads.SetValue(ticket_params.downloads)
        self.on_perm()

    def get_ticket_params(self, ticket_params):
        ticket_params.permanent = self.perm.GetValue()
        ticket_params.total = int(self.total_days.GetValue()) * 3600 * 24
        ticket_params.lastdl = int(self.days_after_dl.GetValue()) * 3600 * 24
        ticket_params.downloads = int(self.downloads.GetValue())

    def on_set_defaults(self, evt):
        self.get_ticket_params(self.prefs.ticket_params)
        self.change_fn()

    def on_upload(self, evt):
        path = self.file.GetPath()
        if not path:
            wx.MessageBox('Please select a file!', 'New Ticket', wx.OK | wx.ICON_ERROR)
        else:
            self.get_ticket_params(self.ticket_params)
            self.on_close()
            Upload(path, self.dl, self.ticket_params)

    def on_show(self, evt):
        self.file.SetPath('')

    def on_close(self, evt=None):
        self.Hide()


# -------------------- App --------------------

class DLApp(wx.App):
    def OnInit(self):
        if not adv.TaskBarIcon.IsAvailable():
            wx.MessageBox('A system tray is required for ' + DL_DESCRIPTION,
                          'dl-wx', wx.OK | wx.ICON_ERROR)
            return False

        self.load_prefs()
        self.pd = PrefDialog(self.prefs, self.save_prefs)
        if not self.prefs.service.url or not self.prefs.email:
            wx.MessageBox('This is the first time you run ' + DL_DESCRIPTION +
                          '. You need to configure it first.',
                          'Preferences', wx.OK | wx.ICON_INFORMATION)
            self.pd.ShowModal()

        self.dl = DL(self.prefs.service)
        self.nt = NewTicket(self.dl, self.prefs, self.save_prefs)
        self.tbi = TaskBarIcon(self)
        stub = wx.Frame(None)
        menu_bar = wx.MenuBar()
        menu_bar.Append(self.tbi.CreatePopupMenu(), "&File")
        stub.SetMenuBar(menu_bar)
        self.SetTopWindow(stub)
        return True

    def load_prefs(self):
        cfgpath = os.path.expanduser(DL_RC)
        self.cfg = configobj.ConfigObj(cfgpath)
        v = validate.Validator()

        self.prefs = Preferences()
        self.prefs.email = v.check('string', self.cfg.get('email', ''))

        self.prefs.service.url = v.check('string', self.cfg.get('url', ''))
        self.prefs.service.username = v.check('string', self.cfg.get('user', ''))
        self.prefs.service.password = v.check('string', self.cfg.get('pass', ''))
        self.prefs.service.verify = v.check('boolean', self.cfg.get('verify', True))
        self.prefs.service.agent = DL_AGENT

        self.prefs.ticket_params.permanent = v.check('boolean', self.cfg.get('perm', False))
        self.prefs.ticket_params.total = v.check('integer', self.cfg.get('total_days', 365)) * 3600 * 24
        self.prefs.ticket_params.lastdl = v.check('integer', self.cfg.get('days_after_dl', 30)) * 3600 * 24
        self.prefs.ticket_params.downloads = v.check('integer', self.cfg.get('downloads', 0))

    def save_prefs(self):
        self.cfg['url'] = self.prefs.service.url
        self.cfg['user'] = self.prefs.service.username
        self.cfg['pass'] = self.prefs.service.password
        self.cfg['verify'] = self.prefs.service.verify
        self.cfg['perm'] = self.prefs.ticket_params.permanent
        self.cfg['email'] = self.prefs.email
        self.cfg['total_days'] = self.prefs.ticket_params.total // (3600 * 24)
        self.cfg['days_after_dl'] = self.prefs.ticket_params.lastdl // (3600 * 24)
        self.cfg['downloads'] = self.prefs.ticket_params.downloads
        self.cfg.write()

    def express_ticket(self, evt=None):
        path = wx.FileSelector(
          "Choose a file", "", "", "",
          "*",
          wx.FD_OPEN | wx.FD_FILE_MUST_EXIST
        )
        if path:
          Upload(path, self.dl, self.prefs.ticket_params) 

    def new_ticket(self, evt=None):
        self.nt.Show()

    def new_grant(self, evt=None):
        Grant(self.prefs.email, self.dl, self.prefs.grant_params)

    def show_prefs(self, evt=None):
        self.pd.Show()

    def show_about(self, evt=None):
        wx.MessageBox(DL_DESCRIPTION + " " + DL_AGENT + "\n" + DL_URL,
                      'About', wx.OK | wx.ICON_INFORMATION)

    def quit(self, evt=None):
        self.ExitMainLoop()

    def MacOpenFile(self, path):
        Upload(path, self.dl, self.prefs.ticket_params)

    def MacReopenApp(self):
        self.express_ticket()


if __name__ == '__main__':
    main = DLApp()
    main.MainLoop()
    # TaskBarIcon cleans up with the app; no explicit Destroy needed for wrappers
