<?php

/**
 * Pheme adapter for Tumblr syntax
 *
 * @author Sandu Lungu <sandu@lungu.info>
 * @copyright 2010 Sandu Lungu
 * @filesource
 * @license http://www.gnu.org/licenses/gpl.html GPL3
 * @link https://www.ohloh.net/p/pheme
 * @package Pheme
 * @subpackage Tumblr
 */

require_once (dirname(__FILE__).'/parser.php');

/**
 * Generic parser class. Uses a syntax compatible with Tumblr's custom templating engine's.
 *
 * The constructor takes any number of parameters, that are assigned to the $_params property
 */
class TumblrParser extends PhemeParser {
    protected $_preg = '/{block:([A-Za-z][A-Za-z0-9]*)}(.*?){\/block:\1}|{(out:)?([A-Za-z0-9][A-Za-z0-9][\w\'-]*)}|{((Color|Font|Lang|Image|Text):([A-Za-z][A-Za-z0-9\_]*))}/isS';
    /**
     * Class constructor (adapted to tumbl like syntax)
     *
     * @param array $vars Associative array of blocks, templated blocks and variables
     *      (note that all variable declarations start with '$')
     * @param array $options Custom options
     */
    public function __construct($rules = array(), $options = array()) {
        $this->options = array_merge($this->options, $options);
        
        foreach ($rules as $name => $value) {
            if (strpos($name, 'block:') !== 0) {
            // variable
                $this->vars[$name] = $value;
            }
            else {
                $name = substr($name, 6);
                if (is_string($value)) {
                // block template
                    $this->templates[$name] = $value;
                    $this->blocks[$name] = new SilentParser();
                }
                else {
                // block
                    $this->blocks[$name] = $value;
                }
            }
        }
    }

    protected function _getLang($lang) {
        $phrase = $_SESSION['localization'][$lang];
        return $this->parse($phrase);
    }

    protected function _pregCallback($matches) {
        if (!empty($matches[4])) {
        // variable
            return !empty($matches[1]) ?
            $this->_getTemplate(substr($matches[2], 1)) :
            $this->_getVar($matches[4]);
        } elseif (!empty($matches[2])){
        // block
            return $this->_getBlock($matches[1])->parse($matches[2], $matches[1]);
        } else {
           if ($matches[6] == 'lang') {
                return $this->_getLang($matches[7]);
                //return $matches[7];
           } else {
                return $this->_getVar($matches[5]);
           }
        }
    }
}

/**
 * Useful for subtemplates, like blocks or interface elements.
 *
 * The constructor takes any number of parameters, that are assigned to the $_params property
 */
class TumblrSubParser extends TumblrParser {
    protected $_params = null;

    public function __construct() {
        $this->_params = func_get_args();
        parent::__construct(array_shift($this->_params), array_shift($this->_params));
    }
}

class TumblrConditionalParser extends TumblrParser {
    protected $_params = null;

    public function __construct($blog, $blockName, $rules, $options) {
        $this->blog = $blog;
        $this->blockName = $blockName;
        parent::__construct($rules,$options);
    }

    public function parse($html = null, $blockName = 'document', $blockParams = null) {
        $blog = $this->blog;
        $op = $this->blockName;
        if ($blog->$op()) {
            return parent::parse($html, $blockName, $blockParams);
        } else {
            return ' ';
        }
    }
}

class TumblrOptionalParser extends TumblrParser {
     protected $_params = null;

    public function __construct($parse, $rules, $options) {
        $this->parse = $parse;
        parent::__construct($rules,$options);
    }

    public function parse($html = null, $blockName = 'document', $blockParams = null) {
        $parse = $this->parse;
        if ($parse) {
            return parent::parse($html, $blockName, $blockParams);
        } else {
            return ' ';
        }
    }
}

class SilentParser extends TumblrParser {
     public function parse($html = null, $blockName = 'document', $blockParams = null) {
            return ' ';
    }
}

class TumblrPostLoopParser extends TumblrSubParser {
    public $options = array('separator' => "\n");

    public function parse($html, $blockName = 'document', $blockParams = null) {
        $items = array();
        // the cycle itself. We use $this->_params[0], stored by the class constructor
        $ind = 1;
        foreach ($this->lines as $vars) {
            $this->currentLine = $vars;
            $items[] = $this->_parseItem($html, $vars, $blockName, $blockParams);
            $ind++;
        }
        return implode($this->options['separator'], $items);
    }

    protected function _parseItem($html, $vars, $blockName = 'document', $blockParams = null) {
        return parent::parse($html, $blockName, $blockParams);
    }

    protected function _getVar($name, $toString = false) {
        return $this->currentLine->$name();
    }

}

class TumblrLinesParser extends TumblrSubParser {
    public $options = array('separator' => "\n");
    
    public function __construct($post) {
        $this->_params = func_get_args();
        $this->post = $post; 
        $this->lines = $this->post->dialogue(); 
        parent::__construct($this->_params);
    }

    public function parse($html, $blockName = 'document', $blockParams = null) {
        $items = array();
        // the cycle itself. We use $this->_params[0], stored by the class constructor
        $ind = 1;
        foreach ($this->lines as $vars) {
            $this->currentLine = $vars;
            $items[] = $this->_parseItem($html, $vars, $blockName, $blockParams);
            $ind++;
        }
        return implode($this->options['separator'], $items);
    }

    protected function _parseItem($html, $vars, $blockName = 'document', $blockParams = null) {
        $this->blocks['Label'] =  new TumblrSubParser(
            array(
                'Label' => $vars->Label(),
                'Name'=> $vars->Name(),
                )
            );
        return parent::parse($html, $blockName, $blockParams);
    }

    protected function _getVar($name, $toString = false) {
        return $this->currentLine->$name();
    }
}

class TumblrTagParser extends TumblrPostLoopParser {

    public function __construct($post) {
        $this->_params = func_get_args();
        $this->post = $post; 
        $this->lines = $this->post->tags(); 
        parent::__construct($this->_params);
    }

}

class TumblrPhotoSetLoopParser extends TumblrPostLoopParser {
    public function __construct($post) {
        $this->_params = func_get_args();
        $this->post = $post; 
        $this->lines = $this->post->photos(); 
        parent::__construct($this->_params);
    }

    protected function _getVar($name, $toString = false) {
        return $this->currentLine->getVar($name);
    }

    public function _getBlock($name) {
        if (method_exists($this->currentLine, $name) && $this->currentLine->$name()) {
            return new TumblrPostParser($this->currentLine);
        } else {
            if($_SESSION['configurations']['block:'.$name]) {
                return new TumblrPostParser($this->currentLine);
            } else {
                return new PhemeSkinParser();
            }
        }
    }
}

/**
 * Copies block text to parent parser's vars array and silently returns
 */
class TumblrLoopParser extends TumblrSubParser {
    public $options = array('separator' => "\n");

    public function __construct() {
        $this->_params = func_get_args();
        $this->copy = $this->_params; 
        parent::__construct($this->_params);
    }

    public function parse($html, $blockName = 'document', $blockParams = null) {
        $items = array();
        // the cycle itself. We use $this->_params[0], stored by the class constructor
        foreach ($this->copy[0] as $vars) {
            $this->common = $vars->allCommonVars();
            $items[] = $this->_parseItem($html, $vars, $blockName, $blockParams);
        }
        return implode($this->options['separator'], $items);
    }

    /**
     * Subtemplate parsing with custom vars set
     *
     * @param string $html
     * @param array $vars Associativea array of vars (strings)
     * @param string $blockName
     * @param array $blockParams
     * @return string
     */
    protected function _parseItem($html, $vars, $blockName = 'document', $blockParams = null) {
        $this->vars = $vars;
        $torender  = array('Text', 'Quote', 'Answer', 'Chat', 'Link', 'Video', 'Photo', 'PhotoSet', 'Audio');
        if(in_array($vars->getType(), $torender)) {
            return $vars->parse($this, $html, $blockName, $blockParams);
        } else {
            $p = New TumblrParser($this->common);
            $html = $p->parse($html);
            unset($p);
            return $html;
        }
    }

    public function parseTextPost(TextPost $textPost, $html, $blockName = 'document', $blockParams = null) {
        $vars = $this->common;
        $vars['block:Text'] = new TumblrPostParser($textPost, $vars); 
        $p = New TumblrParser($vars);
        $html = $p->parse($html);
        unset($p);
        return $html;
    }

    public function parseQuotePost(QuotePost $post, $html, $blockName = 'document', $blockParams = null) {
        $vars = $this->common;
        $vars['block:Quote'] = new TumblrPostParser($post); 
        $p = New TumblrParser($vars);
        $html = $p->parse($html);
        return $html;
    }

    public function parseAnswerPost(AnswerPost $post, $html, $blockName = 'document', $blockParams = null) {
        $vars = $this->common;
        // if a photoset block is not present, tumblr uses the text block to parse it
        if (stripos($html, 'block:Answer') == false) {
            // we need to replace the content of the text tag
            $vars['block:Text'] = new TumblrPhotoSetParser($post); 
            // we need to make sure this is the block to render the content, and not another
            // conditional block like one used to add a class to the post div, etc
            $html = preg_replace_callback("/{block:Text}(.*?){\/block:Text}/is", array($post, "replaceForDefaultBlock"), $html);
        } else {
            $vars['block:Answer'] = new TumblrPostParser($post); 
        }
        $p = New TumblrParser($vars);
        $html = $p->parse($html);
        unset($p);
        return $html;
    }

    public function parseChatPost(ChatPost $post, $html, $blockName = 'document', $blockParams = null) {
        $vars = $this->common;
        $vars['block:Chat'] = new TumblrChatParser($post); 
        $p = New TumblrParser($vars);
        $html = $p->parse($html);
        unset($p);
        return $html;
    }

    public function parseLinkPost(LinkPost $post, $html, $blockName = 'document', $blockParams = null) {
        $vars = $this->common;
        $vars['block:Link'] = new TumblrPostParser($post); 
        $p = New TumblrParser($vars);
        $html = $p->parse($html);
        unset($p);
        return $html;

    }

    public function parseVideoPost(VideoPost $post, $html, $blockName = 'document', $blockParams = null) {
        $vars = $this->common;
        $vars['block:Video'] = new TumblrPostParser($post); 
        $p = New TumblrParser($vars);
        $html = $p->parse($html);
        unset($p);
        return $html;
    }

    public function parsePhotoPost(PhotoPost $post, $html, $blockName = 'document', $blockParams = null) {
        $vars = $this->common;
        $vars['block:Photo'] = new TumblrPostParser($post); 
        $p = New TumblrParser($vars);
        $html = $p->parse($html);
        unset($p);
        return $html;
    }

     public function parsePhotoSetPost(PhotoPost $post, $html, $blockName = 'document', $blockParams = null) {
        $vars = $this->common;
        // if a photoset block is not present, tumblr uses the video blog to pase it
        if (stripos($html, 'block:Photoset') == false) {
            // we need to replace the video tag with the corresponding photoset tag
            $vars['block:Video'] = new TumblrPhotoSetParser($post); 
            $html = preg_replace("{(Video)-([0-9]*)}", "Photoset-$2", $html);
        } else {
            $vars['block:Photoset'] = new TumblrPhotoSetParser($post); 
        }
        $p = New TumblrParser($vars);
        $html = $p->parse($html);
        unset($p);
        return $html;
    }

    public function parseAudioPost(AudioPost $post, $html, $blockName = 'document', $blockParams = null) {
        $vars = $this->common;
        $vars['block:Audio'] = new TumblrPostParser($post, $vars); 
        $p = New TumblrParser($vars);
        $html = $p->parse($html);
        unset($p);
        return $html;
    }
}

/**
 * Copies block text to parent parser's vars array and silently returns
 */
class TumblrTemplateParser extends TumblrParser {
    public function parse($html, $blockName = 'document') {
        self::$_stack[count(self::$_stack) - 1]->templates[$blockName] = $html;
    }
}
