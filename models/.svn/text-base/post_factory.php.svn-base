<?php
/**
 * Description of PostFactory
 *
 * @author julia
 */
class PostFactory {
    
    private static function determineTypeOfPhotoPost($post_data) {
        if(count($post_data['photos']) > 1 ) {
            return new PhotosetPost($post_data);
        } else {
            return new PhotoPost($post_data);
        }
    }
    
    public static function Create($post_data) {
        switch ($post_data['type']) {
            case 'text':
                return new TextPost($post_data);
                break;
            case 'chat':
                return new ChatPost($post_data);
                break;
            case 'link':
                return new LinkPost($post_data);
                break;
            case 'photo':
                return PostFactory::determineTypeOfPhotoPost($post_data);
                break;
            case 'quote':
                return new QuotePost($post_data);
                break;
            case 'video':
                return new VideoPost($post_data);
                break;
            case 'audio':
                return new AudioPost($post_data);
                break;
            case 'answer':
                return new AnswerPost($post_data);
                break;
        }
    }
}

?>
