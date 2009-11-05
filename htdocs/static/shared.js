// defaults
var cookieLifetime = 60000 * 60 * 24 * 90;


// cookie helpers
function setCookie(name, value, expire)
{
  document.cookie = name + "=" + escape(value)
    + ((expire == null) ? "" : ("; expires=" + expire.toUTCString()));
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

function refreshCookie(name, lifetime)
{
  var v = getCookie(name);
  var expire = new Date();
  expire.setTime(expire.getTime() + lifetime);
  setCookie(name, v, expire);
  return v;
}


// defaults
function loadDefaults()
{
  var hr = refreshCookie("hr", cookieLifetime);
  if(hr !== false) document.forms[0].hr.value = hr;
  var hra = refreshCookie("hra", cookieLifetime);
  if(hra !== false) document.forms[0].hra.value = hra;
  var dln = refreshCookie("dln", cookieLifetime);
  if(dln !== false) document.forms[0].dln.value = dln;
  var nt = refreshCookie("nt", cookieLifetime);
  if(nt !== false) document.forms[0].nt.value = nt;
}

function setDefaults()
{
  var expire = new Date();
  expire.setTime(expire.getTime() + cookieLifetime);

  setCookie("hr", document.forms[0].hr.value, expire);
  setCookie("hra", document.forms[0].hra.value, expire);
  setCookie("dln", document.forms[0].dln.value, expire);
  setCookie("nt", document.forms[0].nt.value, expire);
}
