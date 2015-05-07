<?php
/**
 * Description of TagNotSupportedException
 *
 * @author jlasarte
 */
class TagNotSupportedException extends Exception {
    
    public function __construct($tag) {
        parent::__construct($this::errorMessage($tag));
    }
    
    public static function errorMessage($tag) {
        $error_message = 'The tag '.$tag.' is not supported in this version of thmr.';
        return $error_message;
    }
}

?>
