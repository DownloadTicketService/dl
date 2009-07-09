<html>
  <head>
    <script>
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

      function setConds()
      {
	document.getElementById("conds").style.display =
	  (document.forms[0].nl.checked? 'none': 'block');
      }
    </script>
  </head>
  <body onload="loadDefaults();">
    <form action="<?php echo $masterPath; ?>"
	enctype="multipart/form-data" method="post"
	onsubmit="document.forms[0].submit.disabled = true;">
      <input type="hidden" name="max_file_size" value="<?php echo $iMaxSize; ?>"/>
      <table><tr>
	<td>File:</td>
	<td>
	  <input name="file" type="file"/><br/>
	  <small>current max file size: <?php echo $hMaxSize; ?></small>
	</td>
      </tr><tr>
	<td>Comment:</td>
	<td><input name="cmt" type="text"/></td>
      </tr><tr>
	<td>Validity:</td>
	<td>
	  <div>
	    <input name="nl" type="checkbox" onclick="setConds();">Permanent ticket</input>
	  </div><div id="conds">
	    <input name="hr" type="text" value="168"/> total hours, or<br/>
	    <input name="hra" type="text" value="24"/> hours after last download, or<br/>
	    <input name="dln" type="text" value="0"/> downloads.<br/>
	    <small>enter 0 to disable the condition</small>
	  </div>
	</td>
      </tr><tr>
	<td>Notify:</td>
	<td><input name="nt" type="text"/> emails (comma separated)</td>
      </tr></table>
      <input type="submit" name="submit" value="Upload"/>
      <input type="button" value="Set as defaults" onclick="setDefaults();"/>
    </form>
    <hr/>
    <a href="<?php echo $masterPath; ?>?l">List active tickets</a>,
    <a href="<?php echo $masterPath; ?>?p">Logout</a>
  </body>
</html>
