<?php
/**
 * Store single content
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.3.21
 *
 */

use Rtcl\Helpers\Functions;
use RtclStore\Helpers\Functions as StoreFunctions;

global $store;

if (StoreFunctions::is_store_expired()) {
    do_action('rtcl_single_store_expired_content');
    return;
}

do_action('rtcl_before_single_store');
?>
    <div class="rtcl store-content-wrap">
        <?php do_action('rtcl_before_single_store_content'); ?>
        <div class="store-banner">
            <div class="banner"><?php $store->the_banner(); ?></div>
            <div class="store-name-logo">
                <div class="store-logo"><?php $store->the_logo(); ?></div>
                <div class="store-info">
                    <div class="store-name"><h2><?php $store->the_title(); ?></h2></div>
                    <?php if ($store->is_rating_enable()): ?>
                        <div class="reviews-rating">
                            <?php echo Functions::get_rating_html($store->get_average_rating(), $store->get_review_counts()); ?>
                            <span class="reviews-rating-count">(<?php echo absint($store->get_review_counts()); ?>)</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="row store-information">
            <div class="col-md-8 col-sm-12">
                <div class="store-details">
                    <?php if ($store->get_the_slogan()): ?>
                        <h3 class="is-slogan"><?php $store->the_slogan(); ?></h3>
                    <?php endif; ?>
                    <div class="store-description">
                        <?php if ($store->get_the_description()): ?>
                            <div class="fade-content"><?php $store->the_description(100); ?></div>
                        <?php endif; ?>
                        <div class="fade-anchor">
                            <a href="#" class="fade-anchor-text">
                                <?php esc_html_e("More details about this shop", "classified-listing-store") ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php Functions::get_template('store/ad-listing'); ?>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="store-info">
                    <?php do_action('rtcl_single_store_information'); ?>
                </div>
            </div>
        </div>
        <!--  Store Modal  -->
        <?php do_action('rtcl_single_store_detail_modal'); ?>
    </div>

<?php
do_action('rtcl_after_single_store');