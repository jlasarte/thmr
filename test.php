<?php
error_reporting(E_ERROR | E_PARSE );

session_start();
$theme  = $_GET['theme'];
$operation  = $_GET['operation'];
$params = $_GET['params'];

function require_directory($directory) {
   foreach (glob($directory."/*.php") as $filename)
    {
        require_once $filename;
    }
}
require_once 'config.php';
require_once 'helpers/html2text.php';
require_once 'helpers/request_http.php';
require_once 'helpers/simple_html_dom.php';
require_once 'helpers/spyc.php';

require_once 'models/post.php';
require_once 'models/post_factory.php';
require_once 'models/photo_post.php';
require_once 'controllers/controller.php';

require_once 'phemer/tumblr_parser.php';
require_once 'phemer/tumblr_posts_parser.php';
require_once 'phemer/tumblr_blog_parser.php';
require_once 'libs/kint/Kint.Class.php';

require_directory('models');
require_directory('controllers');

$_SESSION['localization'] = Spyc::YAMLLoad(LANG_FILE);
$_SESSION['current_operation'] = $operation;
$controller = new BlogController($theme);
$controller->$operation($params);
