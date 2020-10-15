<?php

namespace Rtcl\Resources;


use Rtcl\Helpers\Functions;

class PricingOptions
{

    static function rtcl_pricing_option($post) {
        $description = get_post_meta($post->ID, "description", true);
        $price = esc_attr(get_post_meta($post->ID, "price", true));
        $visible = get_post_meta($post->ID, "visible", true);
        $top = get_post_meta($post->ID, "_top", true) ? 1 : 0;
        $bump_up = get_post_meta($post->ID, "_bump_up", true) ? 1 : 0;
        $featured = get_post_meta($post->ID, "featured", true) ? 1 : 0;

        wp_nonce_field(rtcl()->nonceText, rtcl()->nonceId);

        $data = array(
            'price'       => sprintf('<div class="row form-group">
                                            <label class="col-2 col-form-label"
                                                   for="rtcl-pricing-price">%s</label>
                                            <div class="col-10">
                                                <input 
                                                type="text" 
                                                id="rtcl-pricing-price" 
                                                name="price" 
                                                value="%s" 
                                                class="form-control"
                                                       required>
                                            </div>
                                        </div>',
                sprintf('%s [%s]', __("Price", 'classified-listing'),
                    Functions::get_currency_symbol('', true)),
                $price
            ),
            'visible'     => sprintf('<div class="row form-group">
                                            <label class="col-2 col-form-label" for="visible">%s</label>
                                            <div class="col-10">
                                                <input type="number" step="1" id="visible" name="visible" value="%s"
                                                       class="form-control" required>
                                                <span class="description">%s</span>
                                            </div>
                                        </div>',
                __("Validate until", "classified-listing"),
                esc_attr($visible),
                __("Number of days the pricing will be validate.", "classified-listing")
            ),
            'allowed'     => sprintf('<div class="row form-group">
                            <label class="col-2 col-form-label"
                                   for="pricing-featured">%s</label>
                            <div class="col-10">
                                <div class="form-check">
                                    <input class="form-check-input" name="featured" type="checkbox"
                                           value="1" %s id="allowed_featured">
                                    <label class="form-check-label" for="allowed_featured">%s</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="_top" type="checkbox" value="1" %s id="allowed_top">
                                    <label class="form-check-label" for="allowed_top">%s</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="_bump_up" type="checkbox" value="1" %s id="allowed_bump_up">
                                    <label class="form-check-label" for="allowed_bump_up">%s</label>
                                </div>
                            </div>
                        </div>',
                __("Allowed", 'classified-listing'),
                $featured ? ' checked' : '',
                __("Featured", 'classified-listing'),
                $top ? ' checked' : '',
                __("Top", 'classified-listing'),
                $bump_up ? ' checked' : '',
                __("Bump Up", 'classified-listing')
            ),
            'description' => sprintf('<div class="row form-group">
                                                <label class="col-2 col-form-label" for="pricing-description">%s</label>
                                                <div class="col-10">
                                                    <textarea rows="5" id="pricing-description" class="form-control"
                                                              name="description">%s</textarea>
                                                </div>
                                            </div>',
                __("Description", 'classified-listing'),
                $description
            )
        );

        $data = apply_filters('rtcl_pricing_admin_options', $data, $post);

        echo implode('', $data);
    }

}
