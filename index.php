<!DOCTYPE html>
<html dir="ltr" lang="en-US" id="thimblr">
<head>
  <meta charset="utf-8" />
  <title>Hint</title>
  <meta name="Copyright" content="Copyright (c) 2013 lasarte" />
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js"></script>
  <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/themes/ui-lightness/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script type="text/javascript" src="assets/colorpicker.js"></script> 
  <script type="text/javascript" src="assets/js/jquery.tinyscrollbar.min.js"></script>   
  <script type="text/javascript" src="assets/bootstrap/js/bootstrap.js"></script> 
  <script type="text/javascript" src="assets/main.js"></script> 

    <link href="assets/colorpicker.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/nanoscroller.css" rel="stylesheet" type="text/css"/>
    <link href="assets/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>

  <style type="text/css">
    body {
      background-color: #4e545b;
    }
    #theme-container {position:absolute; left:0px; right:0px; top:41px; bottom:0px;}
    #theme-preview {width:100%; height:100%;}
    #theme-options {
      color: #fff;
      width:23%; 
      float:right;
    }
    #theme-options h4 {
      font-size: 11px;
      color: white;
      text-transform: uppercase;
      padding-left: 15px;
      cursor: pointer;
      position: relative;
    }

    .form-horizontal .control-label {
      float: left;
      width: 130px;
      padding-top: 5px;
      text-align: right;
      font-size: 13px;
    }

    .form-horizontal .control-group {
      margin-bottom: 0px;
    }

    .form-horizontal .controls {
      margin-left: 150px;
    }

    #load-screen {
      width: 100%;
      height: 100%;
      background: url("assets/images/ajax-loader.gif") no-repeat center center #000;
      position: fixed;
      opacity: 0.7;
    }

    #reset-defaults {
      margin: 10px;
    }

    #options-heading {
      margin-bottom: 10px;
      border-bottom: 1px solid black;
    }
  </style>
</head>
<body>

  <div id="import-modal" class="modal hide fade">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3>Import Data</h3>
    </div>
    <div class="modal-body">
      <p>Enter here the url of the blog to import from.</p>
      <div class="input-append input-prepend">
        <span class="add-on">http://</span>
        <input class="span2" id="blog-url" type="text">
        <span class="add-on">.tumblr.com</span>      
      </div>
    </div>
    <div class="modal-footer">
      <a class="btn" data-dismiss="modal" >Close</a>
      <a id="import-data" class="btn btn-primary">Import</a>
    </div>
  </div>

  <div class="navbar navbar-inverse navbar-static-top">
  <div class="navbar-inner">
    <a class="brand" href="#">Hint</a>
    <form class="navbar-form pull-left" method="get" action="theme.php" id="theme-select">
    <select name="theme" id="theme-selector">
      <option> Select a theme</option>
      <?php
        foreach (glob('themes/*.html') as $theme) {
          $theme = basename($theme);
          if (($theme !== '.') && ($theme !== '..')) {
            echo "<option value=\"$theme\">$theme</option>\n";
          }
        }
      ?>
    </select>
  </form>
  <ul class="nav pull-right">
  <li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
      Import Data
      <b class="caret"></b>
    </a>
    <ul class="dropdown-menu">
      <li> <a id="refresh-data"> Refresh </a> </li>
      <li> <a  href="#import-modal" role="button" data-toggle="modal" id="set-data"> Import from other blog </a> </li>
    </ul>
  </li>
  <li><a id="set-options">Theme Options</a></li>
</ul>
  </div>
</div>
  
  <div id="theme-container">
    <iframe id="theme-preview" border="0" frameborder="0"></iframe>
    
    <div id="theme-options" class="nano">
      <div class="content">
        <div id="options-heading" class="clearfix">
          <h4 class="pull-left">Apariencia</h4>
          <a class="btn btn-mini pull-right btn-inverse" id="reset-defaults"> Reset to defaults </a>
        </div>
        <div id="options-list">
        </div>
      </div>
    </div>

  </div>
  
</body> 
</html>
