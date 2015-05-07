<?php
session_start();
$theme = $_GET['theme'];


function require_directory($directory) {
   foreach (glob($directory."/*.php") as $filename)
    {
        require_once $filename;
    }
}

function tocamel($str, $delimiter = '_') {
  // Split string in words.
  $words = explode(':', $str);
  $operation = explode($delimiter, strtolower($words[1]));
  $return = '';
  foreach ($operation as $word) {
    $return .= ucfirst(trim($word));
  }
    return $words[0].":".$return;
}

require_once 'config.php';
require_once 'helpers/html2text.php';
require_once 'helpers/request_http.php';

require_once 'models/post.php';
require_once 'models/post_factory.php';
require_once 'models/photo_post.php';

require_once 'phemer/tumblr_parser.php';
require_once 'phemer/tumblr_posts_parser.php';
require_once 'libs/kint/Kint.Class.php';

require_directory('models');




//$tumblr_blog = new TumblrBlog(BLOG_URL);
//$s = serialize($tumblr_blog);
//file_put_contents('store', $s);


$s = file_get_contents('store');
$tumblr_blog = unserialize($s);

$VERSION = '0.3.0';
$DATA = 'demo.yml';
$LOCALE = 'en-us.yml';

$metatags = get_meta_tags(TEMPLATE_DIR.$theme);
$options = array();
foreach ($metatags as $key=>$value) {
  if (strpos($key,':') !== false) {
    $options[tocamel($key)] = $value;
  }
}
$parserVars =  array('Title'=>$tumblr_blog->getTitle(),
        'block:Description'=> new TumblrSubParser(array('MetaDescription'=>$tumblr_blog->MetaDescription())),
        'block:SearchPage'=>new TumblrConditionalParser($tumblr_blog,'SearchPage'),
        'block:AskEnabled'=>new TumblrConditionalParser($tumblr_blog,'AskEnabled'),
        'block:SubmissionsEnabled'=>new TumblrConditionalParser($tumblr_blog,'SubmissionsEnabled'),
        'block:HasPages'=>new TumblrConditionalParser($tumblr_blog,'HasPages', 
          array('block:Pages'=> new TumblrSubParser(
            array('Label' =>'A single Page')
            )
        )),
        'Description'=>$tumblr_blog->Description(),
        'block:Posts' => new TumblrLoopParser($tumblr_blog->posts()),
        'CustomCss'=>'',
        'RSS'=>$tumblr_blog->rss(),
        'AskLabel'=>'LALALA',
        'CopyrightYears'=>'2006-2013',
        'Favicon'=>$tumblr_blog->Portrait(16),
        'PortraitURL-16'=>$tumblr_blog->Portrait(16),
        'PortraitURL-24'=>$tumblr_blog->Portrait(24),
        'PortraitURL-30'=>$tumblr_blog->Portrait(30),
        'PortraitURL-40'=>$tumblr_blog->Portrait(40),
        'PortraitURL-48'=>$tumblr_blog->Portrait(48),
        'PortraitURL-64'=>$tumblr_blog->Portrait(64),
        'PortraitURL-96'=>$tumblr_blog->Portrait(96),
        'PortraitURL-128'=>$tumblr_blog->Portrait(128),
        'block:IndexPage'=> new TumblrOptionalParser(true)
    );
$configurations = array();
foreach ($options as $key => $value) {
  if (strpos($key,'image:') !== false) {
    $image_name = explode(":", $key);
    if ($value) {
      $parserVars['block:If'.$image_name[1]."Image"] = new TumblrOptionalParser(true);
      $configurations['block:IfNot'.$image_name[1]."Image"] = false;
      $configurations['block:If'.$image_name[1]."Image"] = true;
      $parserVars['image:'.$image_name[1]] = $value;
    } else {
      $parserVars['block:IfNot'.$image_name[1]."Image"] = new TumblrOptionalParser(true);
      $configurations['block:IfNot'.$image_name[1]."Image"] = true;
    }
  } elseif(strpos($key, 'if:') !== false) {
      $block_name = explode(":", $key);
      if($value) {
        $block = 'block:If'.$block_name[1];
        $parserVars[$block] = new TumblrOptionalParser(true, $block);
        $configurations[$block] = true;
        $configurations['block:IfNot'.$block_name[1]] = false;
      } else {
        $parserVars['block:IfNot'.$block_name[1]] = new TumblrOptionalParser(true);
        $configurations['block:IfNot'.$block_name[1]] = true;
      }
  } else {

      $parserVars[$key] = $value;
      $configurations[$key] = $value;
  }
}

$configurations['block:IndexPage'] = true;
$_SESSION['configurations'] = $configurations;

// master template parser. We link all the rules and subtemplates to this instance
$documentParser = new TumblrParser($parserVars,array('skinnable'=>true));
// load, parse and output the document
$document = file_get_contents(TEMPLATE_DIR.$theme);
$preg = '/{block:([A-Za-z][A-Za-z0-9]*)}(.*?){\/block:\1}|{(out:)?([A-Za-z][A-Za-z0-9][\w\'-]*)}|{((Color|Font|Lang):([A-Za-z][A-Za-z0-9]*))}/is';
//preg_match_all($preg, $document, $matches);
$document = preg_replace_callback(
        '/{((Color|Font|Lang|Image|Text):([\w ]*))}/is',
        create_function(
            // single quotes are essential here,
            // or alternative escape all $ as \$
            '$matches',
            'return tocamel($matches[0]," ");'
        ),
        $document
    );

//d($parserVars);
//$data = Spyc::YAMLLOAD('data/demo.yml');
//$movies = new SimpleXMLElement(file_get_contents('data/data.xml'));
//$posts = $tumblr_blog->posts();
//d($posts);
echo $documentParser->parse($document);
