<?php
/**
 * Listing Form Contact
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var array   $hidden_fields
 * @var string  $state_text
 * @var string  $city_text
 * @var string  $town_text
 * @var string  $zipcode
 * @var string  $phone
 * @var string  $whatsapp_number
 * @var boolean $enable_post_for_unregister
 * @var string  $website
 * @var bool    $latitude
 * @var bool    $longitude
 * @var bool    $has_map
 * @var bool    $hide_map
 * @var string  $email
 */

use Rtcl\Helpers\Functions;

?>
<div class="rtcl-post-contact-details rtcl-post-section">
    <div class="classified-listing-form-title">
        <i class="fa fa-user" aria-hidden="true"></i><h3><?php esc_html_e( "Contact Details", 'classima' ); ?></h3>
    </div>
    <?php if ( ! in_array( 'location', $hidden_fields ) ): ?>
        <div class="row" id="rtcl-location-row">
            <div class="col-sm-3 col-12">
                <label class="control-label"><?php echo esc_html( $state_text ); ?><span> *</span></label>
            </div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <select id="rtcl-location" name="location" class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                        <option value="">--<?php esc_html_e( 'Select Location', 'classima' ) ?>--</option>
                        <?php
                        $locations = Functions::get_one_level_locations();
                        if ( ! empty( $locations ) ) {
                            foreach ( $locations as $location ) {
                                $slt = '';
                                if ( in_array( $location->term_id, $selected_locations ) ) {
                                    $location_id = $location->term_id;
                                    $slt         = " selected";
                                }
                                echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <?php
        $sub_locations = array();
        if ( $location_id ) {
            $sub_locations = Functions::get_one_level_locations( $location_id );
        }
        ?>
        <div class="row <?php echo empty( $sub_locations ) ? ' rtcl-hide' : ''; ?>" id="sub-location-row">
            <div class="col-sm-3 col-12">
                <label class="control-label"><?php echo esc_html( $city_text ); ?><span> *</span></label>
            </div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <select id="rtcl-sub-location" name="sub_location" class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                        <option value="">--<?php esc_html_e( 'Select Location', 'classima' ) ?>--</option>
                        <?php
                        if ( ! empty( $sub_locations ) ) {
                            foreach ( $sub_locations as $location ) {
                                $slt = '';
                                if ( in_array( $location->term_id, $selected_locations ) ) {
                                    $sub_location_id = $location->term_id;
                                    $slt             = " selected";
                                }
                                echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <?php
        $sub_sub_locations = array();
        if ( $sub_location_id ) {
            $sub_sub_locations = Functions::get_one_level_locations( $sub_location_id );
        }
        ?>
        <div class="row <?php echo empty( $sub_sub_locations ) ? ' rtcl-hide' : ''; ?>" id="sub-sub-location-row">
            <div class="col-sm-3 col-12">
                <label class="control-label"><?php echo esc_html( $town_text ); ?><span> *</span></label>
            </div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <select id="rtcl-sub-sub-location" name="sub_sub_location" class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                        <option value="">--<?php esc_html_e( 'Select Location', 'classima' ) ?>--</option>
                        <?php
                        if ( ! empty( $sub_sub_locations ) ) {
                            foreach ( $sub_sub_locations as $location ) {
                                $slt = '';
                                if ( in_array( $location->term_id, $selected_locations ) ) {
                                    $slt = " selected";
                                }
                                echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ( ! in_array( 'zipcode', $hidden_fields ) ): ?>
        <div class="row classima-form-zip-row">
            <div class="col-sm-3 col-12">
                <label class="control-label"><?php esc_html_e( "Zip Code", 'classima' ); ?></label>
            </div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <input type="text" name="zipcode" value="<?php echo esc_attr( $zipcode ); ?>" class="rtcl-map-field form-control" id="rtcl-zipcode"/>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ( ! in_array( 'address', $hidden_fields ) ): ?>
        <div class="row classima-form-address-row">
            <div class="col-sm-3 col-12">
                <label class="control-label"><?php esc_html_e( "Address", 'classima' ); ?></label>
            </div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <textarea name="address" rows="2" class="rtcl-map-field form-control" id="rtcl-address"><?php echo esc_textarea( $address ); ?></textarea>
                </div>
            </div>
        </div>
     <?php endif; ?>

    <?php if ( ! in_array( 'phone', $hidden_fields ) ): ?>
        <div class="row classima-form-phone-row">
            <div class="col-sm-3 col-12">
                <label class="control-label"><?php esc_html_e( "Phone", 'classima' ); ?></label>
            </div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <input type="text" class="form-control" id="rtcl-phone" name="phone" value="<?php echo esc_attr( $phone ); ?>"/>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!in_array('whatsapp_number', $hidden_fields)): ?>
        <div class="row classima-form-whatsapp-row">
            <div class="col-sm-3 col-12">
                <label class="control-label"><?php esc_html_e( "Whatsapp Number", 'classima' ); ?></label>
            </div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <input type="text" class="form-control" id="rtcl-whatsapp-number" name="whatsapp_number" value="<?php echo esc_attr( $whatsapp_number ); ?>"/>
                </div>
            </div>
        </div>
     <?php endif; ?>

    <?php if (!in_array('email', $hidden_fields) || $enable_post_for_unregister): ?>
        <div class="row classima-form-email-row">
            <div class="col-sm-3 col-12">
                <label class="control-label"><?php esc_html_e( "Email", 'classima' ); ?><?php if ( $enable_post_for_unregister ): ?><span> *</span><?php endif; ?></label></label>
            </div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <input type="email" class="form-control" id="rtcl-email" name="email" value="<?php echo esc_attr( $email ); ?>" <?php echo esc_html( $enable_post_for_unregister ? " required" : '' ); ?> />
                    <?php if ( $enable_post_for_unregister ): ?>
                        <p class="description"><?php esc_html_e( "This will be your username", 'classima' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ( $has_map ): ?>
        <div class="row rtcl-listing-map">
            <div class="col-sm-3 col-12">
                <label class="control-label"><?php esc_html_e( 'Map', 'classima' ); ?></label>
            </div>
            <div class="col-sm-9 col-12">
                <div class="form-group">
                    <div class="rtcl-map-wrap">
                        <div class="rtcl-map" data-type="input">
                            <div class="rtcl-map" data-type="input">
                                <div class="marker" data-latitude="<?php echo esc_attr( $latitude ); ?>" data-longitude="<?php echo esc_attr( $longitude ); ?>" data-address="<?php echo esc_attr( $address ); ?>"><?php echo esc_html( $address ); ?></div>
                            </div>
                        </div>
                        <div class="rtcl-form-check">
                            <input class="rtcl-form-check-input" id="rtcl-hide-map" type="checkbox" name="hide_map" value="1" <?php checked( $hide_map, 1 ); ?>>
                            <label class="rtcl-form-check-label" for="rtcl-hide-map"><?php esc_html_e( "Don't show the Map", 'classima' ) ?></label>
                        </div>
                    </div>
                    <!-- Map Hidden field-->
                    <input type="hidden" name="latitude" value="<?php echo esc_attr( $latitude ); ?>" id="rtcl-latitude"/>
                    <input type="hidden" name="longitude" value="<?php echo esc_attr( $longitude ); ?>" id="rtcl-longitude"/>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>