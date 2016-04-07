<?php
/**
 * Created by PhpStorm.
 * User: Vadim
 * Date: 2015-04-23
 * Time: 13:46
 */


class PIM_Functions{


    public function __construct()
    {

        //First page functions
        add_filter('first_page_blocks', array( $this, 'pim_first_page_blocks'), 0, 0);

        add_filter('pim_gallery_images', array( $this, 'pim_pim_images'), 0, 2);

        add_filter('pim_image_url', array( $this, 'pim_pim_image_url'), 0, 2);

        add_filter('pim_gallery', array( $this, 'pim_pim_gallery'), 0, 1);

        add_filter('attachment_fields_to_edit', array( $this, 'pim_edit_media_custom_field'), 11, 2 );

        add_filter('attachment_fields_to_save', array( $this, 'pim_save_media_custom_field'), 11, 2 );


    }


    public function pim_edit_media_custom_field( $form_fields, $post ) {
        $form_fields['slide_link'] = array( 'label' => __('Slide link', 'pim'), 'input' => 'text', 'value' => get_post_meta( $post->ID, '_slide_link', true ) );

        unset($form_fields['location']);
        return $form_fields;
    }

    public function pim_save_media_custom_field( $post, $attachment ) {
        update_post_meta( $post['ID'], '_slide_link', $attachment['slide_link'] );
        return $post;
    }


    public function pim_pim_images($post_id, $title_image = true)
    {
        $images = PIM_Gallery::get_list($post_id, true);


        if(!empty($images) && $images)
        {
            if(count($images) > 1)
            {
                reset($images);
                $key = key($images);


                $retArr = array();

                if($title_image)
                {
                    $retArr['title_image'] = $images[$key];
                    unset($images[$key]);
                }

                foreach($images as $image)
                {
                    $retArr['thumbs'][] = $image;
                }

                if($title_image)
                    return $retArr;
                return $retArr['thumbs'];
            }
        }

        return $images;
    }

    public function pim_pim_image_url($attachment_id, $size = 'small')
    {
        $feat_image = wp_get_attachment_image_src( $attachment_id, $size );

        return $feat_image[0];
    }


    public function pim_pim_gallery($post_id)
    {
        $gallery = apply_filters('pim_gallery_images', $post_id);

        ob_start();

        ?>

        <?php if(isset($gallery) && $gallery): ?>
        <?php $img = apply_filters('pim_image_url', $gallery['title_image']->ID); ?>
        <a href="<?php echo $img; ?>" data-rel="lightbox" class="lightbox">
            <img class="img-responsive img-title" src="<?php echo $img; ?>" alt="">
        </a>

        <ul class="thumbnails nav row">
            <?php foreach($gallery['thumbs'] as $image): ?>
                <?php $img = apply_filters('pim_image_url', $image->ID); ?>
                <li class="col-xs-4">
                    <a href="<?php echo $img ?>" data-rel="lightbox">
                        <img class="img-responsive" src="<?php echo $img ?>" alt="">
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }


    public function pim_first_page_blocks()
    {
        $meta_pages = get_option('_wa_front_page_link_ids');
        $pages = array();

        array_walk($meta_pages, function($id) use (&$pages){
            $pages[] = get_post($id);
        });

        return $pages;
    }
}

return new PIM_Functions();