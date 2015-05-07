<?php
/**
 * Description of PhotosetPost
 *
 * @author julia
 */
class PhotosetPost Extends PhotoPost {
    //TODO: implement photoset layouts
    private $layout;
    
    public function constructAdditionalFields($post_data) {
        $this->rawData = $post_data;
        $this->photo  = isset($post_data['photos']) ? $post_data['photos']: array();
        $this->caption = isset($post_data['caption']) ? $post_data['caption'] : '';
        $this->layout = isset($post_data['photoset_layout']) ? $post_data['photoset_layout'] : 0;
    }

    public function getType() {
        return "PhotoSet";
    }

    public function photos() {
        $ret = array();
        $index = 0;
        foreach ($this->photo as $photo) {
            $ret[] = new PhotoSetPhoto($this->rawData, $index);
            $index++;
        }
        return $ret;
    }
    
    public function photoCount(){
        return count($this->photo);
    }
    
    /**
     * NOT SUPPORTED 
     */
    public function photoset450(){ 
        return $this->makePhotoSet(450);
    }
    public function photoset250(){ 
        return $this->makePhotoSet(250);
    }
    public function jSPhotosetLayout(){ throw new TagNotSupportedException('JSPhotosetLayout');}
    
    public function renderBlock($block, ThmrParser $parser) {
        return "<h2>Photoset Post Render Not Implemented</h2>";
    }

    public function photoset500() {
        return $this->makePhotoSet(500);
    }

    public function photoset700() {
        return $this->makePhotoSet(700);
    }


    public function photoset400() {
        return $this->makePhotoSet(400);
    }

    public function makePhotoSet($photoset_width) {

        $l = $this->layout;
        $array = str_split($l);
        $photos = $this->photo;
        $photosetString = '<div id="photoset_'.$this->id.'" class="photoset" style="margin-bottom:10px;white-space:nowrap;overflow: hidden;margin-top: 10px;">';
        foreach ($array as $row) {
            $photosetString .= '<div class="photoset_row" style="width:'.$photoset_width.'px; white-space:nowrap;overflow: hidden;margin-top: 10px;">';
            $margin = ($row == 1 ) ? 0 : ($row-1)*10;
            $width = ($photoset_width - $margin) / $row;
            for ($i=0; $i < $row; $i++) { 
                $photo = array_shift($photos);
                $margin = ($i < $row - 1) ? 10 : 0;
                $photosetString .='<a href="'.$photo['alt_sizes'][0]['url'].'" style="display: inline-block;vertical-align: top;border:none;margin:none;margin-right: '.$margin.'px;" class="photoset_photo" id="photoset_link_133573456_1">';
                $photosetString .='<img style="width:'.$width.'px;margin:0;padding:0;border:none;" src="'.$photo['alt_sizes'][0]['url'].'" alt=""></a>';
            }
            $photosetString .= '</div>';
        }
        $photosetString .= '</div>';
        return $photosetString;
    }


    public function parse(TumblrLoopParser $parser, $html, $blockName = 'document', $blockParams = null) {
      return $parser->parsePhotoSetPost($this, $html, $blockName, $blockParams);
    }
}

?>
