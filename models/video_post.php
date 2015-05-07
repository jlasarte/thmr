<?php
/**
 * Description of VideoPost
 *
 * @author jlasarte
 */
class VideoPost Extends TumblrPost {
    /**
     *
     * @var string the user-supplied caption 
     */
    private $caption;
    /**
     *
     * @var array array of objects. Object fields within the array:
     * <ul>
     *  <li><code>width</code> – number: width of video player, in pixels</li>
     *  <li><code>embed_code</code> – string: HTML for embedding the video player</li>
     * </ul> 
     */
    private $player;
    
    private static function changeArrayKeys($players_array) {
        $new_array = array();
        foreach ($players_array as $key => $value) {
            $new_array[$value['width']] = $value;
        }
        return $new_array;
    }
    
    public function constructAdditionalFields($post_data) {
        $this->caption = $post_data['caption'];
        $this->player = $this::changeArrayKeys($post_data['player']);
    }

    public function getType() {
        return "Video";
    }
    
    public function caption(){
        return $this->caption;
    }
    
    public function video500() {
        return $this->player['500']['embed_code'];
    }

    public function video700() {
        return $this->player['700']['embed_code'];
    }
    
    public function video400() {
        return $this->player['400']['embed_code'];
    }
    
    public function video250() {
        return $this->player['250']['embed_code'];
    }
    
    public function playCount() {throw new TagNotSupportedException("{PlayCount}");}
    public function formattedPlayCount(){throw new TagNotSupportedException("{FormattedPlayCount}");}
    public function playCountWithLabel() {throw new TagNotSupportedException("{PlayCountWithLabel}");}

    public function renderBlock($block, ThmrParser $parser) {
      if ($this->caption) {
          $block = $parser->render_variable('Caption', $this->caption(), $block);
          $block = $parser->render_block('Caption', $block);
      } else {
          $block = $parser->strip_block('Caption', $block);
      }
      $variables_in_block = $parser->GetVariablesFor($block);
      $variables_values = $this->getVariableValues($variables_in_block);
      $block = $parser->replaceVariablesInBlock($block, $variables_values);
      
      return $block;
    }
    
    public function parse(TumblrLoopParser $parser, $html, $blockName = 'document', $blockParams = null) {
      return $parser->parseVideoPost($this, $html, $blockName, $blockParams);
    }        
}

?>
