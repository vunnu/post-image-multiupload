<?php
/**
 * Created by PhpStorm.
 * User: Vadim
 * Date: 2015-09-03
 * Time: 09:30
 */


class PIM_Gallery extends PIM_Base
{


    public $id;

    public $post;

    static $post_type = 'attachment';



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
        $args = array(
            'post_type' => self::$post_type,
            'post_mime_type' => 'image',
            'posts_per_page' => '-1',
            'orderby'       => 'menu_order',
            'order'         => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'location',
                    'field' => 'slug',
                    'terms' => 'pim_image',
                )
            )

        );

        if($id_post)
            $args['post_parent'] = $id_post;


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

return PIM_Gallery::getInstance();