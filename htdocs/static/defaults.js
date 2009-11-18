// defaults
var cookieLifetime = 1000 * 60 * 60 * 24 * 90;
var pwdLength = 16;
var fields = Array('gn', 'dn', 'hra', 'dln', 'nt', 'st');

// hooks
window.addEventListener("load", loadDefaults, false);


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
  return null;
}

function refreshCookie(name, lifetime)
{
  var v = getCookie(name);
  if(v === null) return null;
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
    if(!document.forms[0][name]) continue;
    var v = refreshCookie(name, cookieLifetime);
    if(v !== null) document.forms[0][name].value = v;
  }
}

function setDefaults()
{
  var expire = new Date();
  expire.setTime(expire.getTime() + cookieLifetime);

  for(var i = 0; i != fields.length; ++i)
  {
    var name = fields[i];
    if(!document.forms[0][name]) continue;
    setCookie(name, document.forms[0][name].value, expire);
  }
}


// password generator
function passGen()
{
  var chrs = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
  var passwd = '';

  for(var i = 0; i != pwdLength; ++i)
    passwd += chrs.charAt(Math.floor(Math.random() * chrs.length));
  document.forms[0].pass.value = passwd;

  return true;
}
