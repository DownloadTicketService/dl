const TYP = "DL";
const AID = "thunderbird-filelink-dl@thregr.org";
const CID = "{c0bee36d-3c0d-460b-bb9a-f0e9c873a833}";
const VER = "0.17.1";

const { classes: Cc, interfaces: Ci, utils: Cu, results: Cr } = Components;

Cu.import("resource://gre/modules/XPCOMUtils.jsm");
Cu.import("resource://gre/modules/Services.jsm");
Cu.import("resource:///modules/cloudFileAccounts.js");

// 'File' property not available in in TB<38
try { Cu.importGlobalProperties(['File']); }
catch(e) {}

function nsDL() {}

nsDL.prototype =
{
  // Required interface
  QueryInterface: XPCOMUtils.generateQI([Ci.nsIDL, Ci.nsIMsgCloudFileProvider]),
  classID: Components.ID(CID),

  get type() TYP,
  get displayName() TYP,
  get version() VER,
  get iconClass() "chrome://thunderbird-filelink-dl/skin/dl-icon.png",
  get settingsURL() "chrome://thunderbird-filelink-dl/content/settings.xhtml",
  get managementURL() "chrome://thunderbird-filelink-dl/content/management.xhtml",
  get accountKey() this._accountKey,
  get serviceURL() this._masterPath,
  get lastError() this._lastErrorText,
  get fileUploadSizeLimit() this._maxSize,
  get remainingFileSpace() -1,
  get fileSpaceUsed() -1,


  // Internal variables
  _accountKey: null,
  _prefBranch: null,
  _username: null,
  _password: null,
  _defaults: null,

  _restURL: null,
  _masterPath: null,
  _maxSize: null,

  _lastErrorText: null,
  _tickets: {},
  _grants: {},
  _grantCount: 0,


  // Support functions
  _request: function(request, msg, success_cb, failure_cb, abort_cb=null, file=null)
  {
    let req = Cc["@mozilla.org/xmlextras/xmlhttprequest;1"].createInstance(Ci.nsIXMLHttpRequest);
    let method = (!msg && !file? "GET": "POST");
    req.mozBackgroundRequest = true;
    req.open(method, this._restURL + "/" + request, true);
    req.setRequestHeader("User-agent", AID + "/" + VER);

    let auth = "Basic " + btoa(this._username + ":" + this._password);
    req.setRequestHeader("Authorization", auth);
    req.setRequestHeader("X-Authorization", auth);

    req.onerror = function()
    {
      failure_cb(req, null);
    };

    req.onabort = function()
    {
      if(abort_cb)
	abort_cb(req);
      else
	failure_cb(req, null);
    };

    req.onload = function()
    {
      // try to parse the output (if any)
      let res = null;

      try
      {
	if(req.responseText)
	  res = JSON.parse(req.responseText);
      }
      catch(e if e instanceof SyntaxError)
      {
	// ignored
      }

      if(req.status < 200 || req.status >= 400)
	failure_cb(req, res);
      else
	success_cb(req, res);
    };

    if(!msg && !file)
      req.send();
    else
    {
      let data = Cc["@mozilla.org/files/formdata;1"].createInstance(Ci.nsIDOMFormData);
      if(msg) data.append("msg", JSON.stringify(msg));
      if(file) data.append("file", new File(file));
      req.send(data);
    }

    return req;
  },


  _setDefaults: function(v)
  {
    this._prefBranch.setIntPref("defaults.total", v.total);
    this._prefBranch.setIntPref("defaults.lastdl", v.lastdl);
    this._prefBranch.setIntPref("defaults.maxdl", v.maxdl);
    this._defaults = v;
  },


  _getPassword: function(aWithUI=true)
  {
    let logins = Services.logins.findLogins({}, this._restURL, null, this._restURL);
    for each(let info in logins)
    {
      if(info.username == this._username)
	return loginInfo.password;
    }
    if(!aWithUI)
      return null;

    // no login data, prompt for a new password
    let serverURL = this._restURL;
    let userPos = serverURL.indexOf("//") + 2;
    let usernamePart = encodeURIComponent(this._username) + '@';
    serverURL = serverURL.substr(0, userPos) + usernamePart + serverURL.substr(userPos);

    let messengerBundle = Services.strings.createBundle(
      "chrome://messenger/locale/messenger.properties");
    let promptString = messengerBundle.formatStringFromName(
      "passwordPrompt", [this._username, this.displayName], 2);

    let win = Services.wm.getMostRecentWindow(null);
    let prompt = Services.ww.getNewAuthPrompter(win);
    let password = {value: null};
    if(prompt.promptPassword(this.displayName, promptString, serverURL,
			     prompt.SAVE_PASSWORD_PERMANENTLY, password))
      return password.value;

    return null;
  },


  _clearPassword: function()
  {
    this._password = null;
    let logins = Services.logins.findLogins({}, this._restURL, null, this._restURL);
    for each(let info in logins)
    {
      if(info.username == this._username)
	Services.logins.removeLogin(info);
    }
  },


  _genericFailure: function(req, res, aCallback)
  {
    // set the error text, if any
    if(res && res.error)
      this._lastErrorText = res.error;

    // check for authentication failures
    if(req.status == 401)
    {
      this._clearPassword();
      aCallback.onStopRequest(null, this, Ci.nsIMsgCloudFileProvider.authErr);
      return;
    }

    aCallback.onStopRequest(null, this, Cr.NS_ERROR_FAILURE);
  },


  // Implementation
  init: function(aAccountKey)
  {
    this._accountKey = aAccountKey;
    this._prefBranch = Services.prefs.getBranch("mail.cloud_files.accounts." + aAccountKey + ".");
    this._restURL = this._prefBranch.getCharPref("restURL");
    this._username = this._prefBranch.getCharPref("username");

    // try to fetch ticket defaults (otherwise wait for init)
    if(this._prefBranch.prefHasUserValue("defaults.total"))
    {
      /* TODO: for now, always fetch server defaults until values are not fully
	       customizable, in order for the client not getting "stuck" forever.

      this._defaults = {total: this._prefBranch.getIntPref("defaults.total"),
			lastdl: this._prefBranch.getIntPref("defaults.lastdl"),
			maxdl: this._prefBranch.getIntPref("defaults.maxdl")};
      */
    }
  },


  createExistingAccount: function(aCallback)
  {
    this.refreshUserInfo(true, aCallback);
  },


  refreshUserInfo: function(aWithUI, aCallback)
  {
    if(Services.io.offline)
      throw Ci.nsIMsgCloudFileProvider.offlineErr;
    aCallback.onStartRequest(null, this);
    if(!(this._password = this._getPassword(aWithUI)))
    {
      aCallback.onStopRequest(null, this, Ci.nsIMsgCloudFileProvider.authErr);
      return;
    }

    let success_cb = function(req, res)
    {
      // get some interesting variables from the service
      this._masterPath = res.masterpath;
      this._maxSize = res.maxsize;

      // set ticket defaults if we don't have any yet
      if(!this._defaults)
      {
	this._setDefaults({total: res.defaults.ticket.total,
			   lastdl: res.defaults.ticket.lastdl,
			   maxdl: res.defaults.ticket.maxdl});
      }

      aCallback.onStopRequest(null, this, Cr.NS_OK);
    }.bind(this);

    let failure_cb = function(req, res)
    {
      this._genericFailure(req, res, aCallback);
    }.bind(this);

    this._request("info", null, success_cb, failure_cb);
  },


  uploadFile: function(aFile, aCallback)
  {
    if(Services.io.offline)
      throw Ci.nsIMsgCloudFileProvider.offlineErr;
    aCallback.onStartRequest(null, this);
    if(!(this._password = this._getPassword()))
    {
      aCallback.onStopRequest(null, this, Ci.nsIMsgCloudFileProvider.authErr);
      return;
    }

    let success_cb = function(req, res)
    {
      this._tickets[aFile.spec].res = res;
      aCallback.onStopRequest(null, this, Cr.NS_OK);
    }.bind(this);

    let failure_cb = function(req, res)
    {
      delete this._tickets[aFile.spec];
      this._genericFailure(req, res, aCallback);
    }.bind(this);

    let abort_cb = function(req)
    {
      delete this._tickets[aFile.spec];
      aCallback.onStopRequest(null, this, Ci.nsIMsgCloudFileProvider.uploadCanceled);
    }.bind(this);

    let req = this._request("newticket", {}, success_cb, failure_cb, abort_cb, aFile);
    this._tickets[aFile.spec] = {req: req, res: null};
  },


  urlForFile: function(aFile)
  {
    return this._tickets[aFile.spec].res.url;
  },


  cancelFileUpload: function(aFile)
  {
    this._tickets[aFile.spec].req.abort();
  },


  deleteFile: function(aFile, aCallback)
  {
    if(Services.io.offline)
      throw Ci.nsIMsgCloudFileProvider.offlineErr;
    aCallback.onStartRequest(null, this);
    if(!(this._password = this._getPassword()))
    {
      aCallback.onStopRequest(null, this, Ci.nsIMsgCloudFileProvider.authErr);
      return;
    }

    let success_cb = function(req, res)
    {
      delete this._tickets[aFile.spec];
      aCallback.onStopRequest(null, this, Cr.NS_OK);
    }.bind(this);

    let failure_cb = function(req, res)
    {
      this._genericFailure(req, res, aCallback);
    }.bind(this);

    let id = encodeURIComponent(this._tickets[aFile.spec].res.id);
    this._request("purgeticket/" + id, {}, success_cb, failure_cb);
  },


  newGrant: function(aCallback, aEmail)
  {
    if(Services.io.offline)
      throw Ci.nsIMsgCloudFileProvider.offlineErr;
    aCallback.onStartRequest(null, this);
    if(!(this._password = this._getPassword()))
    {
      aCallback.onStopRequest(null, this, Ci.nsIMsgCloudFileProvider.authErr);
      return;
    }

    let id = this._grantCount++;

    let success_cb = function(req, res)
    {
      this._grants[id].res = res;
      aCallback.onStopRequest(null, this, Cr.NS_OK);
    }.bind(this);

    let failure_cb = function(req, res)
    {
      delete this._grants[id];
      this._genericFailure(req, res, aCallback);
    }.bind(this);

    let req = this._request("newgrant", {'notify': aEmail}, success_cb, failure_cb);
    this._grants[id] = {req: req, res: null};
    return id;
  },


  urlForGrant: function(id)
  {
    return this._grants[id].res.url;
  },


  deleteGrant: function(id, aCallback)
  {
    throw Cr.NS_ERROR_NOT_IMPLEMENTED;
  },


  // Stubs
  createNewAccount: function()
  {
    return Cr.NS_ERROR_NOT_IMPLEMENTED;
  },


  providerUrlForError: function(aError)
  {
    return "";
  },
};


const NSGetFactory = XPCOMUtils.generateNSGetFactory([nsDL]);
