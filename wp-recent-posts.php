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

define('WP_RECENT_POSTS_SHORTCODE_PLUGGIN_DIR', __DIR__);

include_once WP_RECENT_POSTS_SHORTCODE_PLUGGIN_DIR . '/lib/config.php';
include_once WP_RECENT_POSTS_SHORTCODE_PLUGGIN_DIR . '/lib/admin.php';
include_once WP_RECENT_POSTS_SHORTCODE_PLUGGIN_DIR . '/lib/renderer.php';

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

        $config = $this->config;

        // Shortcode renderer
        add_shortcode('recent_posts', array(
            new WP_Recent_Posts_Renderer($config), 'renderRecentPostsShortcode')
        );

        // Add admin menu
        add_action('admin_menu', function() use ($config) {
            add_options_page(
                "WP Recent posts",
                "WP Recent posts",
                'manage_options',
                "wp-recent-posts",
                array(new WP_Recent_Posts_Admin($config), 'adminPage')
            );
        });
    }

    public function registerCustomImageSizes($sizes)
    {
        return array_merge($sizes, array(
            WP_Recent_Posts_Config::THUMB_SIZE_ALIAS => __('Recent post thumbnails'),
        ));
    }
}

// Start up this plugin
add_action('init', 'RecentPostShortcode');
function RecentPostShortcode() {
    global $RecentPostShortcode;
    $RecentPostShortcode = new WP_Recent_Posts_Shortcode();
    $RecentPostShortcode->register();
}
