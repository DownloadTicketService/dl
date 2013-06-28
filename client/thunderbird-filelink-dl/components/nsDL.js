const { classes: Cc, interfaces: Ci, utils: Cu, results: Cr } = Components;

Cu.import("resource://gre/modules/XPCOMUtils.jsm");
Cu.import("resource://gre/modules/Services.jsm");
Cu.import("resource:///modules/gloda/log4moz.js");
Cu.import("resource:///modules/cloudFileAccounts.js");

const AID = "thunderbird-filelink-dl@thregr.org";
const CID = "{c0bee36d-3c0d-460b-bb9a-f0e9c873a833}";
const VER = "0.11";

function nsDL()
{
  this._log = Log4Moz.getConfiguredLogger(this.type);
}

nsDL.prototype =
{
  // Required interface
  QueryInterface: XPCOMUtils.generateQI([Ci.nsIMsgCloudFileProvider]),
  classID: Components.ID(CID),

  get type() "DL",
  get displayName() "DL",
  get version() VER,
  get iconClass() "chrome://thunderbird-filelink-dl/content/dl-icon.png",
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
  _uploads: {},


  // Support functions
  _request: function(request, msg, success_cb, failure_cb, abort_cb=null, file=null)
  {
    let req = Cc["@mozilla.org/xmlextras/xmlhttprequest;1"].createInstance(Ci.nsIXMLHttpRequest);
    let method = (!msg && !file? "GET": "POST");
    req.open(method, this._restURL + "/" + request, true);
    req.setRequestHeader("Authorization", "Basic " + btoa(this._username + ":" + this._password));
    req.setRequestHeader("User-agent", AID + "/" + VER);

    req.onerror = function()
    {
      failure_cb(null, null);
    };

    req.onabort = function()
    {
      if(abort_cb)
	abort_cb(req);
      else
	failure_cb(null, null);
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
      if(file) data.append("file", File(file));
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


  // Implementation
  init: function(aAccountKey)
  {
    this._accountKey = aAccountKey;
    this._prefBranch = Services.prefs.getBranch("mail.cloud_files.accounts." + aAccountKey + ".");
    this._restURL = this._prefBranch.getCharPref("restURL");
    this._username = this._prefBranch.getCharPref("username");
    this._password = this._prefBranch.getCharPref("password");

    // try to fetch ticket defaults (otherwise wait for init)
    if(this._prefBranch.prefHasUserValue("defaults.downloads"))
    {
      this._defaults = {total: this._prefBranch.getIntPref("defaults.total"),
			lastdl: this._prefBranch.getIntPref("defaults.lastdl"),
			maxdl: this._prefBranch.getIntPref("defaults.maxdl")};
    }
  },


  createExistingAccount: function(aCallback)
  {
    this.refreshUserInfo(false, aCallback);
  },


  refreshUserInfo: function(aWithUI, aCallback)
  {
    if(Services.io.offline)
      throw Ci.nsIMsgCloudFileProvider.offlineErr;

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
      if(req && req.status == 401)
      {
	// TODO: handle auth errors
	aCallback.onStopRequest(null, this, Ci.nsIMsgCloudFileProvider.authErr);
      }

      aCallback.onStopRequest(null, this, Cr.NS_ERROR_FAILURE);
    }.bind(this);

    aCallback.onStartRequest(null, null);
    this._request("info", null, success_cb, failure_cb);
  },


  uploadFile: function(aFile, aCallback)
  {
    if(Services.io.offline)
      throw Ci.nsIMsgCloudFileProvider.offlineErr;

    let success_cb = function(req, res)
    {
      this._uploads[aFile.spec].res = res;
      aCallback.onStopRequest(null, this, Cr.NS_OK);
    }.bind(this);

    let failure_cb = function(req, res)
    {
      if(res && res.err)
	this._lastErrorText = res.err;
      delete this._uploads[aFile.spec];
      aCallback.onStopRequest(null, this, Cr.NS_ERROR_FAILURE);
    }.bind(this);

    let abort_cb = function(req)
    {
      delete this._uploads[aFile.spec];
      aCallback.onStopRequest(null, this, Ci.nsIMsgCloudFileProvider.uploadCanceled);
    }.bind(this);

    aCallback.onStartRequest(null, null);
    let req = this._request("newticket", {}, success_cb, failure_cb, abort_cb, aFile);
    this._uploads[aFile.spec] = {req: req, res: null};
  },


  urlForFile: function(aFile)
  {
    return this._uploads[aFile.spec].res.url;
  },


  cancelFileUpload: function(aFile)
  {
    this._uploads[aFile.spec].req.abort();
  },


  // Stubs
  deleteFile: function(aFile, aCallback)
  {
    return Cr.NS_ERROR_NOT_IMPLEMENTED;
  },


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
