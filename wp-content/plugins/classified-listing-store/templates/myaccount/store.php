<?php
/**
 * @author     RadiusTheme
 * @package    classified-listing-store/templates
 * @version    1.0.0
 *
 * @var Store $store
 */


use Rtcl\Helpers\Functions;
use RtclStore\Models\Store;
use RtclStore\Resources\Options;

$max_image_size = Functions::formatBytes(Functions::get_max_upload(), 0);
$allowed_image_type = implode(', ', (array)Functions::get_option_item('rtcl_misc_settings', 'image_allowed_type', array(
    'png',
    'jpeg',
    'jpg'
)));
?>

<div class="rtcl-store-settings">

    <div id="rtcl-store-media">
        <div class="form-group">
            <label><?php esc_html_e("Store Banner", 'classified-listing-store'); ?></label>
            <div class="rtcl-store-media-item rtcl-store-banner-wrap">
                <?php $bannerClass = $store && $store->get_banner_id() ? '' : ' no-banner'; ?>
                <div class="rtcl-store-banner<?php echo esc_attr($bannerClass); ?>">
                    <div class="rtcl-media-action">
                        <span class="rtcl-icon-plus add"><?php esc_html_e("Add Banner", "classified-listing-store") ?></span>
                        <span class="rtcl-icon-trash remove"><?php esc_html_e("Delete Banner", "classified-listing-store") ?></span>
                    </div>
                    <div class="banner"><?php $store ? $store->the_banner() : null; ?></div>
                </div>
                <div class="alert alert-danger mt-2">
                    <?php
                    $banner_size = (array)Functions::get_option_item('rtcl_misc_settings', 'store_banner_size', array(
                        'width'  => 992,
                        'height' => 300,
                        'crop'   => 'yes'
                    ));
                    printf(
                        esc_html__("Recommended image size to (%dx%d)px, Maximum file size %s, Allowed image type (%s)", "classified-listing-store"),
                        absint($banner_size['width']),
                        absint($banner_size['height']),
                        esc_html($max_image_size),
                        esc_html($allowed_image_type)
                    ) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label><?php esc_html_e("Store Logo", 'classified-listing-store'); ?></label>
            <div class="rtcl-store-media-item rtcl-store-logo-wrap">
                <?php $logoClass = $store && $store->has_logo() ? '' : ' no-logo'; ?>
                <div class="rtcl-store-logo<?php echo esc_attr($logoClass); ?>">
                    <div class="rtcl-media-action">
                        <span class="rtcl-icon-plus add"><?php esc_html_e("Add Logo", "classified-listing-store") ?></span>
                        <span class="rtcl-icon-trash remove"><?php esc_html_e("Delete Logo", "classified-listing-store") ?></span>
                    </div>
                    <div class="logo"><?php $store ? $store->the_logo() : ''; ?></div>
                </div>
                <div class="alert alert-danger mt-2">
                    <?php
                    $logo_size = Functions::get_option_item('rtcl_misc_settings', 'store_logo_size', array(
                        'width'  => 200,
                        'height' => 150,
                        'crop'   => 'yes'
                    ));
                    printf(
                        esc_html__("Recommended image size to (%dx%d)px, Maximum file size %s, Allowed image types %s", "classified-listing-store"),
                        absint($logo_size['width']),
                        absint($logo_size['height']),
                        esc_html($max_image_size),
                        esc_html($allowed_image_type)
                    ) ?>
                </div>
            </div>
        </div>
    </div>
    <form id="rtcl-store-settings" class="mt-4 form form-horizontal" method="post" role="form">
        <?php do_action('rtcl_store_my_account_form_start', $store); ?>
        <div id="rtcl-store-hours">
            <div class="form-group">
                <label><?php esc_html_e("Opening hours", "classified-listing-store") ?></label>
                <div class="oh-list-wrap">
                    <div class="form-group">
                        <div id="oh-type-wrap">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="meta[oh_type]"
                                       id="oh-type-open-on-selected"
                                       value="selected" <?php checked("selected", $store ? $store->get_open_hour_type() : '') ?>>
                                <label class="form-check-label"
                                       for="oh-type-open-on-selected"><?php esc_html_e("Open on selected hours", "classified-listing-store") ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="meta[oh_type]"
                                       id="oh-type-always-open"
                                       value="always" <?php checked("always", $store ? $store->get_open_hour_type() : '') ?>>
                                <label class="form-check-label"
                                       for="oh-type-always-open"><?php esc_html_e("Always open", "classified-listing-store") ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group<?php echo esc_attr($store && $store->get_open_hour_type() !== 'selected' ? ' rtcl-hide' : ''); ?>"
                         id="oh-list">
                        <?php
                        $oh_hours = $store ? $store->get_open_hours() : [];
                        $days = Options::store_open_hour_days();
                        foreach ($days as $dayKey => $day) {
                            $idDay = "oh-" . $dayKey . "-active";
                            ?>
                            <div class="oh-item">
                                <table>
                                    <tr>
                                        <td class="oh-time-active"><input
                                                    id="<?php echo esc_attr($idDay); ?>"
                                                    name="meta[oh_hours][<?php echo esc_attr($dayKey); ?>][active]"
                                                    value="1" <?php checked(1, isset($oh_hours[$dayKey]['active']) ? 1 : 0) ?>
                                                    type="checkbox"></td>
                                        <td class="oh-time-day"><?php echo esc_html($day) ?></td>
                                        <td class="oh-time-hour">
                                            <div class="oh-time"><input type="text"
                                                                        value="<?php echo isset($oh_hours[$dayKey]['open']) ? esc_attr($oh_hours[$dayKey]['open']) : null; ?>"
                                                                        name="meta[oh_hours][<?php echo esc_attr($dayKey); ?>][open]"
                                                                        autocomplete="off"
                                                                        class="form-control open-hour"> - <input
                                                        value="<?php echo isset($oh_hours[$dayKey]['open']) ? esc_attr($oh_hours[$dayKey]['close']) : null; ?>"
                                                        type="text"
                                                        name="meta[oh_hours][<?php echo esc_attr($dayKey); ?>][close]"
                                                        autocomplete="off"
                                                        class="form-control close-hour"></div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="rtcl-store-name"
                   class="col-sm-3 control-label"><?php esc_html_e('Store Name', 'classified-listing-store'); ?></label>
            <div class="col-sm-9">
                <input type="text" name="name" id="rtcl-store-name"
                       value="<?php echo esc_attr($store ? $store->get_the_title() : ''); ?>" class="form-control"
                       required/>
            </div>
        </div>

        <div class="form-group row">
            <label for="rtcl-store-id"
                   class="col-sm-3 control-label"><?php esc_html_e('Store slug / URL', 'classified-listing-store'); ?></label>
            <div class="col-sm-9">
                <?php
                $id = $store ? $store->get_slug() : '';
                $storeIdAttr = ($id) ? " disabled readonly" : null; ?>
                <input type="text" name="id" id="rtcl-store-id"
                       value="<?php echo esc_attr($id); ?>" class="form-control"
                       required<?php echo esc_attr($storeIdAttr); ?>/>
                <span class="help-block"><?php esc_html_e('This should be unique and you can\'t able to change in future. This will be your store url.', 'classified-listing-store'); ?></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="rtcl-slogan"
                   class="col-sm-3 control-label"><?php esc_html_e('Slogan', 'classified-listing-store'); ?></label>
            <div class="col-sm-9">
                <input type="text" name="meta[slogan]" id="rtcl-slogan"
                       value="<?php echo esc_attr($store ? $store->get_the_slogan() : ''); ?>"
                       class="form-control"/>
            </div>
        </div>

        <div class="form-group row">
            <label for="rtcl-email"
                   class="col-sm-3 control-label"><?php esc_html_e('Store E-mail Address', 'classified-listing-store'); ?></label>
            <div class="col-sm-9">
                <input type="text" name="meta[email]" id="rtcl-email" class="form-control"
                       value="<?php echo esc_attr($store ? $store->get_email() : ''); ?>"/>
            </div>
        </div>

        <div class="form-group row">
            <label for="rtcl-phone"
                   class="col-sm-3 control-label"><?php esc_html_e('Store Phone', 'classified-listing-store'); ?></label>
            <div class="col-sm-9">
                <input type="text" name="meta[phone]" id="rtcl-phone"
                       value="<?php echo esc_attr($store ? $store->get_phone() : '') ?>"
                       class="form-control"/>
            </div>
        </div>
        <div class="form-group row">
            <label for="rtcl-website"
                   class="col-sm-3 control-label"><?php esc_html_e('Store Website', 'classified-listing-store'); ?></label>
            <div class="col-sm-9">
                <input type="url" name="meta[website]" id="rtcl-website"
                       value="<?php echo esc_url($store ? $store->get_website() : ''); ?>"
                       class="form-control"/>
            </div>
        </div>
        <div class="form-group row">
            <label for="rtcl-store-address"
                   class="col-sm-3 control-label"><?php esc_html_e('Store Address', 'classified-listing-store'); ?></label>
            <div class="col-sm-9">
                <textarea class="form-control" id="rtcl-store-address"
                          name="meta[address]"><?php echo esc_textarea($store ? $store->get_address() : '') ?></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="rtcl-store-details"
                   class="col-sm-3 control-label"><?php esc_html_e('Store Details', 'classified-listing-store'); ?></label>
            <div class="col-sm-9">
                <textarea rows="6" class="form-control"
                          name="details"
                          id="rtcl-store-details"><?php echo esc_textarea($store ? $store->get_the_description() : "") ?></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="rtcl-social"
                   class="col-sm-3 control-label"><?php esc_html_e('Store Media', 'classified-listing-store'); ?></label>
            <div class="col-sm-9 rtcl-social-wrap">
                <?php
                $social_options = Options::store_social_media_options();
                $social_media = $store ? $store->get_social_media() : [];
                foreach ($social_options as $key => $social_option) {
                    echo sprintf('<input type="url" name="meta[social_media][%1$s]" id="rtcl-store-social-%1$s" value="%2$s" placeholder="%3$s" class="form-control"/>',
                        $key,
                        esc_url(isset($social_media[$key]) ? $social_media[$key] : ''),
                        $social_option
                    );
                }
                ?>

            </div>
        </div>
        <?php do_action('rtcl_store_my_account_form_end', $store); ?>
        <div class="form-group row">
            <div class="col-sm-offset-3 col-sm-9">
                <input type="submit" name="submit" class="btn btn-primary"
                       value="<?php esc_html_e('Update Store', 'classified-listing-store'); ?>"/>
            </div>
        </div>
    </form>
    <div class="rtcl-response"></div>
</div>