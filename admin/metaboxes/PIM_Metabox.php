<?php
/**
 * Created by PhpStorm.
 * User: vadimk
 * Date: 2014-11-21
 * Time: 10:57
 */

class PIM_Metabox{

	public function __construct()
    {

		add_action( 'admin_init', array( $this, 'include_meta_box_handlers' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'styles_and_scripts' ) );
	}


	/**
	 * Add admin styles
	 */
	public function styles_and_scripts()
    {
		global $post, $woocommerce, $wp_scripts;
	}

	/**
	 * Include meta box handlers
	 */
	public function include_meta_box_handlers()
    {

		include('uploader/PIM_Meta_Boxes.php');

	}


}

return new PIM_Metabox();