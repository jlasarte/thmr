<?php

class PhotoSetPhoto extends PhotoPost {
  
  private static function changeArrayKeys($photo_sizes_array) {
        $new_array = array();
        foreach ($photo_sizes_array as $key => $value) {
            $new_array[$value['width']] = $value;
        }
        return $new_array;
  }

  public function __construct($post, $index) {
    $this->id = $post['id'];
    $this->post_url = $post['post_url'];
    $this->timestamp = $post['timestamp'];
    $this->date = $post['date'];
    $this->format = $post['format'];
    $this->reblog_key = $post['reblog_key'];
    foreach ($post['tags'] as $tag) {
      $this->tags[] = new Tag($tag);
    }
    $post['photos'][$index]['alt_sizes'] = self::changeArrayKeys($post['photos'][$index]['alt_sizes']);
    $this->photo  = isset($post['photos']) ? $post['photos'][$index] : array();
    $this->caption = isset($this->photo['caption']) ? $this->photo['caption'] : '';
  }

  public function constructAdditionalFields($post_data){

  }

  public function getType() {

  }

  public function renderBlock($block, ThmrParser $parser){

  }

  public function getVar($name) {
     $method = lcfirst(str_replace("-", "", $name));
      try {
        return $this->$method();
      } catch (TagNotSupportedException $e) {
        return false;
      }
  }
}