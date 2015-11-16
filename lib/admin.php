<?php

defined('ABSPATH') or die ('No script kiddies please!');

class WP_Recent_Posts_Admin
{
    protected $config;

    public function __construct(WP_Recent_Posts_Config $config)
    {
        $this->config = $config;
    }

    public function adminPage()
    {
        if($_POST['wprpo_hidden'] == 'Y') {
            $this->saveOptions($_POST);
            echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';
        }
        include WP_RECENT_POSTS_SHORTCODE_PLUGGIN_DIR . "/tpl/admin_tpl.php";
    }

    public function saveOptions(array $options)
    {
        $this->config->setWidth($options['wprpo_width']);
        $this->config->setHeight($options['wprpo_height']);
        $this->config->setPostWidth($options['wprpo_post_width']);
    }
}
