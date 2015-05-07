<?php
/**
 * Description of TumblrBlog
 *
 * @author jlasarte
 */
Class TumblrBlog {
 /**
 *
 * @var string the blog title as defined by the user; 
 */
  private $title  = '';
  /**
   *
   * @var string the blog as it figures on the blog url. 
   */
  private $name   = '';
  /**
   *
   * @var string the blog url. 
   */
  private $url    = '';
  /**
   *
   * @var string the blog description. Can be empty. 
   */
  private $description = '';
  /**
   *
   * @var boolean indicated wheter the asks are enabled on this blog. 
   */
  private $ask_enabled = '';
  
  //TODO: limit the number of posts for big blogs.
  /**
   *
   * @var Posts[] the posts for this blog.  
   */
  private $posts  = array();
  /**
   *
   * @param type $blog_url
   * @return type 
   */
  static function get_blog_info( $blog_url ) {
    $request_info_url = API_URL_BLOG.$blog_url.'/info';
    $params = array('api_key'=>API_KEY);
    $response = request_http($request_info_url, $params, 'GET' );
    $response = json_decode($response['content'], true);
    return $response['response']['blog'];
  }

  private function requestUserPortrait($size) {
        $this->portrait = API_URL_BLOG.$this->url_clean.'/avatar/';
        $request_info_url = $this->portrait.$size; 
        $headers = get_headers($request_info_url,1);
        return $headers['Location'];
  }

  static function get_blog_posts($blog_url){

    $request_info_url = API_URL_BLOG.$blog_url.'/posts';
    $params = array('api_key'=>API_KEY, 'reblog_info'=>true, 'notes_info'=>true);
    $response = request_http($request_info_url, $params, 'GET' );
    $response = json_decode($response['content'], true);
    $posts = $response['response']['posts'];
    $posts_objects_array = array();
    $date = "";
    foreach ($posts as $post) {
        if(date("Ymd",$post['timestamp']) <> $date) {
          $post['new_day'] = true;
          $date = date("Ymd",$post['timestamp']);
        }
        $posts_objects_array[(string)$post['id']] = PostFactory::Create($post);
    }
    return $posts_objects_array;
  }

  public function __construct($blog_url) {
    
    $blog_info = $this::get_blog_info($blog_url);
    $posts = $this::get_blog_posts($blog_url);
    $this->url_clean = $blog_url;
    $this->title = $blog_info['title'];
    $this->name = $blog_info['name'];
    $this->url = $blog_info['url'];
    $this->description = $blog_info['description'];
    $this->ask_enabled = $blog_info['ask'];
    $this->posts = $posts;

  }

  public function getTitle() {
    return $this->title;
  }

  public function Description() {
    if  (!empty($this->description)) {
      return $this->description;
    } else {
      return false;
    }
  }

  public function AskEnabled()
  {
    return $this->ask_enabled;
  }

  public function HasPages()
  {
    return true;
  }

  public function IfHeaderImage(){
    return false;
  }

  public function IfNotHeaderImage(){
    return true;
  }

  public function MetaDescription() {
    return html2text($this->description);
  }

  public function SearchPage() {
    return false;
  }

  public function Portrait($size)
  {
    return 'http://assets.tumblr.com/images/default_avatar_'.$size.'.gif';
  }

  public function SubmissionsEnabled(){
    return true;
  }

  public function rss() {
    return $this->url."rss";
  }

  public function tagged($tag) {
    $posts = array_filter($this->posts, array(new TagFilter($tag), 'hasTag'));
    return $posts;
  }
  
 /**
 * Generic getter.
 * @return array the posts for this blog. 
 */
  public function posts(){
      return $this->posts;
  }


}

class TagFilter {
  private $tag;

  function __construct($tag) {
    $this->tag = $tag;
  }

  function hasTag($post) {
    return $post->hasTag($this->tag);
  }
}