<?php
/**
 * Created by PhpStorm.
 * User: Vadim
 * Date: 2015-09-03
 * Time: 09:30
 */


class PIM_SlideObj extends PIM_Base
{


    public $id;

    public $post;

    static $post_type = 'pim_slide_obj';

    protected $gallery_id;



    /**
     * Saving Project parameters to meta
     */
    public function save()
    {

    }

    /**
     * Initializing object parameters from meta
     */
    public function init()
    {

    }


    /**
     * Getting list of objects list
     */
    public function get_list_categorised($tax = 'ch_news_type')
    {
        $post_type = self::get_class_post_type();

        $tax_terms = get_terms( $tax, 'parent=0&orderby=name&order=ASC');


        $ret_array = array();

        if ($tax_terms) {
            foreach ($tax_terms  as $tax_term) {
                $args = array(
                    'post_type'			=> $post_type,
                    'post_mime_type' => 'image',
                    "$tax"				=> $tax_term->slug,
                    'post_status'		=> 'publish',
                    'suppress_filters' => false,
                    'posts_per_page'	=> -1
                );

                $ret_array[$tax_term->term_id] =  get_posts($args);

            }
        }


        return $ret_array;
    }


    /**
     * @return mixed
     * Getting list of class objects
     */
    static public function get_list($id_post = false, $key_to_ids = false, $to_object = false)
    {

        $images = get_post_meta($id_post, 'pim_images', true);

        $arr_img = explode(',', json_decode($images));

        $images = array_filter($arr_img);

        if(empty($images))
            return array();

        $args = array(
            'post_type' => self::$post_type,
            'post__in'  => $images,
            'post_mime_type' => 'image',
            'posts_per_page' => '-1',
            'orderby'       => 'post__in',
            'order'         => 'ASC',

        );



        $posts = get_posts($args);



        if($key_to_ids)
        {
            $posts_filtered = array();
            array_filter($posts, function($post) use (&$posts_filtered){

                $posts_filtered[$post->ID] = $post;
            });
            $posts = $posts_filtered;
        }

        if($to_object)
        {
            array_walk($posts, array(__CLASS__, 'translate_to_class_object'));
        }

        return $posts;
    }





    /**
     * Getting current class post_type
     */
    static public function get_class_post_type()
    {
        return self::$post_type;
    }
}

return PIM_SlideObj::getInstance();