<?php
/***
 * 
 */
abstract Class TumblrPost { 
  /**
   * @var longint The post's unique ID 
   */
  protected $id;  
  /**
   * @var string The location of the post 
   */
  protected $post_url;
   /**
   *
   * @var string The time of the post, in seconds since the epoch   
   */
   protected $timestamp;
  /**
   *
   * @var string The GMT date and time of the post, as a string 
   */
  protected $date;
  /**
   *
   * @var string The post format: html or markdown 
   */
  protected $format;
  /**
   *
   * @var string The key used to reblog this post  See the /reblog method 
   */
  protected $reblog_key;
   /**
   *
   * @var string[] Tags applied to the post 
   */
   protected $tags;
  /**
   *
   * @var string  The URL for the source of the content (for quotes, reblogs, etc.) Exists only if there's a content source
   */
  protected $source_url;
  /**
   *
   * @var string  The title of the source site  Exists only if there's a content source
   */
  protected $source_title;

  protected $rawData;
  protected $shortURL;
  protected $newDayDate;
  
  /**
   * If the post if a reblog, this array contains the following fieds:
   * reblogged_from_id, reblogged_from_url, reblogged_from_name, reblogged_from_title,
   * reblogged_root_url, reblogged_root_name, reblogged_root_title.
   * @var array the information from the reblog. 
   */
  protected $reblogged_info = array();
  
  protected $is_reblog = null;
  
  protected $conditional_fields = array();
  
  private function requestUserPortrait($size, $user) {
    $request_info_url = API_URL_BLOG.$user.".tumblr.com/avatar/".$size;        
    $headers = get_headers($request_info_url,1);
    return $headers['Location'];
  }

  public function english() {
    return true;
  }

  private static function day_difference($time){
    $time = time() - $time; // to get the time since that moment

    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
    }
  }
  
  private function isThisReblog(){
    if ($this->is_reblog == null) {
      $request_info_url = API_URL_BLOG.BLOG_URL.'/posts';
      $params = array('api_key'=>API_KEY, 'id'=>$this->id, 'reblog_info'=>'true');
      $response = request_http($request_info_url, $params, 'GET' );
      $response = json_decode($response['content'], true);
      if (isset($response['response']['posts'][0]['reblogged_from_id'])) {
        $post = $response['response']['posts'][0];
        $this->reblogged_info = array(
          'reblogged_from_id'    => $post['reblogged_from_id'],
          'reblogged_from_url'   => $post['reblogged_from_url'],
          'reblogged_from_name'  => $post['reblogged_from_name'],
          'reblogged_from_title' => $post['reblogged_from_title'],
          'reblogged_root_url'   => $post['reblogged_root_url'],
          'reblogged_root_name'  => $post['reblogged_root_name'],
          'reblogged_root_title' => $post['reblogged_root_title']
          );
        $this->is_reblog = true;
      } else {
        $this->is_reblog = false;
      }
    }
    return $this->is_reblog;
    
  }
  
   /**
   * Template method for the instansiation of a post object.
   * 
   * @param array $post Post data as returned by the Tumblr API 
   */
   public function __construct() {
    $args = func_get_args();
    $post = array_shift($args);
    $this->shortURL = $post['short_url'];
    $this->id = $post['id'];
    $this->post_url = $post['post_url'];
    $this->timestamp = $post['timestamp'];
    $this->date = $post['date'];
    $this->format = $post['format'];
    $this->reblog_key = $post['reblog_key'];
    $this->notes = $post['note_count'];
    $this->rawData = $post;
    $this->newDayDate = $post['new_day'] ? $post['new_day'] : false;
    foreach ($post['tags'] as $tag) {
      $this->tags[] = new Tag($tag);
    }
    $this->source_url = isset($post['source_url']) ? $post['source_url'] : '';
    $this->source_title = isset($post['source_title']) ? $post['source_title'] : '';
    $this->constructAdditionalFields($post);
  }
  
  public function rendered() {
    $parser = new ThmrParser;
    $block = $parser->getBlockWithLabel($this->getType());
    $block = $this->renderBlock($block, $parser);
    echo $block;

  }
  
  public function getVariableValues($variables) {
    $output = array();
    foreach( $variables as $variable) {
      $method = lcfirst(str_replace("-", "", $variable));
      try {
        $output[$variable] = $this->$method();
      } catch (TagNotSupportedException $e) {
        $output[$variable] = false;
      }

    }
    return $output;
  }
  
  /**
   *
   * @return String The name of the current post type.
   */
  public function postType(){
    return $this->getType();
  }

  public function tags() {
    return $this->tags;
  }
  
  /**
   *
   * @return String  The permalink of the post. 
   */
  public function permalink(){
    //return $this->post_url;
    return "./#/".$_SESSION['current_theme']."/post/".$this->postID();
  }
  
  /**
   *
   * @return longint the numeric ID of the post. 
   */
  public function postID() {
    return $this->id;
  }
  
  public function reblogParentName() {
    if ($this->is_reblog) {
      return $this->reblogged_info['reblogged_from_name'];
    }
  }
  
  public function reblogParentTitle() {
    if ($this->is_reblog) {
      return $this->reblogged_info['reblogged_from_title'];
    }
  }
  
  public function reblogParentUrl(){
    if ($this->is_reblog) {
      return $this->reblogged_info['reblogged_from_url'];
    }
  }
  
  public function reblogRootName(){
    if ($this->is_reblog) {
      return $this->reblogged_info['reblogged_root_name'];
    }
  }
  
  public function reblogRootTitle(){
    if ($this->is_reblog) {
      return $this->reblogged_info['reblogged_root_title'];
    }
  }
  
  public function reblogRootURL(){
    if ($this->is_reblog) {
      return $this->reblogged_info['reblogged_root_URL'];
    }
  }

  public function postNotes(){
    $notes = '<ol class="notes" style="display: block;"><li class="note like tumblelog_awhitewingdove without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://awhitewingdove.tumblr.com/" title="Untitled "><img src="http://24.media.tumblr.com/avatar_9ac0406f50ad_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://awhitewingdove.tumblr.com/" title="Untitled">awhitewingdove</a> likes this </span><div class="clear"></div></li><li class="note like tumblelog_solar-panels-in-essex without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://solarpanelsessex.net/" title="Solar Panels Essex "><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://solarpanelsessex.net/" title="Solar Panels Essex">solar-panels-in-essex</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_solar-panels-in-essex without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://solarpanelsessex.net/" title="Solar Panels Essex"><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action" data-post-url="http://solarpanelsessex.net/post/40456813075"><a rel="nofollow" href="http://solarpanelsessex.net/" class="tumblelog" title="Solar Panels Essex">solar-panels-in-essex</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note like tumblelog_healthinfood without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://www.healthinfood.com/" title="Untitled "><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://www.healthinfood.com/" title="Untitled">healthinfood</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_healthinfood without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://www.healthinfood.com/" title="Untitled"><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action" data-post-url="http://www.healthinfood.com/post/40415172287"><a rel="nofollow" href="http://www.healthinfood.com/" class="tumblelog" title="Untitled">healthinfood</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note reblog tumblelog_whitten-metalworks without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://whitten-metalworks.tumblr.com/" title="Untitled"><img src="http://25.media.tumblr.com/avatar_808918106724_16.png" class="avatar " alt=""></a><span class="action" data-post-url="http://whitten-metalworks.tumblr.com/post/40181301787"><a rel="nofollow" href="http://whitten-metalworks.tumblr.com/" class="tumblelog" title="Untitled">whitten-metalworks</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note like tumblelog_whitten-metalworks without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://whitten-metalworks.tumblr.com/" title="Untitled "><img src="http://25.media.tumblr.com/avatar_808918106724_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://whitten-metalworks.tumblr.com/" title="Untitled">whitten-metalworks</a> likes this </span><div class="clear"></div></li><li class="note like tumblelog_discount-deal without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://discount-deal.tumblr.com/" title="your shopping buddy "><img src="http://25.media.tumblr.com/avatar_bb44c9810e19_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://discount-deal.tumblr.com/" title="your shopping buddy">discount-deal</a> likes this </span><div class="clear"></div></li><li class="note like tumblelog_creation-site-64 without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://creation-site-64.tumblr.com/" title="cheapsitebab creation site referencement "><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://creation-site-64.tumblr.com/" title="cheapsitebab creation site referencement">creation-site-64</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_amateur--girls without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://amateur--girls.tumblr.com/" title="Untitled"><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action" data-post-url="http://amateur--girls.tumblr.com/post/38370793631"><a rel="nofollow" href="http://amateur--girls.tumblr.com/" class="tumblelog" title="Untitled">amateur--girls</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note like tumblelog_thinspiration-pro-ana without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://www.thinspirationproanatips.com/" title="Untitled "><img src="http://25.media.tumblr.com/avatar_870bee60d997_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://www.thinspirationproanatips.com/" title="Untitled">thinspiration-pro-ana</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_thinspiration-pro-ana without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://www.thinspirationproanatips.com/" title="Untitled"><img src="http://25.media.tumblr.com/avatar_870bee60d997_16.png" class="avatar " alt=""></a><span class="action" data-post-url="http://www.thinspirationproanatips.com/post/37011852764"><a rel="nofollow" href="http://www.thinspirationproanatips.com/" class="tumblelog" title="Untitled">thinspiration-pro-ana</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note reblog tumblelog_jcahill without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://jcahill.tumblr.com/" title="tumblrama"><img src="http://25.media.tumblr.com/avatar_45b4779e544b_16.gif" class="avatar " alt=""></a><span class="action" data-post-url="http://jcahill.tumblr.com/post/36535839603"><a rel="nofollow" href="http://jcahill.tumblr.com/" class="tumblelog" title="tumblrama">jcahill</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note reblog tumblelog_makeupartiststoronto without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://makeupartiststoronto.tumblr.com/" title="Toronto Makeup Artist Colette"><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action" data-post-url="http://makeupartiststoronto.tumblr.com/post/36499349491"><a rel="nofollow" href="http://makeupartiststoronto.tumblr.com/" class="tumblelog" title="Toronto Makeup Artist Colette">makeupartiststoronto</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note like tumblelog_madden-14-strategy-guide without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://www.maddenrevealed.com/" title="Madden 14 Strategy Guide "><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://www.maddenrevealed.com/" title="Madden 14 Strategy Guide">madden-14-strategy-guide</a> likes this </span><div class="clear"></div></li><li class="note like tumblelog_marketing-video-production1 without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://www.media-challenge.net/" title="Corporate video production "><img src="http://24.media.tumblr.com/avatar_fae3ab4e9b31_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://www.media-challenge.net/" title="Corporate video production">marketing-video-production1</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_marketing-video-production1 without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://www.media-challenge.net/" title="Corporate video production"><img src="http://24.media.tumblr.com/avatar_fae3ab4e9b31_16.png" class="avatar " alt=""></a><span class="action" data-post-url="http://www.media-challenge.net/post/34649171032"><a rel="nofollow" href="http://www.media-challenge.net/" class="tumblelog" title="Corporate video production">marketing-video-production1</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note reblog tumblelog_progresslighting without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://progresslighting.attacksanxiety.com/" title="Progress Lighting"><img src="http://25.media.tumblr.com/avatar_c7fd9fe183aa_16.png" class="avatar " alt=""></a><span class="action" data-post-url="http://progresslighting.attacksanxiety.com/post/34021882050"><a rel="nofollow" href="http://progresslighting.attacksanxiety.com/" class="tumblelog" title="Progress Lighting">progresslighting</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note like tumblelog_progresslighting without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://progresslighting.attacksanxiety.com/" title="Progress Lighting "><img src="http://25.media.tumblr.com/avatar_c7fd9fe183aa_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://progresslighting.attacksanxiety.com/" title="Progress Lighting">progresslighting</a> likes this </span><div class="clear"></div></li><li class="note like tumblelog_jocuri-online-gratis without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://jocuri-online-gratis.tumblr.com/" title="Jocuri Online "><img src="http://24.media.tumblr.com/avatar_811b76298a77_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://jocuri-online-gratis.tumblr.com/" title="Jocuri Online">jocuri-online-gratis</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_jocuri-online-gratis without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://jocuri-online-gratis.tumblr.com/" title="Jocuri Online"><img src="http://24.media.tumblr.com/avatar_811b76298a77_16.png" class="avatar " alt=""></a><span class="action" data-post-url="http://jocuri-online-gratis.tumblr.com/post/33973805143"><a rel="nofollow" href="http://jocuri-online-gratis.tumblr.com/" class="tumblelog" title="Jocuri Online">jocuri-online-gratis</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note like tumblelog_cnaclassesonlinenursing without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://cnaclassesonlinenursing.tumblr.com/" title="cna classes online "><img src="http://25.media.tumblr.com/avatar_35e91f9c70b1_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://cnaclassesonlinenursing.tumblr.com/" title="cna classes online">cnaclassesonlinenursing</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_kuboji without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://kuboji.tumblr.com/" title="ナナシさん＠水筒廃人"><img src="http://25.media.tumblr.com/avatar_38ff85e8df27_16.png" class="avatar " alt=""></a><span class="action" data-post-url="http://kuboji.tumblr.com/post/33444915875"><a rel="nofollow" href="http://kuboji.tumblr.com/" class="tumblelog" title="ナナシさん＠水筒廃人">kuboji</a> reblogged this from <a rel="nofollow" href="http://gkojaz.tumblr.com/" class="source_tumblelog" title="Zzzzzzzzz......">gkojaz</a></span><div class="clear"></div></li><li class="note like tumblelog_topherchris without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://topherchris.com/" title="topherchris "><img src="http://24.media.tumblr.com/avatar_2b2c23b3f5aa_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://topherchris.com/" title="topherchris">topherchris</a> likes this </span><div class="clear"></div></li><li class="note like tumblelog_kuboji without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://kuboji.tumblr.com/" title="ナナシさん＠水筒廃人 "><img src="http://25.media.tumblr.com/avatar_38ff85e8df27_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://kuboji.tumblr.com/" title="ナナシさん＠水筒廃人">kuboji</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_gkojaz without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://gkojaz.tumblr.com/" title="Zzzzzzzzz......"><img src="http://25.media.tumblr.com/avatar_856649d5064d_16.png" class="avatar " alt=""></a><span class="action" data-post-url="http://gkojaz.tumblr.com/post/33304667901"><a rel="nofollow" href="http://gkojaz.tumblr.com/" class="tumblelog" title="Zzzzzzzzz......">gkojaz</a> reblogged this from <a rel="nofollow" href="http://yaruo.tumblr.com/" class="source_tumblelog" title="(＾ω＾)やる夫のチラ裏やるお(＾ω＾)">yaruo</a></span><div class="clear"></div></li><li class="note like tumblelog_glovek77 without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://glovek77.tumblr.com/" title="Untitled "><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://glovek77.tumblr.com/" title="Untitled">glovek77</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_glovek77 without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://glovek77.tumblr.com/" title="Untitled"><img src="http://assets.tumblr.com/images/default_avatar_16.gif" class="avatar " alt=""></a><span class="action" data-post-url="http://glovek77.tumblr.com/post/32352783994"><a rel="nofollow" href="http://glovek77.tumblr.com/" class="tumblelog" title="Untitled">glovek77</a> reblogged this from <a rel="nofollow" href="http://staff.tumblr.com/" class="source_tumblelog" title="Tumblr Staff">staff</a></span><div class="clear"></div></li><li class="note like tumblelog_vaniaa without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://vaniaa.tumblr.com/" title="Inspiration for dreamers "><img src="http://25.media.tumblr.com/avatar_642f65276947_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://vaniaa.tumblr.com/" title="Inspiration for dreamers">vaniaa</a> likes this </span><div class="clear"></div></li><li class="note like tumblelog_st0pexistingandstartliving without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://st0pexistingandstartliving.tumblr.com/" title="♥♥♥♥♥♥♥♥♥♥ "><img src="http://24.media.tumblr.com/avatar_9585ad13212c_16.png" class="avatar " alt=""></a><span class="action"><a rel="nofollow" href="http://st0pexistingandstartliving.tumblr.com/" title="♥♥♥♥♥♥♥♥♥♥">st0pexistingandstartliving</a> likes this </span><div class="clear"></div></li><li class="note reblog tumblelog_staff without_commentary"><a rel="nofollow" class="avatar_frame" target="_blank" href="http://staff.tumblr.com/" title="Tumblr Staff"><img src="http://25.media.tumblr.com/avatar_013241641371_16.png" class="avatar " alt=""></a><span class="action" data-post-url="http://staff.tumblr.com/post/133573456"><a rel="nofollow" href="http://staff.tumblr.com/" class="tumblelog" title="Tumblr Staff">staff</a> posted this </span><div class="clear"></div></li></ol>';
    if ($_SESSION['permalink_page']) {
      unset($_SESSION['permalink_page']);
      return $notes;
    } else {
      return false;
    }
  }
  
  /**
   * Portrait Getter Functions
   * 
   */
  public function reblogParentPortraitURL16(){ return $this->requestUserPortrait(16, $this->reblogged_info['reblogged_from_name']);}
  public function reblogParentPortraitURL24(){ return $this->requestUserPortrait(24, $this->reblogged_info['reblogged_from_name']);}
  public function reblogParentPortraitURL30(){ return $this->requestUserPortrait(30, $this->reblogged_info['reblogged_from_name']);}
  public function reblogParentPortraitURL40(){ return $this->requestUserPortrait(40, $this->reblogged_info['reblogged_from_name']);}
  public function reblogParentPortraitURL48(){ return $this->requestUserPortrait(48, $this->reblogged_info['reblogged_from_name']);}
  public function reblogParentPortraitURL64(){ return $this->requestUserPortrait(64, $this->reblogged_info['reblogged_from_name']);}
  public function reblogParentPortraitURL96(){ return $this->requestUserPortrait(96, $this->reblogged_info['reblogged_from_name']);}
  public function reblogParentPortraitURL128(){return $this->requestUserPortrait(128, $this->reblogged_info['reblogged_from_name']);}
  
  public function reblogRootPortraitURL16(){ return $this->requestUserPortrait(16, $this->reblogged_info['reblogged_root_name']);}
  public function reblogRootPortraitURL24(){ return $this->requestUserPortrait(24, $this->reblogged_info['reblogged_root_name']);}
  public function reblogRootPortraitURL30(){ return $this->requestUserPortrait(30, $this->reblogged_info['reblogged_root_name']);}
  public function reblogRootPortraitURL40(){ return $this->requestUserPortrait(40, $this->reblogged_info['reblogged_root_name']);}
  public function reblogRootPortraitURL48(){ return $this->requestUserPortrait(48, $this->reblogged_info['reblogged_root_name']);}
  public function reblogRootPortraitURL64(){ return $this->requestUserPortrait(64, $this->reblogged_info['reblogged_root_name']);}
  public function reblogRootPortraitURL96(){return $this->requestUserPortrait(96, $this->reblogged_info['reblogged_root_name']);}
  public function reblogRootPortraitURL128(){return $this->requestUserPortrait(128, $this->reblogged_info['reblogged_root_name']);}
  

  /**
   * @abstract
   * 
   */
  abstract function constructAdditionalFields($post_data);
  abstract function getType();
  
  public function dayOfMonth(){return date('j',$this->timestamp);}  
  public function dayOfMonthWithZero(){return date('d',$this->timestamp);}  
  public function dayOfWeek(){return date('l',$this->timestamp);}
  public function shortDayOfWeek(){return date('D',$this->timestamp);}
  public function dayOfWeekNumber() {return date('N',$this->timestamp);}
  public function dayOfMonthSuffix() {return date('S',$this->timestamp);}
  public function dayOfYear() {return date('z',$this->timestamp);}
  public function weekOfYear(){return date('W',$this->timestamp);}
  public function month(){return date('F',$this->timestamp);}
  public function shortMonth(){return date('M',$this->timestamp);}
  public function monthNumber(){return date('n',$this->timestamp);}
  public function monthNumberWithZero(){return date('m',$this->timestamp);}
  public function year(){return date('Y',$this->timestamp);}
  public function shortYear(){return date('y',$this->timestamp);}
  public function amPm(){return date('a',$this->timestamp);}
  public function capitalAmPm(){return date('A',$this->timestamp);}
  public function twelveHour(){return date('g',$this->timestamp);}
  public function twentyfourHour(){return date('G',$this->timestamp);}
  public function twelveHourWithZero(){return date('h',$this->timestamp);}
  public function twentyfourHourWithZero(){return date('H',$this->timestamp);}
  public function minutes(){return date('i',$this->timestamp);}
  public function seconds(){return date('s',$this->timestamp);}
  public function timeAgo(){return self::day_difference($this->timestamp)." ago";}
  public function timestamp(){return $this->timestamp;}

  public function HasTags() {
    return !empty($this->tags);
  }

  public function hasTag($tag) {
    return in_array($tag, $this->rawData['tags']);
  }

  public function allDatesFormats() {
  $dates = array(
          'DayOfMOnth' => $this->dayOfMonth(),
          'DayOfMonthSuffix' => $this->dayOfMonthSuffix(),
          'ShortMonth' => $this->shortMonth(),
          'Year' => $this->year(),
          'Month' => $this->month(),
          'DayOfMonthWithZero' => $this->dayOfMonthWithZero(),
          'DayOfWeek' => $this->dayOfWeek(),
          'ShortDayOfWeek' => $this->shortDayOfWeek(),
          'DayOfWeekNumber' => $this->dayOfWeekNumber(),
          'DayOfMonthSuffix' => $this->dayOfMonthSuffix(),
          'DayOfYear' => $this->dayOfYear(),
          'WeekOfYear' => $this->weekOfYear(),
          'MonthNumber' => $this->monthNumber(),
          'MonthNumberWithZero' => $this->monthNumberWithZero(),
          'ShortYear' => $this->shortYear(),
          'AmPm' => $this->amPm(),
          'CapitalAmPm' => $this->capitalAmPm(),
          '12Hour' => $this->twelveHour(),
          '24Hour' => $this->twentyfourHour(),
          '12HourWithZero' => $this->twelveHourWithZero(),
          '24HourWithZero' => $this->twentyfourHourWithZero(),
          'Minutes' => $this->minutes(),
          'Seconds' => $this->seconds(),
          'Timestamp' => $this->timestamp(),
          'TimeAgo' => $this->timeAgo(),
      );
    return $dates;
  }

  public function allReblogVars() {
    $vars = array(
      'ReblogParentName' => $this->reblogParentName(),
      'ReblogParentTitle' => $this->reblogParentTitle(),
      'ReblogParentURL' => $this->reblogParentURL(),
      'ReblogParentURL' => $this->reblogParentURL(),
      'ReblogRootName' => $this->reblogRootName(),
      'ReblogRootTitle' => $this->reblogRootTitle(),
      'ReblogRootURL' => $this->reblogRootURL(),
      );
    return $vars;
  }

  public function indexPage () {
    return ($_SESSION['current_operation'] == 'index');
  }

  /**
   * NOT SUPPORTED 
   */
  
  public function shortURL(){
    return $this->shortURL;
  }
  public function toArray(){
    return array();
  }

  public function noteCount(){
    return $this->notes;
  }

  public function noteCountWithLabel() {
    return $this->notes." notes";
  }

  public function contentSource() {
    return $this->source_title;
  }

  public function allCommonVars($index){
    $ever_or_odd_block = ($index % 2) ? 'block:Even' : 'block:Odd';
    $all = array(
      'block:Date'=> new TumblrOptionalParser(true),
      'block:NewDayDate'=> new TumblrOptionalParser($this->newDayDate),
      'block:NoteCount'=> new TumblrOptionalParser($this->noteCount()),
      'block:SameDayDate'=> new TumblrOptionalParser(!$this->newDayDate),
      'PostType' => $this->getType(),
      'Permalink' =>$this->permalink(),
      'ShortURL' => $this->shortURL(),
      'PostID'=>$this->postId(),
      'PostAuthorName'=>$this->rawData['blog_name'],
      'NoteCount'=>$this->noteCount(),
      'NoteCountWithLabel'=>$this->noteCountWithLabel(),
      'TagsAsClasses'=> '',
      'postID' => $this->id,
      'block:Post'.$index => new TumblrOptionalParser(true),
      $even_or_odd_block => new TumblrOptionalParser(true),
      'block:More' => new TumblrOptionalParser(false),
      'block:HasTags' => new TumblrOptionalParser($this->HasTags(), array('block:Tags' => new TumblrTagParser($this))),
      'block:ContentSource' => new TumblrOptionalParser($this->source_title, 
        array('SourceURL' => $this->source_url, 'SourceTitle'=>$this->source_title)
        ),
      'block:NoSourceLogo' => new TumblrOptionalParser(true),
      'block:English'=> new TumblrOptionalParser(true)
      //'block:Tags' => new TumblrTagParser($this)
      );
    $all = array_merge($all,$this->allDatesFormats());
    if ($this->isThisReblog) {
      $all['block:RebloggedFrom'] = new TumblrParser($this->allReblogVars());
    } else {
      $all['block:NotReblog'] = new TumblrOptionalParser(true);
    }
    if ($_SESSION['permalink_page']) {
      $all['PostNotes'] = $this->postNotes();
      unset($_SESSION['permalink_page']);
    }
    return $all;
  }

  public function getVar($name) {
     $method = lcfirst(str_replace("-", "", $name));
      try {
        if (method_exists($this, $method)) {
          return $this->$method();
        } else {
          return false;
        }
      } catch (TagNotSupportedException $e) {
        return false;
      }
  }
  //TODO: Generate a generic RenderBlock for post_types with no particularity. Move up.
}