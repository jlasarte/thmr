<?php
/**
 * Description of AudioPost
 *
 * @author jlasarte
 */
class AudioPost Extends TumblrPost {
    /**
     *
     * @var String The user-supplied caption
     */
    private $caption;
    /**
     *
     * @var String HTML for embedding the audio player
     */
    private $player;
    /**
     *
     * @var int Number of times the audio post has been played
     */
    private $plays;
    /**
     *
     * @var string Location of the audio file's ID3 album art image
     */
    private $album_art;
    /**
     *
     * @var string The audio file's ID3 artist value
     */
    private $artist;
    /**
     *
     * @var string  The audio file's ID3 album value
     */
    private $album;
    /**
     *
     * @var string The audio file's ID3 title value
     */
    private $track_name;
    /**
     *
     * @var int The audio file's ID3 track value
     */
    private $track_number;
    /**
     *
     * @var int The audio file's ID3 year value
     */
    private $year;
    /**
     *
     * @var string the url of the audio file. 
     */
    private $audio_url;
    
    protected $source_title;
    
    private function createAudioPlayer($color = '', $logo = null) {
        $audio_file= $this->audio_url;
        $id = $this->id;
        if ($color && ($color != 'white')) {
            if ($color == 'grey') {
                $color = '';
                $audio_file .= "&color=E4E4E4";
            } else {
                $color = '_'.$color;
            }
        } else {
            $color = '';
            $audio_file .= "&color=FFFFFF";
        }
        return <<<PLAYER
<script type="text/javascript" language="javascript" src="http://assets.tumblr.com/javascript/tumblelog.js?16"></script><span id="audio_player_$id">[<a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" target="_blank">Flash 9</a> is required to listen to audio.]</span><script type="text/javascript">replaceIfFlash(9,"audio_player_$id",'<div class="audio_player"><embed type="application/x-shockwave-flash" src="http://demo.tumblr.com/swf/audio_player$color.swf?audio_file=$audio_file&logo=$logo" height="27" width="207" quality="best"></embed></div>')</script>
PLAYER;
	
    }

    public function constructAdditionalFields($post_data) {
        $this->caption      = $post_data['caption'];
        $this->player       = $post_data['player'];
        $this->plays        = $post_data['plays'];
        
        $this->album_art    = isset($post_data['album_art']) ? $post_data['album_art'] :'';
        $this->artist       = isset($post_data['artist']) ? $post_data['artist'] :'';
        $this->album        = isset($post_data['album']) ? $post_data['album'] :'';
        $this->track_name   = isset($post_data['track_name']) ? $post_data['track_name'] :'';
        $this->track_number = isset($post_data['track_number']) ? $post_data['track_number'] :'';
        $this->year         = isset($post_data['year']) ? $post_data['year'] :'';
        $this->audio_url    = isset($post_data['audio_url']) ? $post_data['audio_url'] :'';
        $this->source_title = isset($post_data['source_title']) ? $post_data['source_title'] :'';
        
        $html = str_get_html($this->player);
        if ($iframe = $html->find('iframe')) {
            $this->spotify_link = $iframe[0]->src;
            $html->clear(); 
        }
        
        // set the [optional] fields for this postType.
        $this->conditional_fields = array(
            'Caption' => 'caption', 
            'AudioEmbed' => 'player', 
            'AudioPlayer' => 'player',
            'PlayCount' => 'plays',
            'ExternalAudio' => 'audio_url',
            'AlbumArt' => 'album_art',
            'Artist' => 'artist',
            'Album' => 'album',
            'TrackName' => 'track_name',
            );
    }

    public function getType() {
        return "Audio";
    }
    
    public function caption() {
        return $this->caption;
    }
    
    public function audioEmbed(){
        if (!$this->audioPlayer()){
            return $this->player;
        } else {
            return false;
        }
    }
    
    public function audioPlayer(){
        return ((!$this->source_title) or (stripos($this->source_title, 'soundcloud') !== false));
    }
    
    public function audioEmbed500(){
        return $this->player;
    }
    
    public function playCount(){
        return $this->plays;
    }
    
    public function formattedPlayCount(){
        return number_format($this->plays);
    }
    
    public function playCountWithLabel(){
        return $this->formattedPlayCount()." plays";
    }
    
    public function albumArtURL(){
        return $this->album_art;
    }

    public function albumArt() {
        return !empty($this->album_art);
    }
    
    public function artist() {
        return $this->artist;
    }
    
    public function album() {
        return $this->album;
    }
    
    public function trackName() {
        return $this->track_name;
    }
    
    public function rawAudioURL(){      
        return $this->audio_url; 
    }

    public function choosePlayer($color) {
        if ($this->source_title) {
            if ( stripos($this->source_title, 'soundcloud') !== false) {
                return $this->createAudioPlayer($color,'soundcloud');
            } else {
                return $this->player;
            }
        } else {
             return $this->createAudioPlayer($color);
        }
    }
    
    public function audioPlayerWhite(){ 
        return $this->choosePlayer('white');        
    }
    public function audioPlayerGrey(){  
        return $this->choosePlayer('grey');        
    }
    public function audioPlayerBlack(){ 
        return $this->choosePlayer('black');        
    }
    
    public function jsAudioPlayerWhite() {
        return "\x3cspan id=\x22audio_player_".$this->id."\x22\x3e[\x3ca href=\x22http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash\x22 target=\x22_blank\x22\x3eFlash 9\x3c/a\x3e is required to listen to audio.]\x3c/span\x3e\x3cscript type=\x22text/javascript\x22\x3ereplaceIfFlash(9,\x22audio_player_".$this->id."\x22,\'\\x3cdiv class=\\x22audio_player\\x22\\x3e\x3cembed type=\x22application/x-shockwave-flash\x22 src=\x22http://assets.tumblr.com/swf/audio_player.swf?audio_file=".urlencode($this->audio_url)."\x26color=FFFFFF\x26logo=soundcloud\x22 height=\x2227\x22 width=\x22207\x22 quality=\x22best\x22 wmode=\x22opaque\x22\x3e\x3c/embed\x3e\\x3c/div\\x3e\')\x3c/script\x3e";
    }

    private function makeSpotifyEmbed($source, $width) {
        $height = $width + 80;
        $player = '<iframe src="'.$source.'"';
        $player .= 'width="'.$width.'" height="'.$height.'" frameborder="0" allowtransparency="true">';
        $player .= '</iframe>';
        return $player;
    }

    /**
     * NOT SUPPORTED TAGS 
     */
    public function audioEmbed250(){    
        return ($this->spotify_link) ? $this->makeSpotifyEmbed($this->spotify_link, 250) : $this->player ;
    }
    public function audioEmbed400(){    
        return ($this->spotify_link) ? $this->makeSpotifyEmbed($this->spotify_link, 400) : $this->player ;
    }
    public function audioEmbed640(){
        return ($this->spotify_link) ? $this->makeSpotifyEmbed($this->spotify_link, 640) : $this->player ;
    }

    public function externalAudioURL(){ 
        throw new TagNotSupportedException("ExternalAudioURL");}

    public function renderBlock($block, ThmrParser $parser) {
        foreach ($this->conditional_fields as $label=>$variable) {
            if ($this->$variable) {
                $conditional_block = $parser->getBlockWithLabel($label);
                $variables_in_block = $parser->GetVariablesFor($conditional_block);
                $variables_values = $this->getVariableValues($variables_in_block);
                $conditional_block = $parser->replaceVariablesInBlock($conditional_block, $variables_values);
                $block = preg_replace($parser->block_pattern($label), $conditional_block, $block);
            } else {
                $block = $parser->strip_block($label, $block);
            }
        }
        $variables_in_block = $parser->GetVariablesFor($block);
        $variables_values = $this->getVariableValues($variables_in_block);
        $block = $parser->replaceVariablesInBlock($block, $variables_values);
        
        return $block;
    }

    public function parse(TumblrLoopParser $parser, $html, $blockName = 'document', $blockParams = null) {
      return $parser->parseAudioPost($this, $html, $blockName, $blockParams);
    } 
}
?>
