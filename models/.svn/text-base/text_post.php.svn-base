<?php
/**
 * Description of TextPost
 *
 * @author usuario
 */
class TextPost Extends TumblrPost {
    
    /**
     *
     * @var string The [optional] title of the post. 
     */
    private  $title;
    
    /**
     *
     * @var string type the full body post
     */
    private  $body;

    public function constructAdditionalFields($post_data) {
        $this->title = isset($post_data['title']) ? $post_data['title'] : '';
        $this->body = $post_data['body'];
        $this->more_pos = stripos($this->body, "<!-- more -->");
    }

    public function getType() {
        return "Text";
    }
    
    public function title() {
        return $this->title;
    }
    
    public function body() {
      if ($this->more() && ($_SESSION['current_operation'] == 'index')) {
        $newStr = substr($this->body,0,$this->more_pos );
        $newStr .=  '<p class="read_more_container"><a href="'.$this->permalink().'" class="read_more">Read More</a></p>';
        return $newStr;
      } else {
        return $this->body;
      }
    }

    public function more() {
      return ($this->more_pos !== false);
    }

    public function parse(TumblrLoopParser $parser, $html, $blockName = 'document', $blockParams = null) {
      return $parser->parseTextPost($this, $html, $blockName, $blockParams);
    }

}

?>
