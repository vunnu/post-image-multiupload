<?php
/*
Plugin Name: Post image multiupload
Plugin URI: nwagency.eu
Description: Image uploader for post types
Version: 0.1
Author: vadim k.
Author URI: nwagency.eu
*/

define('PIM_VERSION', '1.0');
define('PIM_BASE', plugin_dir_path(__FILE__));
define('PIM_BASE_URL', plugin_dir_url(__FILE__));
define('PIM_TEMPLATE_PATH', plugin_dir_path(__FILE__) . 'templates/');
define('PIM_RESOURCES_URL', PIM_BASE_URL . 'src/resources/');
define('PIM_MAIN_FILE', __FILE__);


class PostImageMultiupload{

    public function __construct()
    {
        //Include Post Type Classes
        include_once(PIM_BASE . '/class/Singleton.php');
        include_once(PIM_BASE . '/class/PIM_Base.php');
        include_once(PIM_BASE . '/class/PIM_Gallery.php');
        include_once(PIM_BASE . '/class/PIM_SlideObj.php');



        //Include frontend functions
        include_once(PIM_BASE . '/includes/PIM_Functions.php');
        include_once(PIM_BASE . '/includes/PIM_Admin_Functions.php');

        //include Admin metaboxes
        include_once(PIM_BASE . '/admin/metaboxes/PIM_Metabox.php');

        //include admin filters and columns
//        include_once(PIM_BASE . 'admin/filters/WA_Filter.php');
//        include_once(PIM_BASE . 'admin/columns/WA_Column.php');

        //Loading scripts
//        add_action( 'init', array( $this, 'load_styles' ), 2 );
        add_action( 'init', array( $this, 'load_scripts' ), 2 );

        // Adding action to post type creation
        add_action( 'admin_print_scripts-post-new.php', array( $this, 'post_admin_scripts' ) );
        add_action( 'admin_print_scripts-post.php', array( $this, 'post_admin_scripts' ) );

        add_action( 'init', array( $this, 'register_post_types' ), 2 );

        add_action( 'init', array( $this, 'populate_post_types' ), 2 );

        add_action('init', array($this, 'load_plugin_textdomain'));
    }



    function post_admin_scripts()
    {
        global $post_type;

//        if (in_array($post_type, array('fund', 'document', 'fund_manager', 'fund_customer', 'chart'))) {
//
//            wp_register_style('bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css', array(), '1.0', 'all');
//            wp_enqueue_style('bootstrap'); // Enqueue it!
//        }

    }


    public function load_scripts()
    {
        if(is_admin())
        {

        }
    }


    public function populate_post_types(){

        $term = get_term_by('slug', 'pim_image', 'location');

        if($term)
            return false;

        wp_insert_term(
            'pim_gallery', // the term
            'location', // the taxonomy
            array(
                'description'=> 'Post multiupoad gallery image category',
                'slug' => 'pim_image',
            )
        );
    }


    // Including post types
    public function register_post_types()
    {

        $post_types = array(
            array(
                'id' => 'pim' . '_slide_obj',
                'name_single' => __('Slide object', 'pim'),
                'name_plural' => __('Slide objects', 'pim'),
                'args' => array(
                    'public' => false,
                    'show_in_nav_menus' => false,
                    'show_in_menu' => false,
                    'show_in_admin_bar' => false,
                    'rewrite' => array('slide_obj' => __('slide_obj', 'pim')),
                    'supports' => array()
                )
            )
        );

        $taxonomies = array(
            array(
                'id' => 'location',
                'name_single' => 'Location',
                'name_plural' => 'Locations',
                'posts' => array('attachment', ),
                'args' => array(
                    'rewrite' => array( 'slug' => __('location', 'sca') ),
                    'hierarchical' => true,
                    'query_var' => true,
                    'show_in_menu' => false,
                    'show_admin_column' => false,
                )
            )
        );



        foreach($post_types as $post_type)
        {
            // Add new custom post type
            $labels = array(
                'name' => _x($post_type['name_plural'], 'Product plural name', 'sca'),
                'singular_name' => _x($post_type['name_single'], 'Product singular name', 'sca'),
                'menu_name' => _x($post_type['name_plural'], 'admin menu', 'sca'),
                'name_admin_bar' => _x($post_type['name_single'], 'add new on admin bar', 'sca'),
                'add_new' => _x('Add New', $post_type['name_single'], 'sca'),
                'add_new_item' => __('Add New ' . $post_type['name_single'], 'sca'),
                'new_item' => __('New ' . $post_type['name_single'], 'sca'),
                'edit_item' => __('Edit ' . $post_type['name_single'], 'sca'),
                'view_item' => __('View ' . $post_type['name_single'], 'sca'),
                'all_items' => __('Our ' . $post_type['name_plural'], 'sca'),
                'search_items' => __('Search ' . $post_type['name_plural'], 'sca'),
                'parent_item_colon' => __('Parent ' . $post_type['name_single'] . ':', 'sca'),
                'not_found' => __('No '. $post_type['name_plural'] .' found.', 'sca'),
                'not_found_in_trash' => __('No '. $post_type['name_plural'] .' found in Trash.', 'sca')
            );

            $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'query_var' => true,
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => null,
            );

            $args = array_merge($args, $post_type['args']);

            register_post_type($post_type['id'], $args);
        }


        foreach ($taxonomies as $taxonomy) {

            // Add new taxonomy, NOT hierarchical (like tags)
            $labels = array(
                'name'                       => _x( $taxonomy['name_plural'], 'taxonomy general name', 'sca' ),
                'singular_name'              => _x( $taxonomy['name_plural'], 'taxonomy singular name', 'sca' ),
                'search_items'               => __( 'Search ' . $taxonomy['name_single'], 'sca' ),
                'popular_items'              => __( 'Popular ' . $taxonomy['name_plural'], 'sca' ),
                'all_items'                  => __( 'All ' . $taxonomy['name_plural'], 'sca' ),
                'parent_item'                => null,
                'parent_item_colon'          => null,
                'edit_item'                  => __( 'Edit ' . $taxonomy['name_single'], 'sca' ),
                'update_item'                => __( 'Update ' . $taxonomy['name_single'], 'sca' ),
                'add_new_item'               => __( 'Add New ' . $taxonomy['name_single'], 'sca' ),
                'new_item_name'              => __( 'New ' . $taxonomy['name_single'] . ' Name', 'sca' ),
                'separate_items_with_commas' => __( 'Separate ' . $taxonomy['name_plural'] . ' with commas', 'sca' ),
                'add_or_remove_items'        => __( 'Add or remove ' . $taxonomy['name_plural'], 'sca' ),
                'choose_from_most_used'      => __( 'Choose from the most used ' . $taxonomy['name_plural'], 'sca' ),
                'not_found'                  => __( 'No ' . $taxonomy['name_plural'] . ' found.', 'sca' ),
                'menu_name'                  => __( $taxonomy['name_plural'], 'sca' ),
            );

            $args = array(
                'hierarchical'          => true,
                'labels'                => $labels,
                'show_ui'               => true,
                'archive'               => true,
                'show_admin_column'     => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var'             => true,
            );

            array_merge($args, $taxonomy['args']);

            register_taxonomy( $taxonomy['id'], $taxonomy['posts'], $args );
        }

    }




    /**
     * Localisation
     */
    public function load_plugin_textdomain()
    {
        $locale = apply_filters('plugin_locale', get_locale(), 'wa');
        $dir = trailingslashit(WP_LANG_DIR);

        load_textdomain('wa', $dir . 'wa/wa-' . $locale . '.mo');
        load_plugin_textdomain('wa', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }




}

return new PostImageMultiupload();