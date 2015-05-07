<?php
/**
 * Description of AnswerPost
 *
 * @author jlasarte
 */
class AnswerPost Extends TumblrPost {
    /**
     *
     * @var string the blog name of the user asking the question
     */
    private $asking_name;
    /**
     *
     * @var string the blog URL of the user asking the question 
     */
    private $asking_url;
    /**
     *
     * @var string the question being asked
     */
    private $question;
    /**
     *
     * @var string the answer given
     */
    private $answer;
    
    private function requestAskerPortrait($size) {
        //$request_info_url = API_URL_BLOG.$this->asking_name.".tumblr.com/avatar/".$size;        
        //$headers = get_headers($request_info_url,1);
        //return $headers['Location'];
        return 'http://assets.tumblr.com/images/default_avatar_'.$size.'.gif';
    }
    
    public function constructAdditionalFields($post_data) {
        
        $this->asking_name = $post_data['asking_name'];
        $this->asking_url = $post_data['asking_url'];
        $this->question = $post_data['question'];
        $this->answer = $post_data['answer'];
    }

    //todo: this shouldnt be here...
    public static function replaceForDefaultBlock($html) {
      if (stripos($html[1], "{Body}") !== false) {
        return "{block:Text}<p><b>{Asker}</b> {Question}</p><p>{Answer}</p>{/block:Text}";
      } else {
        return $html[1];
      }
    }

    public function getType() {
        return "Answer";
    }
    
    public function question() {
        return $this->question;
    }   
    
    public function answer() {
        return $this->answer;
    }
    
    public function asker(){
        return $this->asking_name;
    }
    
    public function askerPortraitURL16() { return $this->requestAskerPortrait(16);}
    public function askerPortraitURL24() { return $this->requestAskerPortrait(24);}
    public function askerPortraitURL30() { return $this->requestAskerPortrait(30);}
    public function askerPortraitURL40() { return $this->requestAskerPortrait(40);}
    public function askerPortraitURL48() { return $this->requestAskerPortrait(48);}
    public function askerPortraitURL64() { return $this->requestAskerPortrait(64);}
    public function askerPortraitURL96() { return $this->requestAskerPortrait(96);}
    public function askerPortraitURL128() {return $this->requestAskerPortrait(128);}

    public function renderBlock($block, ThmrParser $parser) {
      $variables = $parser->GetVariablesFor($block);
      $variables = $this->getVariableValues($variables);
      $block = $parser->replaceVariablesInBlock($block, $variables);
      return $block;
    }

    public function parse(TumblrLoopParser $parser, $html, $blockName = 'document', $blockParams = null) {
      return $parser->parseAnswerPost($this, $html, $blockName, $blockParams);
    }
}

?>
