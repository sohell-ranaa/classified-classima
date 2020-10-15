<?php
/**
 * @author     RadiusTheme
 * @package    classified-listing-store/templates
 * @version    1.0.0
 */


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use RtclStore\Resources\Options;

$max_image_size = Functions::formatBytes(Functions::get_max_upload(), 0);
$allowed_image_type = implode(', ', (array) Functions::get_option_item('rtcl_misc_settings', 'image_allowed_type', array('png', 'jpeg', 'jpg')));
?>

<div class="rtcl-store-settings">

    <form id="rtcl-store-settings" class="classima-form" method="post" role="form">
        <?php do_action( 'rtcl_store_my_account_form_start', $store ); ?>
        <div class="classima-form-section classima-form-store-image">
           <div id="rtcl-store-media">
                <div class="classified-listing-form-title">
                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                    <h3>
                        <?php esc_html_e( 'Store Images', 'classima' ); ?>
                    </h3>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-12">
                        <label class="control-label left-align-label">
                            <?php esc_html_e( 'Store Banner', 'classima' ); ?></label>
                    </div>
                    <div class="col-sm-12 col-12">
                        <div class="form-group">
                            <div class="rtcl-store-media-item rtcl-store-banner-wrap">
                                <?php $bannerClass = $store && $store->get_banner_id() ? '' : ' no-banner'; ?>
                                <div class="rtcl-store-banner<?php echo esc_attr( $bannerClass ); ?>">
                                    <div class="rtcl-media-action">
                                        <span class="rtcl-icon-plus add"><?php esc_html_e( "Add Banner", 'classima' ) ?></span>
                                        <span class="rtcl-icon-trash remove"><?php esc_html_e( "Delete Banner", 'classima' ) ?></span>
                                    </div>
                                    <div class="banner"><?php $store ? $store->the_banner() : ''; ?></div>
                                </div>
                                <div class="alert alert-danger mt-2">
                                    <?php
                                    $banner_size = (array) Functions::get_option_item( 'rtcl_misc_settings', 'store_banner_size', array(
                                        'width'  => 992,
                                        'height' => 300,
                                        'crop'   => 'yes'
                                    ) );
                                    printf(
                                        esc_html__( "Recommended image size to (%dx%d)px, Maximum file size %s, Allowed image type (%s)", 'classima' ),
                                        absint( $banner_size['width'] ),
                                        absint( $banner_size['height'] ),
                                        esc_html( $max_image_size ),
                                        esc_html( $allowed_image_type )
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-12">
                        <label class="control-label left-align-label">
                            <?php esc_html_e( 'Store Logo', 'classima' ); ?></label>
                    </div>
                    <div class="col-sm-12 col-12">
                        <div class="form-group">
                            <div class="rtcl-store-media-item rtcl-store-logo-wrap">
                                <?php $logoClass = $store && $store->has_logo() ? '' : ' no-logo'; ?>
                                <div class="rtcl-store-logo<?php echo esc_attr( $logoClass ); ?>">
                                    <div class="rtcl-media-action">
                                        <span class="rtcl-icon-plus add"><?php esc_html_e( "Add Logo", 'classima' ) ?></span>
                                        <span class="rtcl-icon-trash remove"><?php esc_html_e( "Delete Logo", 'classima' ) ?></span>
                                    </div>
                                    <div class="logo">
                                        <?php
                                            if ( $store && $store->get_logo_url() ) {
                                                $store->the_logo();
                                            }
                                        ?>
                                    </div>
                                </div>
                                <div class="alert alert-danger mt-2">
                                    <?php
                                    $logo_size = Functions::get_option_item( 'rtcl_misc_settings', 'store_logo_size', array(
                                        'width'  => 200,
                                        'height' => 150,
                                        'crop'   => 'yes'
                                    ) );
                                    printf(
                                        esc_html__( "Recommended image size to (%dx%d)px, Maximum file size %s, Allowed image types %s", 'classima' ),
                                        absint( $logo_size['width'] ),
                                        absint( $logo_size['height'] ),
                                        esc_html( $max_image_size ),
                                        esc_html( $allowed_image_type )
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="classima-form-section classima-form-store-schedule">
            <div id="rtcl-store-hours">
                <div class="classified-listing-form-title">
                    <i class="fa fa-calendar-o" aria-hidden="true"></i><h3><?php esc_html_e( 'Store Schedule', 'classima' ); ?></h3>
                </div>

                <div class="row">
                    <div class="col-sm-3 col-12">
                        <label class="control-label"><?php esc_html_e( 'Opening Hours', 'classima' ); ?></label>
                    </div>
                    <div class="col-sm-9 col-12">
                        <div class="form-group">
                            <div class="oh-list-wrap">
                                <div class="form-group">
                                    <div id="oh-type-wrap">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="meta[oh_type]" id="oh-type-always-open" value="always" <?php checked( "always", $store ? $store->get_open_hour_type() : '' ) ?>>
                                            <label class="form-check-label"
                                                   for="oh-type-always-open"><?php esc_html_e( "Always open", 'classima' ) ?></label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="meta[oh_type]" id="oh-type-open-on-selected" value="selected" <?php checked( "selected", $store ? $store->get_open_hour_type() : '' ) ?>>
                                            <label class="form-check-label"
                                                   for="oh-type-open-on-selected"><?php esc_html_e( "Select Opening Hours", 'classima' ) ?></label>
                                        </div>
                                    </div>
                                </div>
                                <?php $ohClass = $store && $store->get_open_hour_type() !== 'selected' ? ' rtcl-hide' : ''; ?>
                                <div class="form-group<?php echo esc_attr($ohClass); ?>" id="oh-list">
                                    <?php
                                    $oh_hours = $store ? $store->get_open_hours() : [];
                                    $days     = Options::store_open_hour_days();
                                    foreach ( $days as $dayKey => $day ) {
                                        $idDay = "oh-" . $dayKey . "-active";
                                        ?>
                                        <div class="oh-item">
                                            <table>
                                                <tr>
                                                    <td class="oh-time-active"><input
                                                                id="<?php echo esc_attr( $idDay ); ?>"
                                                                name="meta[oh_hours][<?php echo esc_attr( $dayKey ); ?>][active]"
                                                                value="1" <?php checked( 1, isset( $oh_hours[ $dayKey ]['active'] ) ? 1 : 0 ) ?>
                                                                type="checkbox"></td>
                                                    <td class="oh-time-day"><?php echo esc_html( $day ) ?></td>
                                                    <td class="oh-time-hour">
                                                        <div class="oh-time"><input type="text"
                                                                                    value="<?php echo isset( $oh_hours[ $dayKey ]['open'] ) ? esc_attr( $oh_hours[ $dayKey ]['open'] ) : null; ?>"
                                                                                    name="meta[oh_hours][<?php echo esc_attr( $dayKey ); ?>][open]"
                                                                                    autocomplete="off"
                                                                                    class="form-control open-hour"> - <input
                                                                    value="<?php echo isset( $oh_hours[ $dayKey ]['open'] ) ? esc_attr( $oh_hours[ $dayKey ]['close'] ) : null; ?>"
                                                                    type="text"
                                                                    name="meta[oh_hours][<?php echo esc_attr( $dayKey ); ?>][close]"
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
                </div>
            </div>
        </div>

        <div class="classima-form-section classima-form-store-info">
            <div class="classified-listing-form-title">
                <i class="fa fa-folder-open" aria-hidden="true"></i><h3><?php esc_html_e( 'Store Information', 'classima' ); ?></h3>
            </div>

            <div class="row">
                <div class="col-sm-3 col-12">
                    <label class="control-label"><?php esc_html_e( 'Id', 'classima' ); ?></label>
                </div>
                <div class="col-sm-9 col-12">
                    <div class="form-group">
                        <?php
                        $id          = $store ? $store->get_slug() : '';
                        $storeIdAttr = ( $id ) ? " disabled readonly" : null; ?>
                        <input type="text" name="id" id="rtcl-store-id" value="<?php echo esc_attr( $id ); ?>" class="form-control" required<?php echo esc_attr( $storeIdAttr ); ?>/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3 col-12">
                    <label class="control-label"><?php esc_html_e( 'Name', 'classima' ); ?></label>
                </div>
                <div class="col-sm-9 col-12">
                    <div class="form-group">
                        <input type="text" name="name" id="rtcl-store-name" value="<?php echo esc_attr( $store ? $store->get_the_title() : '' ); ?>" class="form-control" required/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3 col-12">
                    <label class="control-label"><?php esc_html_e( 'Slogan', 'classima' ); ?></label>
                </div>
                <div class="col-sm-9 col-12">
                    <div class="form-group">
                        <input type="text" name="meta[slogan]" id="rtcl-slogan" value="<?php echo esc_attr( $store ? $store->get_the_slogan() : '' ); ?>" class="form-control"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3 col-12">
                    <label class="control-label"><?php esc_html_e( 'Email', 'classima' ); ?></label>
                </div>
                <div class="col-sm-9 col-12">
                    <div class="form-group">
                         <input type="text" name="meta[email]" id="rtcl-email" class="form-control" value="<?php echo esc_attr( $store ? $store->get_email() : '' ); ?>"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3 col-12">
                    <label class="control-label"><?php esc_html_e( 'Phone', 'classima' ); ?></label>
                </div>
                <div class="col-sm-9 col-12">
                    <div class="form-group">
                        <input type="text" name="meta[phone]" id="rtcl-phone" value="<?php echo esc_attr( $store ? $store->get_phone() : '' ) ?>" class="form-control"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3 col-12">
                    <label class="control-label"><?php esc_html_e( 'Website', 'classima' ); ?></label>
                </div>
                <div class="col-sm-9 col-12">
                    <div class="form-group">
                        <input type="url" name="meta[website]" id="rtcl-website" value="<?php echo esc_url( $store ? $store->get_website() : '' ); ?>" class="form-control"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3 col-12">
                    <label class="control-label"><?php esc_html_e( 'Address', 'classima' ); ?></label>
                </div>
                <div class="col-sm-9 col-12">
                    <div class="form-group">
                        <textarea class="form-control" id="rtcl-store-address" name="meta[address]"><?php echo esc_textarea( $store ? $store->get_address() : '' ) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3 col-12">
                    <label class="control-label"><?php esc_html_e( 'Description', 'classima' ); ?></label>
                </div>
                <div class="col-sm-9 col-12">
                    <div class="form-group">
                        <textarea rows="6" class="form-control" name="details" id="rtcl-store-details"><?php echo esc_textarea( $store ? $store->get_the_description() : "" ) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3 col-12">
                    <label class="control-label"><?php esc_html_e( 'Socials', 'classima' ); ?></label>
                </div>
                <div class="col-sm-9 col-12">
                    <div class="form-group">
                        <?php
                        $social_options = Options::store_social_media_options();
                        $social_media   = $store ? $store->get_social_media() : [];
                        foreach ( $social_options as $key => $social_option ) {
                            echo sprintf( '<input type="url" name="meta[social_media][%1$s]" id="rtcl-store-social-%1$s" value="%2$s" placeholder="%3$s" class="form-control mb10"/>',
                                $key,
                                esc_url( isset( $social_media[ $key ] ) ? $social_media[ $key ] : '' ),
                                $social_option
                            );
                        }
                        ?>
                    </div>
                </div>
            </div>

        </div>
        <?php do_action( 'rtcl_store_my_account_form_end', $store ); ?>
        <div class="row">
            <div class="col-sm-3 col-12"></div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <input type="submit" name="submit" class="btn rtcl-submit-btn" value="<?php esc_html_e( 'Update Store', 'classima' ); ?>" />
                </div>
            </div>
        </div>
    </form>
    <div class="rtcl-response"></div>
</div>