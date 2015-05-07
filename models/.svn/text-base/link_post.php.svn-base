<?php
/**
 * Description of LinkPosy
 *
 * @author jlasarte
 */
class LinkPost Extends TumblrPost {
    /**
     *
     * @var string the title of the page the link points to 
     */
    private $title;
    /**
     *
     * @var string the link
     */
    private $url;
    /**
     *
     * @var string A user-supplied description
     */
    private $description;

    public function constructAdditionalFields($post_data) {
        $this->title = $post_data['title'];
        $this->url = $post_data['url'];
        $this->description = $post_data['description'];
    }

    public function getType() {
        return "Link";
    }
    
    public function uRL() {
        return $this->url;
    }
    
    /**
     * Purposely left blank as this renders accordingly with the user set configurations in tumblr.
     * @return string empty
     */
    public function target(){
        return "";
    }
    
    public function description() {
        return $this->description;
    }
    
    /**
     * 
     * @return string the url 
     */
    public function name() {
        return $this->title;
    }
    
    public function renderBlock($block, ThmrParser $parser) {
      
      if ($this->description) {
          $block = $parser->render_variable('Description', $this->description(), $block);
          $block = $parser->render_block('Description', $block);
      } else {
          $block = $parser->strip_block('Description', $block);
      }
      $variables_in_block = $parser->GetVariablesFor($block);
      $variables_values = $this->getVariableValues($variables_in_block);
      $block = $parser->replaceVariablesInBlock($block, $variables_values);
      
      return $block;
    }
    

     public function parse(TumblrLoopParser $parser, $html, $blockName = 'document', $blockParams = null) {
      return $parser->parseLinkPost($this, $html, $blockName, $blockParams);
    }
}

?>
