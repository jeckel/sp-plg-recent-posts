<?php
/**
 * Plugin Name: WP Recent Posts Shortcode
 * Plugin URI: https://github.com/jeckel/sp-plg-recent-posts
 * Description: This plugin is designed to display some recent posts in a more adaptive way
 * Version: Alpha
 * Author: Jeckel
 * Author URI: http://www.jeckel.fr
 */
defined('ABSPATH') or die ('No script kiddies please!');

include_once __DIR__ . '/lib/config.php';

class WP_Recent_Posts_Shortcode
{
    protected $config;

    public function __construct()
    {
        $this->config = new WP_Recent_Posts_Config;
    }

    public function register()
    {
        // Support for Featured Images
        add_theme_support('post-thumbnails', array('post'));
        add_image_size(
            WP_Recent_Posts_Config::THUMB_SIZE_ALIAS,
            $this->config->getWidth(),
            $this->config->getHeight(),
            true
        );
        add_filter('image_size_names_choose', array($this, 'registerCustomImageSizes'));

        add_action('admin_menu', array($this, 'adminAction'));

        add_shortcode('recent_posts', array($this, 'getRecentPosts'));
    }

    public function registerCustomImageSizes($sizes)
    {
        return array_merge($sizes, array(
            WP_Recent_Posts_Config::THUMB_SIZE_ALIAS => __('Recent post thumbnails'),
        ));
    }

    public function adminAction()
    {
        add_options_page("WP Recent posts", "WP Recent posts", 'manage_options', "wp-recent-posts", array($this, 'adminPage'));
    }

    public function adminPage()
    {
        if($_POST['wprpo_hidden'] == 'Y') {
            $this->config->setWidth($_POST['wprpo_width']);
            $this->config->setHeight($_POST['wprpo_height']);
            echo '<div class="updated"><p><strong>' . _e('Options saved.') . '</strong></p></div>';
        }
        include __dir__ . "/admin.php";
    }

    public function getRecentPosts()
    {
        $posts = $this->loadRecentPosts();
        $toReturn = '';
        foreach($posts as $post) {
            $toReturn .= $this->renderPost($post);
        }
        return sprintf('<div class="row">%s</div>', $toReturn);
    }

    public function loadRecentPosts()
    {
        $args = array(
            'numberposts' => $this->config->getNbPosts(),
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
        $outputMask = '
            <div class="col-md-4">
                <div class="recent_post_thumb">
                    <a href="%2$s">%1$s</a>
                </div>
                <h4>
                    <a href="%2$s">%3$s</a>
                </h4>
                <div class="excerpt">%4$s <a href="%2$s">Read more...</a></div>
            </div>';
        $output = sprintf(
            $outputMask,
            $this->renderFeaturedImage($post),
            get_permalink($post),
            $post->post_title,
            $this->getPostExcerpt($post)
        );
        return $output;
    }

    public function getPostExcerpt(WP_Post $post)
    {
        if (! empty($post->post_excerpt)) {
            return $post_excerpt . ' [..]';
        }

        $content = strip_shortcodes($post->post_content);
        $content = substr($content, 0, strpos($content, ' ', $this->config->getExcerptLength())) . ' [..]';
        return $content;
    }

    public function renderFeaturedImage(WP_Post $post)
    {
        $image_id = get_post_thumbnail_id($post->ID);
        if (empty($image_id)) {
            return "No featured image";
        }
        $src = wp_get_attachment_image_src($image_id, WP_Recent_Posts_Config::THUMB_SIZE_ALIAS, false);
        if ($src[1] != $this->config->getWidth() || $src[2] != $this->config->getHeight()) {
            // wrong size ==> need to regenerate the thumbnail
            $this->regenerateThumbnails($image_id);
        }
        return get_the_post_thumbnail($post->ID, WP_Recent_Posts_Config::THUMB_SIZE_ALIAS);
    }

    public function regenerateThumbnails($image_id)
    {
        require_once 'wp-admin/includes/image.php';
        $fullsizepath = get_attached_file($image_id);
        $metadata =  wp_generate_attachment_metadata($image_id, $fullsizepath);
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
