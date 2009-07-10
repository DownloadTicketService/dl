function setCookie(name, value, expire)
{
  document.cookie = name + "=" + escape(value)
    + ((expire == null) ? "" : ("; expires=" + expire.toGMTString()));
}

function getCookie(name)
{
  var search = name + "=";
  if(document.cookie.length > 0)
  {
    offset = document.cookie.indexOf(search);
    if (offset != -1)
    {
      offset += search.length;
      end = document.cookie.indexOf(";", offset);
      if (end == -1) end = document.cookie.length;
      return unescape(document.cookie.substring(offset, end));
    }
  }
  return false;
}

function loadDefaults()
{
  var hr = getCookie("hr");
  if(hr !== false) document.forms[0].hr.value = hr;
  var hra = getCookie("hra");
  if(hra !== false) document.forms[0].hra.value = hra;
  var dln = getCookie("dln");
  if(dln !== false) document.forms[0].dln.value = dln;
  var nt = getCookie("nt");
  if(nt !== false) document.forms[0].nt.value = nt;
}

function setDefaults()
{
  var expires = new Date();
  expires.setTime(expires.getTime() + 60*60*24*90);
  
  setCookie("hr", document.forms[0].hr.value, expires);
  setCookie("hra", document.forms[0].hra.value, expires);
  setCookie("dln", document.forms[0].dln.value, expires);
  setCookie("nt", document.forms[0].nt.value, expires);
}
