<?php

/**
 * Controlador para el Home o pagina principal.
 * @author Julia Lasarte
 * @package controllers
 * @see Controller
 */
Class BlogController extends Controller {

    private $blog;
    private $theme;

    public function __construct($theme) {
        // if the theme has changes we need to clear the configurations
        // or if the variable is empty, we initialize it to an empty array.
        if ((!isset($_SESSION['configurations'])) || ($_SESSION['current_theme'] != $theme)) {
            $_SESSION['configurations'] = array();
        } 
        
        $_SESSION['current_theme'] = $theme;
        $this->blog = unserialize(file_get_contents(CACHE_DIR.'blog'));
        $this->theme = $theme;
        $this->default_vars = array(
            'title'=> $this->blog->getTitle(),
            'description'=>$this->blog->Description(),
            'meta_description' => $this->blog->MetaDescription(),
            'ask_enabled'=>$this->blog->AskEnabled(),
            'submissions_enabled'=>$this->blog->SubmissionsEnabled(),
            'has_pages'=>$this->blog->HasPages(),
            'pages'=> array('Label' =>'About', 'Label' =>'Bio', 'Label' =>'Stuff'),
            'rss'=> $this->blog->rss(),
            'ask_label'=> 'Ask me Anything',
            'copyright'=> '2006-2013',
            'favicon'=> $this->blog->Portrait(16),
            'PortraitURL-16'=>$this->blog->Portrait(16),
            'PortraitURL-24'=>$this->blog->Portrait(24),
            'PortraitURL-30'=>$this->blog->Portrait(30),
            'PortraitURL-40'=>$this->blog->Portrait(40),
            'PortraitURL-48'=>$this->blog->Portrait(48),
            'PortraitURL-64'=>$this->blog->Portrait(64),
            'PortraitURL-96'=>$this->blog->Portrait(96),
            'PortraitURL-128'=>$this->blog->Portrait(128),
            'index_page'=>false,
            'search_page'=>false,
            'tag_page'=>false,
            'posts'=>$this->blog->posts()
            );
    }

    /**
     * Muestra la pagina principal del sitio.
     * @param int $page numero de pagina a mostrar.
     */
    public function index($page = 0) {
        $vars = $this->default_vars;
        $vars['index_page'] = true;
        $_SESSION['index_page'] = true;
        $parser = new TumblrBlogParser($vars, $this->theme);
        $parser->parse();
    }

    public function tagged($tag) {
        $vars = $this->default_vars;
        //$vars['search_page'] = true;
        $vars['posts'] = $this->blog->tagged($tag);
        $vars['page_tag'] = $tag;
        $vars['tag_page'] = true;
        $parser = new TumblrBlogParser($vars, $this->theme);
        $parser->parse();
    }

    public function search($query) {
        $vars = $this->default_vars;
        $vars['search_page'] = true;
        if($query <> "no_results") {
            $vars['search_query'] = $query;
            $vars['search_count'] = rand();
            shuffle($vars['posts']);
        } else {
            $vars['search_query'] = $query;
            $vars['no_results'] = true;
            $vars['posts'] = array();
        }
        $parser = new TumblrBlogParser($vars, $this->theme);
        $parser->parse();
    }

    public function post($post_id) {
        $vars = $this->default_vars;
        $vars['permalink_page'] = true;
        $_SESSION['permalink_page'] = true;
        $vars['posts'] = array($vars['posts'][$post_id]);
        $parser = new TumblrBlogParser($vars, $this->theme);
        $parser->parse();
    }

    public function get_options() {
        echo TumblrBlogParser::getThemeOptions($this->theme);
    }

    public function set_option($configuration) {
        $configuration = json_decode($configuration, true);
        Configuration::setConfiguration($configuration['name'], $configuration['value'], $configuration['type']);
    }

    public function reset_to_defaults() {
        session_destroy();
    }

    public function import_blog_data($blog_url) {
        $blog_url = $blog_url.".tumblr.com";
        $this->blog = new TumblrBlog($blog_url);
        $s = serialize($this->blog);
        file_put_contents(CACHE_DIR.'blog', $s);
    }

    public function refresh_blog_data() {
        $blog = unserialize(file_get_contents(CACHE_DIR.'blog'));
        $this->blog = new TumblrBlog($blog->url_clean);
        $s = serialize($this->blog);
        file_put_contents(CACHE_DIR.'blog', $s);
    }


}