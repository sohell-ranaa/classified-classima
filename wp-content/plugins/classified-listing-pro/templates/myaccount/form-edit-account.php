<?php
/**
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var WP_User $user
 * @var string  $phone
 * @var string  $whatsapp_number
 * @var string  $website
 * @var string  $state_text
 * @var string  $city_text
 * @var array   $user_locations
 * @var int     $sub_location_id
 * @var int     $location_id
 * @var string  $town_text
 * @var string  $zipcode
 * @var float   $latitude
 * @var float   $longitude
 * @var int     $pp_id
 */

use Rtcl\Helpers\Functions;

if (!defined('ABSPATH')) {
    exit;
}

do_action('rtcl_before_edit_account_form'); ?>

<form class="rtcl-EditAccountForm form-horizontal" id="rtcl-user-account" method="post">

    <?php do_action('rtcl_edit_account_form_start'); ?>

    <div class="form-group row">
        <label for="rtcl-username"
               class="col-sm-3 control-label"><?php _e('Username', 'classified-listing'); ?></label>
        <div class="col-sm-9">
            <p class="form-control-static"><strong><?php echo esc_html($user->user_login); ?></strong></p>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-first-name"
               class="col-sm-3 control-label"><?php _e('First Name', 'classified-listing'); ?></label>
        <div class="col-sm-9">
            <input type="text" name="first_name" id="rtcl-first-name" value="<?php echo esc_attr($user->first_name); ?>"
                   class="form-control"/>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-last-name"
               class="col-sm-3 control-label"><?php _e('Last Name', 'classified-listing'); ?></label>
        <div class="col-sm-9">
            <input type="text" name="last_name" id="rtcl-last-name" value="<?php echo esc_attr($user->last_name); ?>"
                   class="form-control"/>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-email" class="col-sm-3 control-label"><?php _e('E-mail Address', 'classified-listing'); ?>
            <strong>*</strong></label>
        <div class="col-sm-9">
            <input type="email" name="email" id="rtcl-email" class="form-control"
                   value="<?php echo esc_attr($user->user_email); ?>" required="required"/>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-profile-picture" class="col-sm-3 control-label">
            <?php _e('Profile Picture', 'classified-listing'); ?><strong>*</strong>
        </label>
        <div class="col-sm-9">
            <div class="rtcl-profile-picture-wrap">
                <?php if (!$pp_id): ?>
                    <div class="rtcl-gravatar-wrap">
                        <?php echo get_avatar($user->ID);
                        echo "<p>" . sprintf(
                                __('<a href="%s">Change on Gravatar</a>.', 'classified-listing'),
                                __('https://en.gravatar.com/', 'classified-listing')
                            ) . "</p>";
                        ?>
                    </div>
                <?php endif; ?>
                <div class="rtcl-media-upload-wrap">
                    <div class="rtcl-media-upload rtcl-media-upload-pp<?php echo($pp_id ? ' has-media' : ' no-media') ?>">
                        <div class="rtcl-media-action">
                            <span class="rtcl-icon-plus add"><?php esc_html_e("Add Logo", "classified-listing"); ?></span>
                            <span class="rtcl-icon-trash remove"><?php esc_html_e("Delete Logo", "classified-listing"); ?></span>
                        </div>
                        <div class="rtcl-media-item">
                            <?php echo($pp_id ? wp_get_attachment_image($pp_id, [100, 100]) : '') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-offset-3 col-sm-9">
            <div class="form-check">
                <input type="checkbox" name="change_password" class="form-check-input" id="rtcl-change-password"
                       value="1">
                <label class="form-check-label" for="rtcl-change-password">
                    <?php _e('Change Password', 'classified-listing'); ?>
                </label>
            </div>
        </div>
    </div>

    <div class="form-group row rtcl-password-fields" style="display: none;">
        <label for="password" class="col-sm-3 control-label"><?php _e('New Password', 'classified-listing'); ?>
            <strong>*</strong></label>
        <div class="col-sm-9">
            <input type="password" name="pass1" id="password" class="form-control" autocomplete="off"
                   required="required"/>
        </div>
    </div>

    <div class="form-group row rtcl-password-fields" style="display: none">
        <label for="password_confirm"
               class="col-sm-3 control-label"><?php _e('Confirm Password', 'classified-listing'); ?>
            <strong>*</strong></label>
        <div class="col-sm-9">
            <input type="password" name="pass2" id="password_confirm" class="form-control" autocomplete="off"
                   data-rule-equalTo="#password" required/>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-phone" class="col-sm-3 control-label"><?php _e('Phone', 'classified-listing'); ?></label>
        <div class="col-sm-9">
            <input type="text" name="phone" id="rtcl-phone" value="<?php echo esc_attr($phone); ?>"
                   class="form-control"/>
        </div>
    </div>
    <div class="form-group row">
        <label for="rtcl-last-name"
               class="col-sm-3 control-label"><?php esc_html_e('Whatsapp phone', 'classified-listing'); ?></label>
        <div class="col-sm-9">
            <input type="text" name="whatsapp_number" id="rtcl-whatsapp-phone"
                   value="<?php echo esc_attr($whatsapp_number); ?>"
                   class="form-control"/>
        </div>
    </div>
    <div class="form-group row">
        <label for="rtcl-website" class="col-sm-3 control-label"><?php _e('Website', 'classified-listing'); ?></label>
        <div class="col-sm-9">
            <input type="url" name="website" id="rtcl-website" value="<?php echo esc_attr($website); ?>"
                   class="form-control"/>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-3 control-label"><?php _e('Location', 'classified-listing'); ?></label>
        <div class="col-sm-9">
            <div class="form-group" id="rtcl-location-row">
                <label for='rtcl-location'><?php echo esc_html($state_text); ?><span
                            class="require-star">*</span></label>
                <select id="rtcl-location" name="location"
                        class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                    <option value="">--<?php _e('Select state', 'classified-listing') ?>--</option>
                    <?php
                    $locations = Functions::get_one_level_locations();
                    if (!empty($locations)) {
                        foreach ($locations as $location) {
                            $slt = '';
                            if (in_array($location->term_id, $user_locations)) {
                                $location_id = $location->term_id;
                                $slt = " selected";
                            }
                            echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <?php
            $sub_locations = array();
            if ($location_id) {
                $sub_locations = Functions::get_one_level_locations($location_id);
            }
            ?>
            <div class="form-group<?php echo empty($sub_locations) ? ' rtcl-hide' : ''; ?>"
                 id="sub-location-row">
                <label for='rtcl-sub-location'><?php echo esc_html($city_text); ?><span
                            class="require-star">*</span></label>
                <select id="rtcl-sub-location" name="sub_location"
                        class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                    <option value="">--<?php _e('Select location', 'classified-listing') ?>--</option>
                    <?php
                    if (!empty($sub_locations)) {
                        foreach ($sub_locations as $location) {
                            $slt = '';
                            if (in_array($location->term_id, $user_locations)) {
                                $sub_location_id = $location->term_id;
                                $slt = " selected";
                            }
                            echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <?php
            $sub_sub_locations = array();
            if ($sub_location_id) {
                $sub_sub_locations = Functions::get_one_level_locations($sub_location_id);
            }
            ?>
            <div class="form-group<?php echo empty($sub_sub_locations) ? ' rtcl-hide' : ''; ?>"
                 id="sub-sub-location-row">
                <label for='rtcl-sub-sub-location'><?php echo esc_html($town_text); ?>
                    <span class="require-star">*</span></label>
                <select id="rtcl-sub-sub-location" name="sub_sub_location"
                        class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                    <option value="">--<?php _e('Select location', 'classified-listing') ?>--</option>
                    <?php
                    if (!empty($sub_sub_locations)) {
                        foreach ($sub_sub_locations as $location) {
                            $slt = '';
                            if (in_array($location->term_id, $user_locations)) {
                                $slt = " selected";
                            }
                            echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="rtcl-zipcode"><?php _e("Zip Code", "classified-listing") ?></label>
                <input type="text" name="zipcode" value="<?php echo esc_attr($zipcode); ?>"
                       class="rtcl-map-field form-control" id="rtcl-zipcode"/>
            </div>
            <div class="form-group">
                <label for="rtcl-address"><?php _e("Address", "classified-listing") ?></label>
                <textarea name="address" rows="2" class="rtcl-map-field form-control"
                          id="rtcl-address"><?php echo esc_textarea($address); ?></textarea>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="rtcl-map" class="col-sm-3 control-label"><?php _e('Map', 'classified-listing'); ?></label>
        <div class="col-sm-9">
            <div class="rtcl-map-wrap">
                <div class="rtcl-map" data-type="input">
                    <div class="marker" data-latitude="<?php echo esc_attr($latitude); ?>"
                         data-longitude="<?php echo esc_attr($longitude); ?>"
                         data-address="<?php echo esc_attr($address); ?>"><?php echo esc_html($address); ?></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Map Hidden field-->
    <input type="hidden" name="latitude" value="<?php echo esc_attr($latitude); ?>" id="rtcl-latitude"/>
    <input type="hidden" name="longitude" value="<?php echo esc_attr($longitude); ?>" id="rtcl-longitude"/>
    <?php do_action('rtcl_edit_account_form'); ?>

    <?php wp_nonce_field('rtcl_update_user_account', 'rtcl_user_account_nonce'); ?>

    <div class="form-group row">
        <div class="col-sm-offset-3 col-sm-9">
            <input type="submit" name="submit" class="btn btn-primary"
                   value="<?php _e('Update Account', 'classified-listing'); ?>"/>
        </div>
    </div>

    <div class="rtcl-response"></div>

    <?php do_action('rtcl_edit_account_form_end'); ?>
</form>

<?php do_action('rtcl_after_edit_account_form'); ?>
