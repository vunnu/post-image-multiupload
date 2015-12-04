<?php
/**
 * Created by PhpStorm.
 * User: Vadim
 * Date: 2015-04-27
 * Time: 08:47
 */


class PIM_Meta_Box_ProjectFields{


    public $id;
    public $title;
    public $context;
    public $priority;
    public $post_types;

    public function __construct()
    {

        $pimorder_options = get_option('pimorder_options');
        $pimorder_objects = isset($pimorder_options['objects']) ? $pimorder_options['objects'] : array();

        $this->id = 'pim_project_fields';
        $this->title = __('Project fields', 'pim');
        $this->context = 'normal';
        $this->priority = 'default';
        $this->post_types = $pimorder_objects;

        add_action('save_post', array($this, 'meta_box_save'), 10, 1);

        add_action( 'admin_enqueue_scripts', array( $this, 'styles_and_scripts' ) );

        add_action('admin_head', array($this, 'pim_admin_head'));

        add_action('wp_ajax_plupload_action', array($this, 'pim_plupload_action'));
    }

    /**
     * Add admin styles
     */
    public function styles_and_scripts()
    {
        global $post, $woocommerce, $wp_scripts;


        if(isset($post) && $post && !in_array($post->post_type, $this->post_types))// adjust this if-condition according to your theme/plugin
            return;

        wp_enqueue_script('plupload-all');

        wp_register_script('myplupload', PIM_RESOURCES_URL.'js/myplupload.js', array('jquery'));
        wp_enqueue_script('myplupload');

        wp_register_style('myplupload', PIM_RESOURCES_URL.'css/myplupload.css');
        wp_enqueue_style('myplupload');
    }



    function pim_admin_head() {
    // place js config array for plupload
        $plupload_init = array(
            'runtimes' => 'html5,silverlight,flash,html4',
            'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
            'container' => 'plupload-upload-ui', // will be adjusted per uploader
            'drop_element' => 'drag-drop-area', // will be adjusted per uploader
            'file_data_name' => 'async-upload', // will be adjusted per uploader
            'multiple_queues' => true,
            'max_file_size' => wp_max_upload_size() . 'b',
            'url' => admin_url('admin-ajax.php'),
            'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
            'filters' => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
            'multipart' => true,
            'urlstream_upload' => true,
            'multi_selection' => false, // will be added per uploader
            // additional post data to send to our ajax hook
            'multipart_params' => array(
                '_ajax_nonce' => "", // will be added per uploader
                'action' => 'plupload_action', // the ajax action name
                'imgid' => 0 // will be added per uploader
            )
        );
        ?>
        <script type="text/javascript">
            var base_plupload_config=<?php echo json_encode($plupload_init); ?>;
        </script>
        <?php
    }

    function pim_plupload_action() {

        // check ajax noonce
        $imgid = $_POST["imgid"];
        check_ajax_referer($imgid . 'pluploadan');

        // handle file upload
        $status = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'plupload_action'));

        // send the uploaded file url in response
        echo $status['url'];
        exit;
    }

    public function meta_box_inner($post)
    {


        $post_id = $post->ID;

        $attachments = PIM_Gallery::get_list($post_id, true);


        $id = "pim_images"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == “img1” then $_POST[“img1”] will have all the image urls

        $svalue = ""; // this will be initial value of the above form field. Image urls.

        $multiple = true; // allow multiple files upload

        $width = null; // If you want to automatically resize all uploaded images then provide width here (in pixels)

        $height = null; // If you want to automatically resize all uploaded images then provide height here (in pixels)

        ?>

        <script>
            jQuery(function() {
                jQuery( "#sortable" ).sortable();
                jQuery( "#sortable" ).disableSelection();
            });
        </script>
        <div class="row">
            <label>Upload Images</label>
            <input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $svalue; ?>" />
            <div class="plupload-upload-uic hide-if-no-js <?php if ($multiple): ?>plupload-upload-uic-multiple<?php endif; ?>" id="<?php echo $id; ?>plupload-upload-ui">
                <input id="<?php echo $id; ?>plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files'); ?>" class="button" />
                <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($id . 'pluploadan'); ?>"></span>
                <?php if ($width && $height): ?>
                    <span class="plupload-resize"></span><span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
                    <span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
                <?php endif; ?>
                <div class="filelist"></div>
            </div>
            <div class="images plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?>" id="<?php echo $id; ?>plupload-thumbs">
            </div>
            <div class="clear"></div>
            <div class="images plupload-attach" id="sortable">
                <?php foreach($attachments as $id => $attachment): ?>
                    <?php $url = wp_get_attachment_url( $id); ?>
                    <div class="thumb" id="attachment_<?php echo $id; ?>">
                        <img src="<?php echo $url; ?>" alt="">
                        <input type="hidden" name="images_order[]" id="images_order" value="<?php echo $id; ?>">
                        <a href="#" data-attachment-remove="<?php echo $id; ?>" class="delete_attachment">
                            Delete
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


    <?php
    }

    function meta_box_save($post_id)
    {

        if (in_array(get_post_type($post_id), $this->post_types)) {

            if(!isset($_POST['_inline_edit']))
            {
                $attachments = PIM_Gallery::get_list($post_id, true);


                if(isset($_POST['pim_images']) && $_POST['pim_images'])
                {
                    $images = explode(',', $_POST['pim_images']);

                    $i = 0;
                    foreach ($images as $image_path) {
                        $filetype = wp_check_filetype( basename( $image_path ), null );
                        $wp_upload_dir = wp_upload_dir();

                        $attachment = array(
                            'guid'           => $wp_upload_dir['url'] . '/' . basename( $image_path ),
                            'post_mime_type' => $filetype['type'],
                            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $image_path ) ),
                            'post_content'   => 'pim_gallery',
                            'post_status'    => 'inherit',
                            'menu_order'     => $i++,
                            'post_type'      => 'pim_image'
                        );

                        foreach( get_intermediate_image_sizes() as $s ) {
                            $sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => true );
                            $sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
                            $sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
                            $sizes[$s]['crop'] = get_option( "{$s}_crop" ); // For default sizes set in options
                        }

                        $sizes = apply_filters( 'intermediate_image_sizes_advanced', $sizes );


                        $attach_id = wp_insert_attachment( $attachment, $image_path, $post_id );
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $image_path );

                        foreach( $sizes as $size => $size_data ) {
                            $resized = image_make_intermediate_size( $image_path, $size_data['width'], $size_data['height'], $size_data['crop'] );
                            if ( $resized )
                                $attach_data['sizes'][$size] = $resized;
                        }

                        wp_update_attachment_metadata( $attach_id, $attach_data );

                        wp_set_object_terms($attach_id, array('pim_image'), 'location');
                    }

                }
                if(isset($_POST['images_order']) && !empty($_POST['images_order']))
                {
                    global $wpdb;

                    $id_arr = $_POST['images_order'];

                    $delete_images = array_diff_key($attachments, array_flip($id_arr));


                    foreach ($id_arr as $key => $id) {
                        $wpdb->update($wpdb->posts, array('menu_order' => $key), array('ID' => intval($id)));
                    }

                    if(!empty($delete_images))
                    {
                        foreach($delete_images as $attachmentid => $post)
                        {
                            wp_delete_attachment( $attachmentid, 1 );
                        }
                    }
                }else{

                    foreach($attachments as $attachmentid => $post)
                    {
                        wp_delete_attachment( $attachmentid, 1 );
                    }
                }

            }

        }

    }


}

return new PIM_Meta_Box_ProjectFields();