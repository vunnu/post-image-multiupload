<?php
/**
 * Created by PhpStorm.
 * User: Vadim
 * Date: 2015-09-03
 * Time: 09:30
 */

use PIM_GALLERY\Singleton;

abstract class PIM_Base extends Singleton
{

    protected $id;

    protected static $post_type;


    /**
     * @param $param
     * @return bool|mixed
     * Getting object meta for key
     */
    public function get_meta_value($param, $single = true)
    {
        if($param)
        {
            return get_post_meta($this->id, $param, $single);
        }
        return false;
    }


    /**
     * @param $key
     * @param $value
     * @return bool|int
     * Saving object meta for key
     */
    public function save_meta_value($key, $value)
    {
        if($key && $value)
        {
            if(is_array($value))
            {
                //Cleaning meta for key
                delete_post_meta($this->id, $key);

                foreach ($value as $val) {
                    add_post_meta($this->id, $key, $val);
                }
            }else{

                return update_post_meta($this->id, $key, $value);
            }
        }
        return false;
    }



    public static function translate_to_class_object(WP_Post &$post)
    {
        $post =  self::getById($post->ID);
    }

    /**
     * @param $key
     * @return bool
     * Deleting object meta by key
     */
    public function delete_meta_key($key)
    {
        return delete_post_meta($this->id, $key);
    }


    /**
     * Saving Room parameters to meta
     */
    abstract public function save();

    /**
     * Initializing object parameters from meta
     */
    abstract public function init();


    /**
     * Getting list of class objects categorised by taxonomy
     */
    abstract public function get_list_categorised($taxonomy);


    /**
     * @return mixed
     * Getting list of class objects
     */
    static public function get_list(){

        $args = array(
            'post_type' => self::$post_type,
            'posts_per_page' => '-1'
        );

        $posts = get_posts($args);

        debugvar(self::$post_type);exit('---');
        array_walk($posts, array(__CLASS__, 'translate_to_class_object'));

        return $posts;
    }


    /**
     * Getting current class post_type
     */
    static public function get_class_post_type(){

        return self::post_type;
    }


}