<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <title><?php echo strip_tags($title); ?></title>
      <link rel="stylesheet" type="text/css" href="style/static/view.css" media="all"/>
      <script type="text/javascript" src="static/jquery.js"></script>
      <script type="text/javascript" src="static/dl.js"></script>
      <script type="text/javascript" src="style/static/view.js"></script>
    </head>
    <body>
	  <div id="navbar-inner">
	    <div id="container">
	      <h1>
	      <img src="style/static/dl.png" alt=""/>
	      <a>Ticket Service</a>
	      </h1>
	    </div>
	  </div>
    <br>
    <div id="form_container">
	<?php
          if(!empty($ref))
	  {
	    echo '<div id="langmap">';

	    $first = true;
            foreach($langData as $k => $v)
	    {
	      if($first) $first = false;
	      else echo " | ";

	      $K = strtoupper($k);
	      $name = htmlentities($v['name']);

	      if($locale == $v['locale'])
		echo "<span title=\"$name\">$K</span>";
	      else
		echo "<a href=\"$ref&lang=$k\" title=\"$name\">$K</a>";
	    }
	    echo '</div>';
	  }
          ?>
	<div class="appnitro">
	  <div class="form_description">
	    <h2><?php echo $title; ?></h2>
	  </div>
