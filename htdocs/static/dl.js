// defaults
var cookieLifetime = 1000 * 60 * 60 * 24 * 90;
var pwdLength = 16;
var fields = Array('gn', 'dn', 'hra', 'dln', 'nt', 'st');


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
    var offset = document.cookie.indexOf(search);
    if (offset != -1)
    {
      offset += search.length;
      var end = document.cookie.indexOf(";", offset);
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
function loadDefaults(set)
{
  for(var i = 0; i != fields.length; ++i)
  {
    var name = fields[i];
    if(!document.forms[0][name]) continue;
    var v = refreshCookie(set + '_' + name, cookieLifetime);
    if(v !== null) document.forms[0][name].value = v;
  }

  var v = refreshCookie(set + '_advanced', cookieLifetime);
  if(parseInt(v)) toggleAdvanced(true);
}

function setDefaults(set)
{
  var expire = new Date();
  expire.setTime(expire.getTime() + cookieLifetime);

  for(var i = 0; i != fields.length; ++i)
  {
    var name = fields[i];
    if(!document.forms[0][name]) continue;
    setCookie(set + '_' + name, document.forms[0][name].value, expire);
  }

  var v = $('#advanced').hasClass('active');
  setCookie(set + '_advanced', (v? 0: 1), expire);
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


// UI/form
function toggleAdvanced(set)
{
  $('#toggler').toggleClass("active");
  var t = $('#advanced').toggleClass("active");
  if(!set) t.slideToggle("fast"); else t.toggle();
}

function validateForm()
{
  var ok = true;

  $('label.required').each(function()
  {
    var label = $(this);
    var value = $('input.required', label.next()).val();
    if(value.length)
      label.removeClass('error');
    else
    {
      label.addClass('error');
      ok = false;
    }
  });

  return ok;
}

function validate(event)
{
  if(!validateForm())
    event.preventDefault();
  else
  {
    $('#submit').attr('disabled', true);
    $('.onsubmit').submit();
  }
}

function validateHook(fun)
{
  var hook = $('<div class="onsubmit" style="display: none;">');
  hook.appendTo(document.body);
  hook[0].onsubmit = fun;
}
