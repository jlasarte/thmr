<?php

Class Tag {
  private $tag;

  public function __construct($tag) {
    $this->tag = $tag;
  }

  public function Tag(){
    return $this->tag;
  }

  public function URLSafeTag(){
    return urlencode($this->tag);
  }

  public function TagURL(){
    return "./#/".$_SESSION['current_theme']."/tagged/".$this->tag;
  }

  public function TagURLChrono(){
    return '';
  }
}

?>