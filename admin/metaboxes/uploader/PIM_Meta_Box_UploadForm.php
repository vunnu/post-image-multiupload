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
    
    private $upload_id;

    public function __construct()
    {

        $pimorder_options = get_option('pimorder_options');
        $pimorder_objects = isset($pimorder_options['objects']) ? $pimorder_options['objects'] : array();

        $this->id = 'pim_project_fields';
        $this->title = __('Images', 'pim');
        $this->context = 'normal';
        $this->priority = 'default';
        $this->post_types = $pimorder_objects;

        $this->upload_id = "pim_images"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == �img1� then $_POST[�img1�] will have all the image urls

        add_action('save_post', array($this, 'meta_box_save'), 10, 1);

        add_action( 'admin_enqueue_scripts', array( $this, 'styles_and_scripts' ) );
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


        wp_register_script('pim_popupformjs', PIM_RESOURCES_URL.'js/popup-form.js', array('jquery'));
        wp_enqueue_script('pim_popupformjs');


        wp_register_style('myplupload', PIM_RESOURCES_URL.'css/myplupload.css');
        wp_enqueue_style('myplupload');
    }


    

    public function meta_box_inner($post)
    {


        $post_id = $post->ID;

        $attachments = PIM_Gallery::get_list($post_id, true);


        $svalue = ""; // this will be initial value of the above form field. Image urls.

        $multiple = true; // allow multiple files upload

        $width = null; // If you want to automatically resize all uploaded images then provide width here (in pixels)

        $height = null; // If you want to automatically resize all uploaded images then provide height here (in pixels)

        ?>

        <script>
            jQuery(function() {
                jQuery( "#sortable" ).sortable({
                    change: function( event, ui ) {
                        jQuery('.images_order').attr('name', 'images_order[]');
                    }
                });
                jQuery( "#sortable" ).disableSelection();
            });
        </script>
        <div class="row">
            <label>Upload Images</label>
            <?php wp_nonce_field('pim-form-save', 'pim-save-form-nonce'); ?>
            <input type="hidden" name="<?php echo $this->upload_id; ?>" id="<?php echo $this->upload_id; ?>" value="<?php echo $svalue; ?>" class="img-id" />
            <input type="hidden" name="<?php echo $this->upload_id; ?>_base" id="<?php echo $this->upload_id; ?>_base" value="<?php echo $svalue; ?>" class="img-id" />
            <div class="plupload-upload-uic hide-if-no-js <?php if ($multiple): ?>plupload-upload-uic-multiple<?php endif; ?>" id="<?php echo $this->upload_id; ?>plupload-upload-ui">
                <input id="<?php echo $this->upload_id; ?>plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files'); ?>" class="button modal-upload-btn" />
                <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($this->upload_id . 'pluploadan'); ?>"></span>
                <?php if ($width && $height): ?>
                    <span class="plupload-resize"></span><span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
                    <span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
                <?php endif; ?>
                <div class="filelist"></div>
            </div>
            <div class="images plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?>" id="<?php echo $this->upload_id; ?>plupload-thumbs">
            </div>
            <div class="clear"></div>
            <div class="images plupload-attach" id="sortable">
                <?php foreach($attachments as $upload_id => $attachment): ?>
                    <?php $url = apply_filters('pim_image_url', $upload_id, 'thumbnail'); ?>
                    <div class="thumb" id="attachment_<?php echo $upload_id; ?>">
                        <img src="<?php echo $url; ?>" alt="">
                        <input type="hidden" name="uploaded_img[]" class="images_order" value="<?php echo $upload_id; ?>">
                        <a href="#" data-attachment-edit="<?php echo $upload_id; ?>" class="delete_attachment modal-edit-btn">
                            <span class="dashicons dashicons-edit"></span>
                        </a>
                        <a href="#" data-attachment-remove="<?php echo $upload_id; ?>" class="delete_attachment modal-remove-btn" style="float: right;">
                            <span class="dashicons dashicons-trash"></span>
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


                if (
                    // nonce was submitted and is verified
                    isset( $_POST['pim-save-form-nonce'] ) &&
                    wp_verify_nonce( $_POST['pim-save-form-nonce'], 'pim-form-save' )

                )
                {
                    // see if image data was submitted:
                    // sanitize the data and save it in the term_images array
                    if ( ! empty( $_POST[$this->upload_id] ) ) {
                        $images = $_POST[$this->upload_id];

                        if(!empty($_POST['uploaded_img']))
                        {
                            $images .= implode(',', $_POST['uploaded_img']);
                        }
                        delete_post_meta($post_id, $this->upload_id);
                        add_post_meta($post_id, $this->upload_id, json_encode($images), true);
                    }
                }


                if(isset($_POST['images_order']) && !empty($_POST['images_order']))
                {
                    delete_post_meta($post_id, $this->upload_id);
                    update_post_meta($post_id, $this->upload_id, json_encode(implode(',', $_POST['images_order'])));
                }

                if(!isset($_POST['images_order']) &&
                    !isset($_POST['uploaded_img']) &&
                    empty( $_POST[$this->upload_id])
                )
                {
                    delete_post_meta($post_id, $this->upload_id);
                }

            }

        }

    }


}

return new PIM_Meta_Box_ProjectFields();