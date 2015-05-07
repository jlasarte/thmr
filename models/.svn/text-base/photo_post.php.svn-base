<?php
/**
 * A photo post.
 *
 * @author jlasarte
 */
class PhotoPost Extends TumblrPost {

    protected $photo;
    protected $caption;
    

    private static function changeArrayKeys($photo_sizes_array) {
        $new_array = array();
        foreach ($photo_sizes_array as $key => $value) {
            $new_array[$value['width']] = $value;
        }
        return $new_array;
    }

    private function getClosestSize($search)
    {
       $closest = null;
       $keys = array_keys($this->photo['alt_sizes']);
       foreach( $keys as $item)
       {
          if($closest == null || abs($search - $closest) > abs($item - $search))
          {
             $closest = $item;
          }
       }
       return $closest;
    }
    
    public function constructAdditionalFields($post_data) {
        $post_data['photos'][0]['alt_sizes'] = PhotoPost::changeArrayKeys($post_data['photos'][0]['alt_sizes']);
        $this->photo  = isset($post_data['photos']) ? $post_data['photos'][0] : array();
        $this->caption = isset($post_data['caption']) ? $post_data['caption'] : '';
    }

    public function getType() {
        return "Photo";
    }
    
    public function photoAlt() {
        return ($this->caption) ? html2text($this->caption) : '';
    }
    
    public function caption() {
        return $this->caption;
    }
    
    public function linkURL(){
        return $this->photo['original_size']['url'];
    }
    
    public function linkOpenTag() {
        return '<a href="'.$this->linkURL().'">';
    }
    
    public function linkCloseTag() {
        return '</a>';
    }

    private function getPhotoUrl($size){
        if ($url = $this->photo['alt_sizes'][$size]['url']) {
            return $url;
        } else {
            return $this->photo['alt_sizes'][$this->getClosestSize($size)]['url'];
        }
    }
    
    public function photoURL500(){
        return $this->getPhotoUrl(500);
    }
    
    public function photoURL400(){
        return $this->getPhotoUrl(400);
    }
    
    public function photoURL250(){
        return $this->getPhotoUrl(250);
    }
    
    public function photoURL100(){
        return $this->getPhotoUrl(100);
    }
    
    public function photoURL75sq(){
        return $this->getPhotoUrl(75);
    }

    public function PhotoURLHighRes() {
        $max = max(array_keys($this->photo['alt_sizes']));
        return $this->photo['alt_sizes'][$max]['url'];
    }

    public function highRes() {
        $max = max(array_keys($this->photo['alt_sizes']));
        return $max > 500;
    }

    public function toArray(){
        $arr = array();
        $arr['PhotoAlt'] = $this->photoAlt();
        $arr['LinkOpenTag'] = $this->linkOpenTag();
        $arr['LinkCloseTag'] = $this->linkCloseTag();
        return $arr;
    }

    /**
     * NOT SUPPORTED TAGS 
     */
    
    public function camera() {     throw new TagNotSupportedException('camera');}
    public function aperture(){    throw new TagNotSupportedException('aperture');}
    public function exposure(){    throw new TagNotSupportedException('exposure');}
    public function focalLength(){ throw new TagNotSupportedException('focalLength');}

     public function parse(TumblrLoopParser $parser, $html, $blockName = 'document', $blockParams = null) {
      return $parser->parsePhotoPost($this, $html, $blockName, $blockParams);
    }
  
}

?>
