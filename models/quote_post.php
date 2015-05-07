<?php
/**
 * Description of QuotePost
 *
 * @author jlasarte
 */
class QuotePost Extends TumblrPost {
    
    /**
     *
     * @var string the text of the quote. 
     */
    private $text;   
    /**
     *
     * @var string full HTML for the source of the quote. 
     */
    private $source;
    

    public function constructAdditionalFields($post_data) {
        $this->source = $post_data['source'];
        $this->text = $post_data['text'];     
    }

    public function getType() {
        return "Quote";
    }
    
    public function quote(){
        return $this->text;
    }
    
    public function source(){
        return $this->source;
    }
    
    public function length(){
        $len = strlen($this->text);
        if($len < 100) {
            return 'short';
        } elseif ($len < 250) {
            return 'medium';
        } else {
            return 'long';
        }
    }
    
    public function renderBlock($block, ThmrParser $parser) {
      
        if ($this->source) {
          $block = $parser->render_variable('Source', $this->source(), $block);
          $block = $parser->render_block('Source', $block);
        } else {
            $block = $parser->strip_block('Source', $block);
        }
        $variables_in_block = $parser->GetVariablesFor($block);
        $variables_values = $this->getVariableValues($variables_in_block);
        $block = $parser->replaceVariablesInBlock($block, $variables_values);

        return $block;
   }

   public function parse(TumblrLoopParser $parser, $html, $blockName = 'document', $blockParams = null) {
      return $parser->parseQuotePost($this, $html, $blockName, $blockParams);
    }


}

?>
