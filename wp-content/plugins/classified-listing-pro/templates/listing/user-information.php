<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var string  $phone
 * @var string  $whatsapp_number
 * @var string  $email
 * @var string  $alternate_contact_form
 * @var string  $website
 * @var array   $phone_options
 * @var bool    $has_contact_form
 * @var string  $email_to_seller_form
 * @var Listing $listing
 * @var int     $listing_id Listing id
 */

use Rtcl\Controllers\ChatController;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Text;
use Rtcl\Models\Listing;

?>
<div class="rtcl-listing-user-info">
    <div class="rtcl-listing-side-title">
        <h3><?php esc_html_e("Contact", 'classified-listing'); ?></h3>
    </div>
    <?php if (count($locations) || $phone || $email || $website || $alternate_contact_form) : ?>
        <div class="list-group">
            <?php
            if (!empty($locations)) : ?>
                <div class='list-group-item'>
                    <div class='media'>
                        <span class='rtcl-icon rtcl-icon-location mr-2'></span>
                        <div class='media-body'><span><?php _e("Location", "classified-listing") ?></span>
                            <div class='locations'><?php echo implode('<span class="rtcl-delimiter">,</span> ',
                                    $locations) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($phone) :
                $mobileClass = wp_is_mobile() ? " rtcl-mobile" : null;
                $phone_options = [
                    'safe_phone'   => mb_substr($phone, 0, mb_strlen($phone) - 3) . apply_filters('rtcl_phone_number_placeholder', 'XXX'),
                    'phone_hidden' => mb_substr($phone, -3)
                ];
                if ($whatsapp_number && !Functions::is_field_disabled('whatsapp_number')) {
                    $phone_options['safe_whatsapp_number'] = mb_substr($whatsapp_number, 0, mb_strlen($whatsapp_number) - 3) . apply_filters('rtcl_phone_number_placeholder', 'XXX');
                    $phone_options['whatsapp_hidden'] = mb_substr($whatsapp_number, -3);
                }
                $phone_options = apply_filters('rtcl_phone_number_options', $phone_options, ['phone' => $phone, 'whatsapp_number' => $whatsapp_number])
                ?>
                <div class='list-group-item reveal-phone<?php echo esc_attr($mobileClass); ?>'
                     data-options="<?php echo htmlspecialchars(wp_json_encode($phone_options)); ?>">
                    <div class='media'>
                        <span class='rtcl-icon rtcl-icon-phone mr-2'></span>
                        <div class='media-body'><span><?php esc_html_e("Contact Number",
                                    "classified-listing"); ?></span>
                            <div class='numbers'><?php echo esc_html($phone_options['safe_phone']); ?></div>
                            <small class='text-muted'><?php esc_html_e("Click to reveal phone number",
                                    "classified-listing") ?></small>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if ($has_contact_form && ($email || $alternate_contact_form)) : ?>
                <div class='rtcl-do-email list-group-item'>
                    <div class='media'>
                        <span class='rtcl-icon rtcl-icon-mail mr-2'></span>
                        <div class='media-body'>
                            <a class="rtcl-do-email-link" href='#'>
                                <span><?php echo Text::get_single_listing_email_button_text(); ?></span>
                            </a>
                        </div>
                    </div>
                    <?php
                    if ($alternate_contact_form) {
                        echo sprintf('<div id="rtcl-contact-form">%s</div>', do_shortcode($alternate_contact_form));
                    } else {
                        Functions::print_html($email_to_seller_form, true);
                    } ?>
                </div>
            <?php endif; ?>
            <?php
            if (Functions::is_enable_chat() && ((is_user_logged_in() && $listing->get_author_id() !== get_current_user_id()) || !is_user_logged_in())):
                $chat_btn_class = ['rtcl-chat-link'];
                $chat_url = Link::get_my_account_page_link();
                if (is_user_logged_in()) {
                    $chat_url = '#';
                    array_push($chat_btn_class, 'rtcl-contact-seller');
                } else {
                    array_push($chat_btn_class, 'rtcl-no-contact-seller');
                }
                ?>
                <div class='rtcl-contact-seller list-group-item'>
                    <a class="<?php echo esc_attr(implode(' ', $chat_btn_class)) ?>"
                       href="<?php echo esc_url($chat_url) ?>" data-listing_id="<?php the_ID() ?>">
                        <i class='rtcl-icon rtcl-icon-chat mr-1'> </i><?php esc_html_e("Chat", "classified-listing") ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php do_action('rtcl_add_user_information', $listing_id); ?>

            <?php if ($website) : ?>
                <div class='rtcl-website list-group-item'>
                    <a class="rtcl-website-link btn btn-primary" href="<?php echo esc_url($website); ?>"
                       target="_blank"<?php echo Functions::is_external($website) ? ' rel="nofollow"' : ''; ?>><span
                                class='rtcl-icon rtcl-icon-globe text-white'></span><?php esc_html_e("Visit Website", "classified-listing") ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

