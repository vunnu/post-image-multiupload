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

        $labels = array(
            'name'              => 'Locations',
            'singular_name'     => 'Location',
            'search_items'      => 'Search Locations',
            'all_items'         => 'All Locations',
            'parent_item'       => 'Parent Location',
            'parent_item_colon' => 'Parent Location:',
            'edit_item'         => 'Edit Location',
            'update_item'       => 'Update Location',
            'add_new_item'      => 'Add New Location',
            'new_item_name'     => 'New Location Name',
            'menu_name'         => 'Location',
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'query_var' => true,
            'rewrite' => true,
            'show_in_menu' => false,
            'show_admin_column' => false,
        );

        register_taxonomy( 'location', 'attachment', $args );

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