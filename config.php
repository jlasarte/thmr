<?php
define('API_KEY', 'KnhLi5ILJhuVxbLjV8FhLUNhzQQSfAQ0wJ9MkARGhbvd7cEd4F');
define('SECRET_KEY', 'O29pU3xSA53rjAkT2tvnnzar48CoHhSlLmkwnyilxsKmVYFJj6');
define('BLOG_NAME', 'carlangas-theme');
define('API_URL_BLOG', 'http://api.tumblr.com/v2/blog/' );
define('API_URL_USER', 'http://api.tumblr.com/v2/user/');
define('REQUEST_URL_BLOG', API_URL_BLOG.BLOG_NAME.".tumblr.com");
define('THEME_DIR', 'themes/');
define('THEME', 'minimal');
define('BLOG_URL', 'carlangas-a-test-blog.tumblr.com');
define('TEMPLATE_DIR',str_replace("\\","/",getcwd()).'/themes/');
define('LANG_FILE',str_replace("\\","/",getcwd()).'/lang/en-us.yml');
define('CACHE_DIR', str_replace("\\","/",getcwd())."/cache/");

$VERSION = '0.3.0';
$DATA = 'demo.yml';
$LOCALE = 'en-us.yml';