<?php
/**
 * Created by PhpStorm.
 * User: vadimk
 * Date: 2014-11-21
 * Time: 10:29
 */

namespace Uploader;

class PIM_Meta_Boxes {

    private $meta_boxes = array();

    public function __construct() {

        // Include the meta box classes
        $this->meta_boxes[] = include('PIM_Meta_Box_UploadForm.php');



        // Set up required actions
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 1 );
    }

    /**
     * Add meta boxes to edit product page
     */
    public function add_meta_boxes() {

        foreach ( $this->meta_boxes as $meta_box ) {
            foreach ( $meta_box->post_types as $post_type ) {
                add_meta_box(
                    $meta_box->id,
                    $meta_box->title,
                    array( $meta_box, 'meta_box_inner' ),
                    $post_type,
                    $meta_box->context,
                    $meta_box->priority
                );
            }
        }
    }
}

return new PIM_Meta_Boxes();