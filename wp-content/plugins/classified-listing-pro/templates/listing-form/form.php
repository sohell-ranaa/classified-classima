<?php
/**
 * Listing Form
 *
 * @author    RadiusTheme
 * @package   classified-listing/templates
 * @version   1.0.0
 *
 * @var int    $category_id
 * @var string $selected_type
 */

?>

<div class="rtcl rtcl-user rtcl-post-form-wrap">
    <?php do_action("rtcl_listing_form_before", $post_id); ?>
    <form action="" method="post" id="rtcl-post-form" class="form-vertical">
        <?php do_action("rtcl_listing_form_start", $post_id); ?>
        <div class="rtcl-post">
            <?php do_action("rtcl_listing_form", $post_id); ?>
        </div>
        <button type="submit" class="btn btn-primary rtcl-submit-btn">
            <?php
            if ($post_id > 0) {
                esc_html_e('Update', 'classified-listing');
            } else {
                esc_html_e('Submit', 'classified-listing');
            }
            ?>
        </button>
        <?php do_action("rtcl_listing_form_end", $post_id); ?>
    </form>
    <?php do_action("rtcl_listing_form_after", $post_id); ?>
</div>
