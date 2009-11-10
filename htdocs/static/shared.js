// defaults
var cookieLifetime = 1000 * 60 * 60 * 24 * 90;
var fields = Array('dn', 'hra', 'dln', 'nt', 'st');


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
  for(var i = 0; i != fields.length; ++i)
  {
    var name = fields[i];
    var v = refreshCookie(name, cookieLifetime);
    if(v !== false) document.forms[0][name].value = v;
  }
}

function setDefaults()
{
  var expire = new Date();
  expire.setTime(expire.getTime() + cookieLifetime);

  for(var i = 0; i != fields.length; ++i)
  {
    var name = fields[i];
    setCookie(name, document.forms[0][name].value, expire);
  }
}
