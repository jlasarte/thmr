<?php

Class Configuration {

  public static function setConfiguration($name, $value, $type) {
    switch ($type) {
      case "color":
        $_SESSION['configurations'][$name] = $value;
        break;
      case "font":
        $_SESSION['configurations'][$name] = $value;
        break;
      case "if":
        $block_name = explode(":", $name);
        $block = 'block:If'.$block_name[1];
        if($value != "false") {
          $_SESSION['configurations'][$block] = true;
          $_SESSION['configurations']['block:IfNot'.$block_name[1]] = false;
          $_SESSION['configurations'][$name] = true;
        } else {
          $_SESSION['configurations']['block:IfNot'.$block_name[1]] = true;
          $_SESSION['configurations'][$block] = false;
          $_SESSION['configurations'][$name] = false;
        }
        break;
      case "text" :
        $block_name = explode(":", $name);
        if($value) {
          $block = 'block:If'.$block_name[1];
          $_SESSION['configurations'][$block] = true;
          $_SESSION['configurations']['block:IfNot'.$block_name[1]] = false;
          $_SESSION['configurations'][$name] = $value;
        } else {
          $_SESSION['configurations']['block:IfNot'.$block_name[1]] = true;
        }
        break;

      case "image":
        $image_name = explode(":", $name);
        if (!(ctype_space($value) || $value == '')) {
          $_SESSION['configurations']['block:IfNot'.$image_name[1]."Image"] = false;
          $_SESSION['configurations']['block:If'.$image_name[1]."Image"] = true;
          $_SESSION['configurations']['image:'.$image_name[1]] = $value;
        } else {
          $_SESSION['configurations']['block:IfNot'.$image_name[1]."Image"] = true;
          $_SESSION['configurations']['block:If'.$image_name[1]."Image"] = false;
          $_SESSION['configurations']['image:'.$image_name[1]] = false;
        }
      break;
    }
  }

  public static function getConfiguration($name) {
    if (isset($_SESSION[$name])) {
      return $_SESSION[$name];
    } else {
      return false;
    }
  }

}

?>