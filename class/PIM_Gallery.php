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

    private $article_main_color;

    private $article_url;



    /**
     * @return mixed
     */
    public function get_article_url()
    {
        return $this->article_url;
    }

    /**
     * @param mixed $article_url
     */
    public function set_article_url($article_url)
    {
        $this->article_url = $article_url;
    }


    /**
     * @return mixed
     */
    public function get_article_mainColor()
    {

        return $this->article_main_color;
    }

    /**
     * @param mixed $article_main_color
     */
    public function set_article_mainColor($article_main_color)
    {

        $this->article_main_color = $article_main_color;
    }


    /**
     * Saving Project parameters to meta
     */
    public function save()
    {

        $this->save_meta_value('_ch_news_main_color', $this->get_article_mainColor());
        $this->save_meta_value('_ch_news_url', $this->get_article_url());
    }

    /**
     * Initializing object parameters from meta
     */
    public function init()
    {

        $this->set_article_mainColor($this->get_meta_value('_ch_news_main_color'));
        $this->set_article_url($this->get_meta_value('_ch_news_url'));
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

return CH_News::getInstance();