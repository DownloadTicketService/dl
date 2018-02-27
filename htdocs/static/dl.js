"use strict";

// defaults
var initialDelay = 3000;
var speedWindow = 1000;
var etaWindow = 5000;
var cookieLifetime = 1000 * 60 * 60 * 24 * 90;
var pwdLength = 16;
var fields =
[
  'grant_totaldays', 'grant_lastuldays', 'grant_maxul', 'grant_expiry',
  'ticket_totaldays', 'ticket_lastdldays', 'ticket_maxdl', 'ticket_expiry',
  'notify', 'pass_send', 'send_to'
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
      var el = $(form[name]);
      if(el.attr('type') == 'checkbox')
	el.prop('checked', parseInt(v));
      else
	el.val(v);
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
    var el = $(form[name]);
    var val = (el.attr('type') == 'checkbox'? +el.prop('checked'): el.val());
    setCookie(set + '_' + name, val, expire);
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

  // assume generated passwords are ephemeral
  $('#pass_send').prop('checked', true);

  return true;
}


// localization stub
function T_(text)
{
  return text;
}


// clipboard related
function copyToClipboard(str)
{
  if('queryCommandSupported' in document && document.queryCommandSupported("copy"))
  {
    // the "proper" way
    var el = document.createElement('textarea');
    el.value = str;
    document.body.appendChild(el);
    el.focus();
    el.setSelectionRange(0, str.length);
    document.execCommand('copy');
    document.body.removeChild(el);
  }
  else if("clipboardData" in window)
  {
    // the way it *should* be
    window.clipboardData.setData('Text', str);
  }
}


// UI/form
function toggleAdvanced(set)
{
  $('#toggler').toggleClass("active");
  var t = $('#advanced').toggleClass("active");
  if(!set) t.slideToggle("fast"); else t.toggle();
}

function selectAll(v)
{
  $('input:checkbox', document.forms[0]).each(function()
  {
    this.checked = v;
  });
}

function validateForm(form)
{
  var ok = true;
  var max = $('input[name=MAX_FILE_SIZE]', form).val();
  var size = 0;

  $('label.required', form).each(function()
  {
    var label = $(this);
    var field = $('input[required]', label.next())[0];
    var state = true;

    // check content
    if(!$(field).val().length)
      state = false;

    // check also file sizes if the browser is recent enough
    if(state && field.files)
    {
      for(var i = 0; i != field.files.length; ++i)
	size += field.files[i].size;
      if(size > max)
	state = false;
    }

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
  {
    event.stopImmediatePropagation();
    event.preventDefault();
  }
}

function setNt(email)
{
  $('#notify').val(email);
}


// automatic upload progress
function time()
{
  return (new Date).getTime();
}

function noProgress(event)
{
  // disable the form buttons
  $('.buttons input', event.target).attr('disabled', true);
}

function setupAutoProgress(el)
{
  el = $(el);

  // upgrade only tuned forms
  var div = el.find('#uploadprogress');
  if(!div.length || !('FormData' in window))
  {
    el.submit(noProgress);
    return;
  }

  // create support elements
  var progress = $('<progress id="progress" min="0" max="1"></progress>');
  var text = $('<span id="text"></span>');
  var cancelBtn = $('<input id="cancel" type="button">');
  cancelBtn.val(T_("Cancel"));

  div.append(cancelBtn);
  div.append(progress);
  div.append(text);

  el.data('autoprogress',
  {
    'div': div,
    'submit': el.find('#submit'),
    'cancelBtn': cancelBtn,
    'progress': progress,
    'text': text
  });

  el.submit(autoProgress);
}

function autoProgressState(ev, data)
{
  if(data.cancelled || ev.target.readyState != 4) return;

  // replace current document with response
  document.documentElement.innerHTML = ev.target.response;
  window.scrollTo(0, 0);
  $.cache = {};
}

function autoProgressAborted(ev, data)
{
  data.submit.attr('disabled', false);
  data.div.hide();
}

function autoProgressCancel(ev, data)
{
  $(ev.target).attr('disabled', true);
  data.cancelled = true;
  data.xhr.abort();
}

function autoProgressCompleted(data)
{
  data.cancelBtn.attr('disabled', true);
  data.text.text(T_("Completed, waiting..."));
}

function autoProgressCb(ev, data)
{
  data.progress.val(ev.loaded / ev.total);
  if(ev.loaded == ev.total)
    return autoProgressCompleted(data);

  var now = time();
  var elapsed = now - data.start;
  if(elapsed < initialDelay) return null;

  var lastSpeedText = data.speed[1];
  var speedText = lastSpeedText;
  if((now - data.speed[0]) > speedWindow)
  {
    var unit;
    var speed = ev.loaded / (elapsed / 1000) / 1024;

    if(speed < 1024)
      unit = T_('KiB/s');
    else
    {
      speed /= 1024;
      unit = T_('MiB/s');
    }

    speedText = speed.toFixed(3) + ' ' + unit;
    data.speed[0] = now;
  }

  var lastEtaText = data.eta[1];
  var etaText = lastEtaText;
  if((now - data.eta[0]) > etaWindow)
  {
    var speed = ev.loaded / (elapsed / 1000);
    var eta = (ev.total - ev.loaded) / speed;

    var hours = Math.floor(eta / 3600);
    var minutes = Math.floor((eta - hours * 3600) / 60);
    var seconds = Math.floor(eta - hours * 3600 - minutes * 60);

    minutes = ('0' + minutes).slice(-2);
    seconds = ('0' + seconds).slice(-2);

    etaText = T_('ETA') + ' ' + hours + ':' + minutes + ':' + seconds;
    data.eta[0] = now;
  }

  if(speedText != lastSpeedText || etaText != lastEtaText)
  {
    data.speed[1] = speedText;
    data.eta[1] = etaText;
    data.text.text(speedText + ' ' + etaText);
  }
}

function autoProgress(event)
{
  // disable submit
  var form = $(event.target);
  var data = form.data('autoprogress');
  data.submit.attr('disabled', true);

  // event handlers
  var xhr = new XMLHttpRequest();
  data.cancelBtn.click(function(ev) { autoProgressCancel(ev, data); });
  xhr.onreadystatechange = function(ev) { autoProgressState(ev, data); };
  xhr.onabort = function(ev) { autoProgressAborted(ev, data); };
  xhr.upload.onprogress = function(ev) { autoProgressCb(ev, data); };

  // reset
  data.cancelBtn.attr('disabled', false);
  data.cancelled = false;
  data.progress.val(-1);
  data.text.text('');
  data.xhr = xhr;
  data.start = time();
  data.speed = [data.start, ''];
  data.eta = [data.start, ''];

  // start
  event.preventDefault();
  xhr.open("POST", form[0].action);
  xhr.send(new FormData(form[0]));
  data.div.slideDown();
}


// Initialization
$.getCss = function(url)
{
  $('head').append('<link rel="stylesheet" type="text/css" href="' + encodeURI(url) + '"/>');
}

function init()
{
  // form defaults
  $('form[defaults]').each(function(i, t)
  {
    var form = $(t);
    var set = form.attr('defaults');
    loadDefaults(set, t);
    form.find('#setDefaults').click(function(el) { setDefaults(set, t); });
  });

  // togglers
  $('#toggler').click(toggleAdvanced);
  $('#tex').change(function(el) { $('#tex_data').toggle(this.value == 'custom'); }).change();
  $('#gex').change(function(el) { $('#gex_data').toggle(this.value == 'custom'); }).change();

  // file controls
  var ffirst = $('form div.file').first();
  var ftempl = ffirst.clone();
  var rmbtn = $('<input type="button" class="addrm" value="-"/>');
  rmbtn.click(function(el) { $(this).parent().remove(); });
  $('input', ftempl).removeAttr('required');
  ftempl.append(rmbtn);
  var addbtn = $('<input type="button" class="addrm" value="+"/>');
  addbtn.click(function() { ffirst.parent().append(ftempl.clone(true)); });
  ffirst.append(addbtn);

  $('form div.file + div.file').each(function(i, t)
  {
    $(t).append(rmbtn.clone(true));
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
  if($('progress, form.autoprogress').length)
  {
    $.getScript('static/progress-polyfill.js');
    $.getCss('static/progress-polyfill.css');
  }

  // automatic upload progress
  $('form.autoprogress').each(function(i, t) { setupAutoProgress(t); });
}

$(document).ready(init);
