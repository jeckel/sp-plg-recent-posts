<?php
/*
Plugin Name: WP Recent Posts Shortcode
*/
defined('ABSPATH') or die ('No script kiddies please!');

class WP_Recent_Posts_Shortcode
{
    const THUMB_SIZE_ALIAS = 'recent-post-thumb';

    protected $config = array(
        'width' => 300,
        'height' => 150,
    );

    public function register()
    {
        // Support for Featured Images
        add_theme_support('post-thumbnails', array('post'));
        add_image_size(self::THUMB_SIZE_ALIAS, $this->config['width'], $this->config['height'], true);
        add_filter('image_size_names_choose', array($this, 'registerCustomImageSizes'));

        add_shortcode('recent_posts', array($this, 'getRecentPosts'));
    }

    public function registerCustomImageSizes( $sizes ) {
        return array_merge($sizes, array(
            self::THUMB_SIZE_ALIAS => __('Recent post thumbnails'),
        ));
    }

    public function getRecentPosts()
    {
        $posts = $this->loadRecentPosts();
        $toReturn = '';
        foreach($posts as $post) {
            //var_dump($post);
            $toReturn .= $this->renderPost($post);
        }
        return $toReturn;
    }

    public function loadRecentPosts()
    {
        $args = array(
            'numberposts' => 5,
            'offset'      => 0,
            'category'    => 0,
            'orderby'     => 'post_date',
            'order'       => 'DESC',
            'post_status' => 'publish',
            'post_type'   => 'post',
        );
        return wp_get_recent_posts($args, OBJECT);
    }

    public function renderPost(WP_Post $post)
    {
        $output = sprintf(
            '<div><div class="recent_post_thumb">%s</div><h1><a href="%s">%s</a></h1></div>',
            $this->renderFeaturedImage($post),
            get_permalink($post),
            $post->post_title
        );
        return $output;
    }

    public function renderFeaturedImage(WP_Post $post)
    {
        $image_id = get_post_thumbnail_id($post->ID);
        if (empty($image_id)) {
            return "No featured image";
        }
        $src = wp_get_attachment_image_src($image_id, self::THUMB_SIZE_ALIAS, false);
        if ($src[1] != $this->config['width'] || $src[2] != $this->config['height']) {
            // wrong size ==> need to regenerate the thumbnail
            $this->regenerateThumbnails($image_id);
        }
        return get_the_post_thumbnail($post->ID, self::THUMB_SIZE_ALIAS);
    }

    public function regenerateThumbnails($image_id)
    {
        require_once 'wp-admin/includes/image.php';
        $fullsizepath = get_attached_file( $image_id);
        $metadata =  wp_generate_attachment_metadata( $image_id, $fullsizepath);
        wp_update_attachment_metadata($image_id, $metadata);
    }
}

// Start up this plugin
add_action('init', 'RecentPostShortcode');
function RecentPostShortcode() {
    global $RecentPostShortcode;
    $RecentPostShortcode = new WP_Recent_Posts_Shortcode();
    $RecentPostShortcode->register();
}
