<?php
/**
 * @author     RadiusTheme
 * @package    classified-listing-store/templates
 * @version    1.0.0
 *
 * @var \WP_User $current_user
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Resources\Options;
use RtclStore\Models\Membership;

?>

<div class="membership-statistic-report-wrap">
    <h4><?php esc_html_e("Membership Report", "classified-listing-store") ?></h4>
    <div class="statistic-report">
        <?php
        $member = rtclStore()->factory->get_membership();
        if ($member->has_membership()):?>
            <div class="reports">
                <div class="report-item rtcl-membership-status">
                    <label><?php esc_html_e('Status', 'classified-listing-store') ?></label>
                    <div class="value">
                        <?php if ($member->is_expired()): ?>
                            <span class="expired"><?php esc_html_e("Expired", "classified-listing-store") ?></span>
                        <?php else: ?>
                            <span class="active"><?php esc_html_e("Active", "classified-listing-store") ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="report-item rtcl-membership-validity">
                    <label><?php esc_html_e('Validity', 'classified-listing-store') ?></label>
                    <div class="value">
                        <?php
                        printf('<strong>%s:</strong> %s',
                            $member->is_expired() ? __("Expired at", "classified-listing-store") : __("Until", "classified-listing-store"),
                            Functions::datetime('rtcl', $member->get_expiry_date())
                        );
                        ?>
                    </div>
                </div>
                <div class="report-item rtcl-membership-remaining-ads">
                    <label><?php esc_html_e('Remaining Ads', 'classified-listing-store') ?></label>
                    <div class="value"><?php echo esc_html($member->get_remaining_ads()); ?></div>
                </div>
                <div class="report-item rtcl-membership-posted-ads">
                    <label><?php esc_html_e('Posted Ads', 'classified-listing-store') ?></label>
                    <div class="value"><?php echo esc_html($member->get_posted_ads()); ?></div>
                </div>
                <?php if (!empty($promotions = $member->get_promotions())): ?>
                    <div class="report-item rtcl-membership-promotions">
                        <table class="rtcl-responsive-table table table-hover table-stripped table-bordered">
                            <tr class="promotion-item">
                                <th class="promotion-label"><?php esc_html_e("Promotions", "classified-listing-store"); ?></th>
                                <th class="promotion-ads"><?php esc_html_e("Remaining ads", "classified-listing-store"); ?></th>
                                <th class="promotion-validity"><?php _e("Validation Duration<small>(# Days)</small>", "classified-listing-store"); ?></th>
                            </tr>
                            <?php foreach ($promotions as $promotion_key => $promotion): ?>
                                <tr class="promotion-item">
                                    <th class="promotion-label"
                                        data-label="<?php esc_html_e("Promotions:", "classified-listing-store"); ?>"><?php esc_html_e(Options::get_listing_promotions()[$promotion_key]); ?></th>
                                    <td class="promotion-ads"
                                        data-label="<?php esc_html_e("Remaining ads:", "classified-listing-store"); ?>"><?php esc_html_e($promotion['ads']); ?></td>
                                    <td class="promotion-validate"
                                        data-label="<?php esc_html_e('Validation Duration:', 'classified-listing-store') ?>"><?php esc_html_e($promotion['validate']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p><?php esc_html_e("You have no membership subscription.", "classified-listing-store") ?></p>
        <?php endif ?>
        <p><?php printf(__("You can buy a subscription from <a href='%s'>here</a>.", "classified-listing-store"), Link::get_checkout_endpoint_url('membership')) ?></p>
    </div>
</div>