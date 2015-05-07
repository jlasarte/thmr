<?php
/// @author jlasarte
class TumblrPostParser extends TumblrParser {
    protected $post;

    public function __construct($post, $vars) {
        $this->post = $post;
        parent::__construct($vars);
    }

    public function _getVar($name, $toString = false) {
        return $this->post->getVar($name);
    }

    public function _getBlock($name) {
        if (method_exists($this->post, $name) && $this->post->$name()) {
            if ($name == 'Tags') {
                return new TumblrTagParser($this->post);
            } else {
                return new TumblrPostParser($this->post);
            }
        } else {
            if($_SESSION['configurations']['block:'.$name]) {
                return new TumblrPostParser($this->post);
            } else {
                return new PhemeSkinParser();
            }
        }
    }
}

class TumblrChatParser extends TumblrPostParser {

    public function _getBlock($name) {
        if (method_exists($this->post, $name) && $this->post->$name()) {
            if ($name == 'Lines') {
                return new TumblrLinesParser($this->post);
            } else {
                return new TumblrPostParser($this->post);
            }
        } else {
            return new PhemeSkinParser();
        }
    }
}

class TumblrPhotoSetParser extends TumblrPostParser {
    public function _getBlock($name) {
        if (method_exists($this->post, $name) && $this->post->$name()) {
            if ($name == 'Photos') {
                return new TumblrPhotoSetLoopParser($this->post);
            } else {
                return new TumblrPostParser($this->post);
            }
        } else {
            if($_SESSION['configurations']['block:'.$name]) {
                return new TumblrPhotoSetParser($this->post);
            } else {
                return new PhemeSkinParser();
            }
        }
    }
}
?>