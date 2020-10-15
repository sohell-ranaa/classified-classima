<?php
/**
 *Manage Listing by user
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var WP_Query $rtcl_query
 */


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Pagination;

global $post;
?>

<div class="rtcl rtcl-listings manage-listing">

    <!-- header here -->
    <div class="action-wrap mb-2">
        <div class="float-sm-left">
            <form action="<?php echo Link::get_account_endpoint_url("listings"); ?>" class="form-inline">
                <label class="sr-only" for="search-ml"><?php esc_html_e("Name", "classified-listing") ?></label>
                <input type="text" id="search-ml" name="u" class="form-control mb-2 mr-sm-2"
                       placeholder="<?php esc_html_e("Search by title", 'classified-listing'); ?>"
                       value="<?php echo isset($_GET['u']) ? sanitize_text_field($_GET['u']) : ''; ?>">
                <button type="submit" class="btn btn-primary mb-2"><?php esc_html_e("Search",
                        'classified-listing'); ?></button>
            </form>
        </div>
        <div class="float-sm-right">
            <a href="<?php echo Link::get_listing_form_page_link(); ?>"
               class="btn btn-success"><?php esc_html_e('Add New Listing', 'classified-listing'); ?></a>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php if ($rtcl_query->have_posts()): ?>
        <div class="rtcl-list-view">
            <!-- the loop -->
            <?php while ($rtcl_query->have_posts()) : $rtcl_query->the_post();
                $post_meta = get_post_meta($post->ID);
                $listing = rtcl()->factory->get_listing($post->ID);
                ?>
                <div class="listing-item rtcl-listing-item">
                    <div class="listing-thumb">
                        <a href="<?php the_permalink(); ?>"><?php $listing->the_thumbnail(); ?></a>
                    </div>
                    <div class="item-content">
                        <div class="rtcl-listings-title-block">
                            <h3 class="listing-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <?php $listing->the_labels(); ?>
                        </div>
                        <?php $listing->the_meta(); ?>

                        <p class="mb-0">
                            <strong><?php esc_html_e('Status', 'classified-listing'); ?></strong>:
                            <?php echo Functions::get_status_i18n($post->post_status); ?>
                        </p>

                        <?php if (get_post_meta($listing->get_id(), 'never_expires', true)) : ?>
                            <p class="rtcl-never-expired">
                                <strong><?php esc_html_e('Expires on', 'classified-listing'); ?></strong>:
                                <?php esc_html_e('Never Expires', 'classified-listing'); ?>
                            </p>
                        <?php elseif ($expiry_date = get_post_meta($listing->get_id(), 'expiry_date', true)) : ?>
                            <div class="rtcl-expired-on">
                                <strong><?php esc_html_e('Expires on', 'classified-listing'); ?></strong>:
                                <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'),
                                    strtotime($expiry_date)); ?>
                            </div>
                        <?php endif; ?>

                        <?php do_action('rtcl_listing_loop_extra_meta', $listing); ?>

                    </div>
                    <div class="rtcl-actions">
                        <div class="btn-group btn-group-justified manage-listing-btn">
                            <?php if (!Functions::is_payment_disabled()): ?>
                                <a href="<?php echo esc_url(Link::get_checkout_endpoint_url("submission", $post->ID)); ?>"
                                   class="btn btn-primary btn-sm btn-block">
                                    <?php esc_html_e('Promote', 'classified-listing') ?>
                                </a>
                            <?php endif; ?>
                            <?php if (Functions::current_user_can('edit_' . rtcl()->post_type, $post->ID)): ?>
                                <a href="<?php echo esc_url(Link::get_listing_edit_page_link($post->ID)); ?>"
                                   class="btn btn-info btn-sm rtcl-edit-listing"
                                   data-id="<?php echo esc_attr($post->ID) ?>">
                                    <?php esc_html_e('Edit', 'classified-listing') ?>
                                </a>
                            <?php endif; ?>
                            <?php if (Functions::current_user_can('delete_' . rtcl()->post_type, $post->ID)): ?>
                                <a href="#" class="btn btn-danger btn-sm rtcl-delete-listing"
                                   data-id="<?php echo esc_attr($post->ID) ?>">
                                    <?php esc_html_e('Delete', 'classified-listing') ?>
                                </a>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
            <!-- end of the loop -->
        </div>
        <!-- pagination here -->
        <?php Pagination::pagination($rtcl_query); ?>
    <?php else: ?>
        <p><?php esc_html_e("No listing found.", 'classified-listing'); ?></p>
    <?php endif; ?>
</div>