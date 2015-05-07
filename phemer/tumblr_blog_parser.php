<?php
// @author jlasarte
Class TumblrBlogParser {
  
  private $blog;
  private $html;
  private $theme;
  private $vars;

  public static function tocamel($str, $delimiter = '_') {
    // Split string in words.
    $words = explode(':', $str);
    $operation = explode($delimiter, $words[1]);
    $return = '';
    foreach ($operation as $word) {
      $return .= ucfirst($word[0]).substr($word,1);
    }
      return $words[0].":".$return;
  }

  private function replaceSpacesInOptions($document) {
    $document = preg_replace_callback(
        '/{((Color|Font|Lang|Image|Text):([\w ]*))}/is',
        create_function(
            // single quotes are essential here,
            // or alternative escape all $ as \$
            '$matches',
            'return TumblrBlogParser::tocamel($matches[0]," ");'
        ),
        $document
    );
    return $document;
  }

  private static function getThemeMetaTagsOptions($theme) {
    $metatags = get_meta_tags($theme);
    $options = array();
    foreach ($metatags as $key=>$value) {
      if (strpos($key,':') !== false) {
        $options[self::tocamel($key)] = $value;
      }
    }
    return $options;
  }

  public static function getThemeOptions($theme) {
    $options = self::getThemeMetaTagsOptions(TEMPLATE_DIR.$theme);
    $return = "<form class='form-horizontal'>";
    foreach ($options as $key => $value) {
      $label = explode(":", $key);
      $label = $label[0] == 'image' ? $label[1]." image" : $label[1];
      $return .= "<div class='control-group'>";
      $return .= "<label class='control-label' for=".$key.">".$label."</label>";
      $return .= "<div class='controls'>";
      if (strpos($key,'image:') !== false) {
        $return .= "<input type='text' name=".$key." data-type='image' class='configuration image input-small' value=".$value.">";
      } elseif(strpos($key, 'if:') !== false) {
          $checked = $value ? "checked='cheked'" : "";
          $return .= "<input type='checkbox' data-type='if' name=".$key." class='configuration input-small' ".$checked.">";
      } elseif(strpos($key, 'text:') !== false) {
        $return .= "<input type='text'  data-type='text' name=".$key." class='configuration input-small' value=".$value.">";
      } elseif(strpos($key, 'color:') !== false) {
        $return .= "<input type='text'  data-type='color' name=".$key." class='configuration color input-small' value=".$value.">";
      } else {
        $return .= "<input type='text'  data-type='font' name=".$key." class='configuration input-small' value=".$value.">";
      }
      $return .= "</div>";
      $return .= "</div>";
    }
    $return .= "</div>";
    $return .= "</form>";
    return $return;
  }

  /**
   * Jesus fucking christ this is the worst.
   * @param [type] $parser_vars [description]
   */
  public function BuildVarsFromOptions($parser_vars) {
    $options = $this->getThemeMetaTagsOptions(TEMPLATE_DIR.$this->theme);
    $configurations = array(); $vars = array();
    foreach ($options as $key => $value) {
      if (isset($_SESSION['configurations'][$key])) {
        $val = $_SESSION['configurations'][$key];
        $skip = true;
      } else {
        $val = $value;
        $skip = false;
      }
      if (strpos($key,'image:') !== false) {
        $image_name = explode(":", $key);
        if ($val) {
          $vars['block:If'.$image_name[1]."Image"] = new TumblrOptionalParser(true);
          if (!$skip) {
            $configurations['block:IfNot'.$image_name[1]."Image"] = false;
            $configurations['block:If'.$image_name[1]."Image"] = true;
          }
          $vars['image:'.$image_name[1]] = $val;
        } else {
          $vars['block:If'.$image_name[1]."Image"] = new TumblrOptionalParser(false);
           if (!$skip) {
            $configurations['block:IfNot'.$image_name[1]."Image"] = true;
            $configurations['block:If'.$image_name[1]."Image"] = false;
           }
          $vars['image:'.$image_name[1]] = $val;
          $vars['block:IfNot'.$image_name[1]."Image"] = new TumblrOptionalParser(true);
        }
      } elseif(strpos($key, 'if:') !== false) {
          $block_name = explode(":", $key);
          if($val) {
            $block = 'block:If'.$block_name[1];
            $vars[$block] = new TumblrOptionalParser(true, $block);
             if (!$skip) { 
                $configurations[$block] = true;
                $configurations['block:IfNot'.$block_name[1]] = false;
             }
          } else {
            $vars['block:IfNot'.$block_name[1]] = new TumblrOptionalParser(true);
            if (!$skip) { 
              $configurations['block:IfNot'.$block_name[1]] = true;
            }
          }
      } elseif(strpos($key, 'text:') !== false) {
          $block_name = explode(":", $key);
          if($val) {
            $block = 'block:If'.$block_name[1];
            $vars[$block] = new TumblrOptionalParser(true, $block);
            if (!$skip) { 
              $configurations[$block] = true;
              $configurations['block:IfNot'.$block_name[1]] = false;
              $configurations[$key] = $val;
            }
            $vars[$key] = $val;
          } else {
            $vars['block:IfNot'.$block_name[1]] = new TumblrOptionalParser(true);
            if (!$skip) { 
              $configurations['block:IfNot'.$block_name[1]] = true;
            }
          }
      } else {
          $vars[$key] = $val;
          if (!$skip) { 
            $configurations[$key] = $val;
          }
      }
    }
    $_SESSION['configurations'] = array_merge($_SESSION['configurations'], $configurations);
    return $vars;
  }

  public function __construct($vars, $theme) {
    $this->theme = $theme;
    $this->html = file_get_contents(TEMPLATE_DIR.$theme);
    $this->html = $this->replaceSpacesInOptions($this->html);

    $this->vars = array(
        'Title'=>$vars['title'],
        'block:Description'=> new TumblrSubParser(
          array('MetaDescription'=>$vars['meta_description'])),
        'block:SearchPage'=>new TumblrOptionalParser($vars['search_page'],
          array(
            'SearchQuery' => $vars['search_query'],
            'URLSafeSearchQuery' => urlencode($vars['search_query']),
            'SearchResultCount' => $vars['search_count'],
            )
          ),
        'block:NoSearchResults'=> new TumblrOptionalParser($vars['no_results'],
          array(
            'SearchQuery' => $vars['search_query'],
            'URLSafeSearchQuery' => urlencode($vars['search_query']),
            'SearchResultCount' => $vars['search_count'],
            )
          ),
        'block:TagPage'=>new TumblrOptionalParser($vars['tag_page'],
          array('Tag'=>$vars['page_tag'])
          ),
        'block:AskEnabled'=>new TumblrOptionalParser($vars['ask_enabled']),
        'block:SubmissionsEnabled'=>new TumblrOptionalParser($vars['submission_enabled']),
        'block:HasPages'=>new TumblrOptionalParser(true, 
          array('block:Pages'=> new TumblrSubParser(
            $vars['pages']
            )
        )),
        'Description'=>$vars['description'],
        'block:Posts' => new TumblrLoopParser($vars['posts']),
        'RSS'=>$vars['rss'],
        'AskLabel'=>$vars['ask_label'],
        'CopyrightYears'=>$vars['copyright'],
        'Favicon'=>$vars['favicon'],
        'CurrentPage'=>1,
        'TotalPages'=>5,
        'PortraitURL-16'=>$vars['PortraitURL-16'],
        'PortraitURL-24'=>$vars['PortraitURL-24'],
        'PortraitURL-30'=>$vars['PortraitURL-30'],
        'PortraitURL-40'=>$vars['PortraitURL-40'],
        'PortraitURL-48'=>$vars['PortraitURL-48'],
        'PortraitURL-64'=>$vars['PortraitURL-64'],
        'PortraitURL-96'=>$vars['PortraitURL-96'],
        'PortraitURL-128'=>$vars['PortraitURL-128'],
        'block:IndexPage'=> new TumblrOptionalParser($vars['index_page']),
        'block:PermalinkPage'=> new TumblrOptionalParser($vars['permalink_page']),
        'block:English'=> new TumblrOptionalParser(true)
    );
    $this->vars = array_merge($this->vars, $this->BuildVarsFromOptions());
  }

  public function parse() {
    $documentParser = new TumblrParser($this->vars,array('skinnable'=>true));
    echo $documentParser->parse($this->html);
  }

}