<?php

Class Line {

  private $line;
  private $label;
  private $name;
  private $alt;
  private $user_number;

  public function __construct($i, $data, $user_number){
    $this->alt = ($i % 2) ? 'odd' : 'even';
    $this->label = $data['label'];
    $this->line = $data['phrase'];
    $this->name = $data['name'];
    $this->user_number = $user_number;
  }

  public function Line(){
    return $this->line;
  }

  public function Name(){
    return $this->name;
  }

  public function Alt(){
    return $this->alt;
  }

  public function Label(){
    return $this->label;
  }

  public function UserNumber() {
    return $this->user_number;
  }

  public function getLabelBlock(){
    return new TumblrSubParser(
      array(
        'block:Label' =>  array(
                'Label' => $this->Label(),
                'Name'=> $this->Name()
                )
            )
        );
  }
}

?>