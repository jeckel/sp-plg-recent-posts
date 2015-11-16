<?php

defined('ABSPATH') or die ('No script kiddies please!');

class WP_Recent_Posts_Config
{
    const THUMB_SIZE_ALIAS = 'recent-post-thumb';

    const FIELD_WIDTH      = 'wprpo_width';
    const FIELD_HEIGHT     = 'wprpo_height';
    const FIELD_POST_WIDTH = 'wprpo_post_width';
    const FIELD_NBPOSTS    = 'wprpo_nb_posts';
    const FIELD_EXCERPT_LENGHT = 'wprpo_excerpt_lenght';

    public $width;
    public $height;
    public $nb_posts;
    public $excerpt_length;

    public function getHeight()
    {
        if (is_null($this->height)) {
            $this->height = get_option(self::FIELD_HEIGHT, 150);
        }
        return $this->height;
    }

    public function getWidth()
    {
        if (is_null($this->width)) {
            $this->width  = get_option(self::FIELD_WIDTH, 300);
        }
        return $this->width;
    }

    public function getNbPosts()
    {
        if (is_null($this->nb_posts)) {
            $this->nb_posts = get_option(self::FIELD_NBPOSTS, 3);
        }
        return $this->nb_posts;
    }

    public function getExcerptLength()
    {
        if (is_null($this->excerpt_length)) {
            $this->excerpt_length = get_option(self::FIELD_EXCERPT_LENGHT, 100);
        }
        return $this->excerpt_length;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        update_option(self::FIELD_WIDTH, $this->width);
        return $this;
    }

    public function setHeight($height)
    {
        $this->height = $height;
        update_option(self::FIELD_HEIGHT, $this->height);
        return $this;
    }

    public function setPostWidth($width)
    {
        $this->post_width = $width;
        update_option(self::FIELD_POST_WIDTH, $this->post_width);
        return $this;
    }

    public function getPostWidth()
    {
        if (is_null($this->post_width)) {
            $this->post_width = get_option(self::FIELD_POST_WIDTH, '300px');
        }
        return $this->post_width;
    }

    public function setNbPosts($nb_posts)
    {
        $this->nb_posts = $nb_posts;
        update_option(self::FIELD_NBPOSTS, $this->nb_posts);
        return $this;
    }

    public function setExcerptLength($excerpt_length)
    {
        $this->excerpt_length = $excerpt_length;
        update_option(self::FIELD_EXCERPT_LENGHT, $this->excerpt_length);
        return $this;
    }
}
