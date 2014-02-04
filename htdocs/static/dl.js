// defaults
var cookieLifetime = 1000 * 60 * 60 * 24 * 90;
var pwdLength = 16;
var fields =
[
  'grant_total', 'ticket_totaldays', 'ticket_lastdldays',
  'ticket_maxdl', 'ticket_permanent', 'notify', 'send_to'
];


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
function loadDefaults(set, form)
{
  if(!form) form = document.forms[0];

  for(var i = 0; i != fields.length; ++i)
  {
    var name = fields[i];
    if(!form[name]) continue;
    var v = refreshCookie(set + '_' + name, cookieLifetime);
    if(v !== null)
    {
      if(form[name].type == 'checkbox')
	form[name].checked = parseInt(v);
      else
	form[name].value = v;
    }
  }

  var v = refreshCookie(set + '_advanced', cookieLifetime);
  if(v === null || parseInt(v)) toggleAdvanced(true);
}

function setDefaults(set, form)
{
  var expire = new Date();
  expire.setTime(expire.getTime() + cookieLifetime);
  if(!form) form = document.forms[0];

  for(var i = 0; i != fields.length; ++i)
  {
    var name = fields[i];
    if(!form[name]) continue;
    var value;
    if(form[name].type == 'checkbox')
      value = form[name].checked + 0;
    else
      value = form[name].value;
    setCookie(set + '_' + name, value, expire);
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


function hideComments()
{
  $('tr.file.expanded').removeClass('expanded');
  $('tr.file.comment').hide();
}


function toggleComment(id)
{
  $('tr.file.' + id).toggleClass('expanded');
  $('tr.file.comment.' + id).toggle();
}


function selectAll(v)
{
  if(v === undefined) v = true;
  $('input:checkbox', document.forms[0]).attr('checked', v);
}


function validateForm(form)
{
  var ok = true;
  var max = $('input[name=MAX_FILE_SIZE]', form).val();

  $('label.required', form).each(function()
  {
    var label = $(this);
    var field = $('input[required]', label.next())[0];
    var state = true;

    // check content
    if(!$(field).val().length)
      state = false;

    // check also file sizes if the browser is recent enough
    if(state && field.files && field.files[0].size > max)
      state = false;

    // set field state
    if(state)
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
  if(!validateForm(event.target))
    event.stopImmediatePropagation();
}

function setNt(email)
{
  $('#notify').val(email);
}


// automatic upload progress
function autoProgress(event)
{
  // disable submit
  form = event.target;
  $('#submit', form).attr('disabled', true);

  var xhr = new XMLHttpRequest();
  if(!('FormData' in window) || !xhr.upload) return;
  event.preventDefault();

  // show progress bar
  progress = $('#uploadprogressbar', form);
  progress.attr({'min': 0, 'max': 100});
  progress.fadeIn();

  // event handlers
  xhr.upload.onprogress = function(ev)
  {
    progress.attr('value',  (ev.loaded / ev.total) * 100);
  };

  xhr.onreadystatechange = function(ev)
  {
    if(this.readyState != 4) return;

    // replace current document with response
    document.open();
    document.write(this.response);
    document.close();
    $.cache = {};
  };

  // post
  xhr.open("POST", form.action);
  xhr.send(new FormData(form));
}


// Initialization
$.getCss = function(url)
{
  $('head').append('<link rel="stylesheet" type="text/css" href="' + encodeURI(url) + '"/>');
}

function init()
{
  // togglers
  $('#toggler').click(toggleAdvanced);

  // form defaults
  $('form[defaults]').each(function(i, t)
  {
    var form = $(t);
    var set = form.attr('defaults');
    loadDefaults(set, t);
    form.find('#setDefaults').click(function(el) { setDefaults(set, t); });
  });

  // js validation
  $('form.validate').submit(validate);

  // sortable tables
  var tables = $('table.sortable');
  if(tables.length)
  {
    $.getScript('static/stupidtable.js', function()
    {
      tables.each(function(i, t)
      {
	t = $(t);
	t.stupidtable();
	t.find('th.sorting-asc').click();
      });
    });
  }

  // progress polyfill
  if($('progress').length)
  {
    $.getScript('static/progress-polyfill.js');
    $.getCss('static/progress-polyfill.css');
  }

  // automatic upload progress
  $('form.autoprogress').submit(autoProgress);
}

$(document).ready(init);
