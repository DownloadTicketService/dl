const { classes: Cc, interfaces: Ci, utils: Cu, results: Cr } = Components;

Cu.import("resource:///modules/Services.jsm");
Cu.import("resource:///modules/cloudFileAccounts.js");
Cu.import("resource://gre/modules/XPCOMUtils.jsm");

var composeBundle = Services.strings.createBundle(
    "chrome://thunderbird-filelink-dl/locale/compose.properties");

function insertUploadGrantListener(provider)
{
  this.provider = provider;
}

insertUploadGrantListener.prototype =
{
  id: null,

  onStartRequest: function(aRequest, aContext)
  {
    // show some progress
    ToggleWindowLock(true);
    document.getElementById("compose-progressmeter").setAttribute("mode", "undetermined");
    document.getElementById("statusbar-progresspanel").collapsed = false;

    let msg = composeBundle.GetStringFromName("newGrantProgress");
    document.getElementById('statusText').setAttribute('label', msg);
  },

  onStopRequest: function(aRequest, aContext, aStatusCode)
  {
    // restore progress state
    ToggleWindowLock(false);
    document.getElementById("compose-progressmeter").setAttribute("mode", "normal");
    document.getElementById("compose-progressmeter").setAttribute("value", 0);
    document.getElementById("statusbar-progresspanel").collapsed = true;

    if(aStatusCode != Cr.NS_OK)
    {
      let msg = composeBundle.GetStringFromName("newGrantFailure");
      document.getElementById('statusText').setAttribute('label', msg);
    }
    else
    {
      // insert grant URL
      let url = this.provider.urlForGrant(this.id);
      let editor = GetCurrentEditor();
      editor.beginTransaction();
      editor.insertHTML("<a href=\"" + encodeURI(url) + "\">" + encodeURI(url) + "</a>");
      editor.endTransaction();
      document.getElementById('statusText').setAttribute('label', '');
    }
  },

  QueryInterface: XPCOMUtils.generateQI([Ci.nsIRequestObserver, Ci.nsISupportsWeakReference])
};

function insertUploadGrant()
{
  // search for a DL account
  let provider = Cc["@thregr.org/thunderbird-filelink-dl;1"].getService(Ci.nsIDL);
  let accounts = cloudFileAccounts.getAccountsForType(provider.type);
  if(!accounts.length)
  {
    let title = composeBundle.GetStringFromName("accountNeededTitle");
    let msg = composeBundle.GetStringFromName("accountNeededMsg");
    let prompts = Cc["@mozilla.org/embedcomp/prompt-service;1"].getService(Ci.nsIPromptService);
    if(prompts.confirm(window, title, msg))
      cloudFileAccounts.addAccountDialog();
    return;
  }

  // initialize the first valid DL account
  let accountKey = accounts[0].accountKey;
  try { provider.init(accountKey); }
  catch(e)
  {
    Cu.reportError(e);
    return;
  }

  // request a new grant URL
  let listener = new insertUploadGrantListener(provider);
  listener.id = provider.newGrant(listener);
}
