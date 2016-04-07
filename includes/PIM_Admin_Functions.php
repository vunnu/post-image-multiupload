<?php
/**
 * Created by PhpStorm.
 * User: Vadim
 * Date: 2015-04-23
 * Time: 13:46
 */


class PIM_Admin_Functions{


    public function __construct()
    {

        add_action('admin_menu', array($this, 'admin_menu'));

        add_action('admin_init', array($this, 'update_options'));

        add_filter('image_upload_form', array( $this, 'ch_image_upload_form'), 0, 3);

        /**
         * ToDO: only started doing this one, so you have to finish. Add theme video support and complete file save
         */
        add_filter('video_upload_form', array( $this, 'ch_video_upload_form'), 0, 3);


    }


    //Global functions
    function ch_image_upload_form($post_id, $meta_name, $form_title)
    {

        ob_start();

        $existing_image_id = get_post_meta($post_id, $meta_name, true);
        $arr_existing_image = wp_get_attachment_image_src($existing_image_id, 'large');
        $existing_image_url = $arr_existing_image[0];

        ?>

        <h2><?php echo $form_title; ?></h2>

        <?php if(is_numeric($existing_image_id)): ?>
            <div classs="col-md-6" style="background-color: #3e4042;">
                <img src="<?php echo $existing_image_url; ?>" alt=""/>
            </div>
            <div class="col-md-6">
                Delete file: <input type="checkbox" name="delete_files[<?php echo $existing_image_id; ?>][<?php echo $meta_name; ?>]"
                                    id="delete_[<?php echo $existing_image_id; ?>][<?php echo $meta_name; ?>]" />
            </div>
        <?php endif; ?>

        Upload an image: <input type="file" name="<?php echo $meta_name; ?>" id="<?php echo $meta_name; ?>" />


    <?php
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }


    
    
    function admin_menu() {
        add_options_page(__('Image Multi upload settings', 'pimrder'), __('Image Multi upload settings', 'pimrder'), 'manage_options', 'pimorder-settings', array($this, 'admin_page'));
    }


    function admin_page() {
        include_once(PIM_BASE . '/settings.php');
    }


    function update_options() {

        if (!isset($_POST['pimorder_submit']))
            return false;

        $input_options = array();
        $input_options['objects'] = isset($_POST['objects']) ? $_POST['objects'] : '';
        $input_options['objects_slide'] = isset($_POST['objects_slide']) ? $_POST['objects_slide'] : '';
        $input_options['tags'] = isset($_POST['tags']) ? $_POST['tags'] : '';

        update_option('pimorder_options', $input_options);

        wp_redirect('admin.php?page=pimorder-settings&msg=update');
    }
}

return new PIM_Admin_Functions();