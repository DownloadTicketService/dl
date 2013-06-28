Components.utils.import("resource:///modules/Services.jsm");

function onLoadProvider(provider)
{
  let messenger = Components.classes["@mozilla.org/messenger;1"]
    .createInstance(Components.interfaces.nsIMessenger);

  let bundle = Services.strings.createBundle("chrome://messenger/locale/messenger.properties");
  let unknownSize = bundle.GetStringFromName("attachmentSizeUnknown");

  let urlElem = document.getElementById("provider-url");
  urlElem.textContent = provider.serviceURL;
  urlElem.setAttribute("href", provider.serviceURL);

  // Total/Used space chart
  let mfsElem = document.getElementById("max-file-size");
  let mfs = provider.fileUploadSizeLimit;
  mfsElem.textContent = (mfs < 0? unknownSize: messenger.formatFileSize(mfs));

  let fsuElem = document.getElementById("file-space-used");
  let fsu = provider.fileSpaceUsed;
  fsuElem.textContent = (fsu < 0? unknownSize: messenger.formatFileSize(fsu));
  let fileSpaceUsedSwatch = document.getElementById("file-space-used-swatch");
  fileSpaceUsedSwatch.style.backgroundColor = pv.Colors.category20.values[0];

  let fsrElem = document.getElementById("remaining-file-space");
  let fsr = provider.remainingFileSpace;
  fsrElem.textContent = (fsr < 0? unknownSize: messenger.formatFileSize(fsr));
  let remainingFileSpaceSwatch = document.getElementById("remaining-file-space-swatch");
  remainingFileSpaceSwatch.style.backgroundColor = pv.Colors.category20.values[1];

  let totalSpace = fsu + fsr;
  if(totalSpace <= 0)
    document.getElementById("provider-spacebox").setAttribute("unknown-space", "true");
  else
  {
    let pieScale = 2 * Math.PI / totalSpace;
    let spaceDiv = document.getElementById("provider-space-visuals");
    let vis = new pv.Panel().canvas(spaceDiv)
      .width(150)
      .height(150);
    vis.add(pv.Wedge)
      .data([fsu, fsr])
      .left(75)
      .top(75)
      .innerRadius(30)
      .outerRadius(65)
      .angle(function(d) d * pieScale);
    
    vis.add(pv.Label)
      .left(75)
      .top(75)
      .font("14px Sans-Serif")
      .textAlign("center")
      .textBaseline("middle")
      .text(messenger.formatFileSize(totalSpace));
  
    vis.render();
    document.getElementById("provider-spacebox").removeAttribute("unknown-space");
  }
}
