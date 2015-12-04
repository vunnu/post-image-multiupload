<?php
/**
 * Created by PhpStorm.
 * User: Vadim
 * Date: 2015-04-23
 * Time: 15:55
 */

namespace PIM_GALLERY;

class Singleton
{
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @staticvar Singleton $instance The *Singleton* instances of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance($post = false)
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static($post);

            if (is_numeric($post)) {
                $instance->id = absint($post);
                $instance->post = get_post($instance->id);
            } elseif ($post instanceof WA_Project) {
                $instance->id = absint($post->id);
                $instance->post = $post;
            } elseif ($post instanceof WP_Post || isset($post->ID)) {
                $instance->id = absint($post->ID);
                $instance->post = $post;
            }

            $instance->init();

        }

        return $instance;
    }


    /**
     * @param $post
     * @return static
     */
    public static function getById($post)
    {
        $instance = new static($post);

        if (is_numeric($post)) {
            $instance->id = absint($post);
            $instance->post = get_post($instance->id);
        } elseif ($post instanceof WA_Project) {
            $instance->id = absint($post->id);
            $instance->post = $post;
        } elseif ($post instanceof WP_Post || isset($post->ID)) {
            $instance->id = absint($post->ID);
            $instance->post = $post;
        }


        $instance->init();

        return $instance;
    }


    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}