<?php
/**
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 * @var Listing $listing ;
 */

use Rtcl\Models\Listing;

global $listing;
?>
<?php if (($listing->can_show_new() && $listing->is_new()) ||
    ($listing->can_show_popular() && $listing->is_popular()) ||
    ($listing->can_show_top() && $listing->is_top()) ||
    ($listing->can_show_bump_up() && $listing->is_bump_up()) ||
    ($listing->can_show_featured() && $listing->is_featured())) : ?>

    <div class='rtcl-listing-badge-wrap'>
        <?php if ($listing->can_show_new() && $listing->is_new()) : ?>
            <span class="badge new-badge badge-primary"><?php echo esc_html($listing->get_new_label_text()); ?></span>
        <?php endif; ?>

        <?php if ($listing->can_show_popular() && $listing->is_popular()) : ?>
            <span class="badge popular-badge badge-success"><?php echo esc_html($listing->get_popular_label_text()); ?></span>
        <?php endif; ?>

        <?php if ($listing->can_show_top() && $listing->is_top()) : ?>
            <span class="badge top-badge badge-warning"><?php echo esc_html($listing->get_top_label_text()); ?></span>
        <?php endif; ?>

        <?php if ($listing->can_show_featured() && $listing->is_featured()) : ?>
            <span class="badge feature-badge badge-info"><?php echo esc_html($listing->get_featured_label_text()); ?></span>
        <?php endif; ?>

        <?php if ($listing->can_show_bump_up() && $listing->is_bump_up()) : ?>
            <span class="badge bump-up-badge badge-danger"><?php echo esc_html($listing->get_bump_up_label_text()); ?></span>
        <?php endif; ?>
    </div>

<?php endif; ?>