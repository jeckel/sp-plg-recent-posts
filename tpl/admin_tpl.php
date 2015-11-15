<?php
defined('ABSPATH') or die ('No script kiddies please!');
?>
<div class="wrap">
    <?php echo "<h2>" . __('WP Recent Post options', 'wprpo_trdom' ) . "</h2>"; ?>
     
    <form name="wprpo_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="wprpo_hidden" value="Y">
        <?php    echo "<h4>" . __( 'Featured image size', 'wprpo_trdom' ) . "</h4>"; ?>
        <p><?php _e("Image width : "); ?><input type="text" name="wprpo_width" value="<?php echo $this->config->getWidth(); ?>" size="20"><?php _e(" ex: 300" ); ?></p>
        <p><?php _e("Image height : "); ?><input type="text" name="wprpo_height" value="<?php echo $this->config->getHeight(); ?>" size="20"><?php _e(" ex: 150" ); ?></p>
        <hr />
        <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'wprpo_trdom' ) ?>" />
        </p>
    </form>
</div>