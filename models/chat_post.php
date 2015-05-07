<?php
/**
 * Description of ChatPost
 *
 * @author jlasarte
 */
class ChatPost Extends TumblrPost {
    /**
     *
     * @var string the [optional] title of the post
     */
    private $title;
    /**
     *
     * @var string the full chat body
     */
    private $body;
    /**
     *
     * @var array array of objects with the following properties.
     * <ul>
     *  <li><code>name</code> – string: name of the speaker</li>
     *  <li><code>label</code> – string: label of the speaker</li>
     *  <li><code>phrase</code> – string: text</li>
     *</ul>
     * 
     */
    private $dialogue = array();
    
    private function get_dialogue_names($dialogue){
      $column = array();
      $i = 1;
      foreach($dialogue as $line) {
          if(isset($line["name"]) && (!isset($column[$line["name"]]))) {
              $column[$line["name"]] = $i;
          }
          $i ++;
      }
      return $column;
    }

    public function getVariableValuesInLine($variables, $line) {
      $output = array();
      foreach( $variables as $variable) {
          $method = lcfirst(str_replace("-", "", $variable));
          try {
              $output[$variable] = $this->$method($line);
          } catch (TagNotSupportedException $e) {
              $output[$variable] = false;
          }
          
      }
      return $output;
  }
    
    private function renderLinesBlock($parser, $block){
        $parsed_lines = ''; // we need an empty string to append to.
        // we need to render the block once for each line of dialogue.
        foreach ($this->dialogue as $k=>$line) {
            // render not required fields.
            if (isset($line['label'])) {
                $parsed_lines.= $parser->render_variable('Label', $this->label($k), $block);
                $parsed_lines = $parser->render_block('Label', $parsed_lines);
            } else {
                $parsed_lines .= $parser->strip_block('Label', $block);
            }
            // render everything else.
            $variables_in_block = $parser->GetVariablesFor($parsed_lines);
            // notice that we user getVariablesValuesInLine instead of getVariableValuesinBlock,
            //this is because we need to pass the $line number as a parameter for the fucntions
            // specific to each line.
            $variables_values = $this->getVariableValuesInLine($variables_in_block, $k);
            $parsed_lines = $parser->replaceVariablesInBlock($parsed_lines, $variables_values);
        }
        return $parsed_lines;
    }
    
    public function constructAdditionalFields($post_data) {
        $this->title = isset($post_data['title']) ? $post_data['title'] : '';
        $this->body = $post_data['body'];
        $names = $this->get_dialogue_names($post_data['dialogue']);
        foreach ($post_data['dialogue'] as $key => $value) {
          $this->dialogue[] = new Line($key, $value, $names[$value['name']]);
        }
    }

    public function getType() {                
        return "Chat";
    }
    
    public function title(){
        return $this->title;
    }
    
    public function label($line = 0){
        return $this->dialogue[$line]['label'];
    }
    
    public function name($line = 0) {
        return $this->dialogue[$line]['name'];
    }
    
    public function userNumber() {
        throw new TagNotSupportedException("{UserNumber}");
    }
    
    public function alt($line = 0) {
        return ($line % 2) ? 'odd' : 'even';
    }

    public function dialogue(){
      return $this->dialogue;
    }
    
    //TODO: return the current line.
    public function lines( ) {
        return !empty($this->dialogue);
    }
    
    public function renderBlock($block, ThmrParser $parser) {
        // render not required fields;
        if ($this->title) {
          $block = $parser->render_variable('Title', $this->title(), $block);
          $block = $parser->render_block('Title', $block);
        } else {
          $block = $parser->strip_block('Title', $block);
        }
        // we only want to render if a lines block exits
        if ($lines_block = $parser->getBlockWithLabel('Lines')) {
            //render the lines block
            $lines_block = $this->renderLinesBlock($parser, $lines_block);
            //replace the block with the rendered result
            $block = preg_replace($parser->block_pattern('Lines'), $lines_block, $block);
        }        
        return $block;
     }
    

    public function parse(TumblrLoopParser $parser, $html, $blockName = 'document', $blockParams = null) {
      return $parser->parseChatPost($this, $html, $blockName, $blockParams);
    }
    
    
}

?>
