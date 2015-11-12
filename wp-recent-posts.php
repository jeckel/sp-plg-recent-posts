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

class WP_Recent_Posts_Shortcode
{
    const THUMB_SIZE_ALIAS = 'recent-post-thumb';
    const DEFAULT_WIDTH    = 300;
    const DEFAULT_HEIGHT   = 150;

    protected $config = array(
        'width'          => self::DEFAULT_WIDTH,
        'height'         => self::DEFAULT_HEIGHT,
        'nb_posts'       => 3,
        'excerpt_length' => 100
    );

    public function register()
    {
        // Support for Featured Images
        $this->loadOptions();
        add_theme_support('post-thumbnails', array('post'));
        add_image_size(self::THUMB_SIZE_ALIAS, $this->config['width'], $this->config['height'], true);
        add_filter('image_size_names_choose', array($this, 'registerCustomImageSizes'));

        add_action('admin_menu', array($this, 'adminAction'));

        add_shortcode('recent_posts', array($this, 'getRecentPosts'));
    }

    public function registerCustomImageSizes($sizes)
    {
        return array_merge($sizes, array(
            self::THUMB_SIZE_ALIAS => __('Recent post thumbnails'),
        ));
    }

    public function adminAction()
    {
        add_options_page("WP Recent posts", "WP Recent posts", 'manage_options', "wp-recent-posts", array($this, 'adminPage'));
    }

    public function adminPage()
    {
        if($_POST['wprpo_hidden'] == 'Y') {
            $this->config['width'] = $_POST['wprpo_width'];
            update_option('wprpo_width', $this->config['width']);

            $this->config['height'] = $_POST['wprpo_height'];
            update_option('wprpo_height', $this->config['height']);

            echo '<div class="updated"><p><strong>' . _e('Options saved.') . '</strong></p></div>';
        }
        include __dir__ . "/admin.php";
    }

    public function loadOptions()
    {
        $this->config['width'] = get_option('wprpo_width', self::DEFAULT_WIDTH);
        $this->config['height'] = get_option('wprpo_height', self::DEFAULT_HEIGHT);
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
            'numberposts' => $this->config['nb_posts'],
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
        $content = substr($content, 0, strpos($content, ' ', $this->config['excerpt_length'])) . ' [..]';
        return $content;
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
