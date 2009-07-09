<html>
  <head>
    <link href="style.css" rel="stylesheet" type="text/css"/>
    <script src="shared.js" type="text/javascript"></script>
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
      <input type="reset" value="Reset"/>
      <input type="button" value="Set as defaults" onclick="setDefaults();"/>
    </form>
    <div class="nav">
      <a href="<?php echo $masterPath; ?>?l">List active tickets</a>,
      <a href="<?php echo $masterPath; ?>?p">Logout</a>
    </div>
  </body>
</html>
