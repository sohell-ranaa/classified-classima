<?php

namespace Rtcl\Widgets;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Text;
use Rtcl\Models\RtclCFGField;
use Rtcl\Resources\Options;

/**
 * Class Filter
 *
 * @package Rtcl\Widgets
 */
class Filter extends \WP_Widget
{

    protected $widget_slug;
    private $filterTypes = [
        'text',
        'textarea',
        'number',
        'checkbox',
        'select',
        'radio',
        'date'
    ];
    protected $instance;

    public function __construct() {

        $this->widget_slug = 'rtcl-widget-filter';

        parent::__construct(
            $this->widget_slug,
            __('Classified Listing Filter', 'classified-listing'),
            array(
                'classname'   => 'rtcl ' . $this->widget_slug . '-class',
                'description' => __('Classified listing Filter.', 'classified-listing')
            )
        );
    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {
        $this->instance = $instance;
        global $wp;
        $queried_object = get_queried_object();
        foreach ([rtcl()->location, rtcl()->category] as $taxonomy) {
            if (is_a($queried_object, \WP_Term::class) && $queried_object->taxonomy === $taxonomy) {
                $this->instance['current_taxonomy'][$taxonomy] = $queried_object;
            } else {
                $q_term = '';
                if (isset($wp->query_vars[$taxonomy])) {
                    $q_term = explode('/', $wp->query_vars[$taxonomy]);
                    $q_term = end($q_term);
                }
                $this->instance['current_taxonomy'][$taxonomy] = $q_term ? get_term_by('slug', $q_term, $taxonomy) : '';
            }
        }

        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $data = array(
            'category_filter'     => $this->get_category_filter(),
            'location_filter'     => $this->get_location_filter(),
            'ad_type_filter'      => $this->get_ad_type_filter(),
            'price_filter'        => $this->get_price_filter(),
            'custom_field_filter' => $this->get_custom_field_filter(),
            'object'              => $this
        );
        Functions::get_template("widgets/filter", $data);

        echo $args['after_widget'];
    }

    public function get_instance() {
        return $this->instance;
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update($new_instance, $old_instance) {

        $instance = $old_instance;

        $instance['title'] = !empty($new_instance['title']) ? strip_tags($new_instance['title']) : '';
        $instance['search_by_category'] = isset($new_instance['search_by_category']) ? 1 : 0;
        $instance['show_icon_image_for_category'] = isset($new_instance['show_icon_image_for_category']) ? 1 : 0;
        $instance['search_by_location'] = isset($new_instance['search_by_location']) ? 1 : 0;
        $instance['search_by_ad_type'] = isset($new_instance['search_by_ad_type']) ? 1 : 0;
        $instance['search_by_custom_fields'] = isset($new_instance['search_by_custom_fields']) ? 1 : 0;
        $instance['search_by_price'] = isset($new_instance['search_by_price']) ? 1 : 0;
        $instance['hide_empty'] = isset($new_instance['hide_empty']) ? 1 : 0;
        $instance['show_count'] = isset($new_instance['show_count']) ? 1 : 0;
        $instance['ajax_load'] = isset($new_instance['ajax_load']) ? 1 : 0;
        $instance['taxonomy_reset_link'] = isset($new_instance['taxonomy_reset_link']) ? 1 : 0;

        return $instance;

    }

    /**
     * @param array $instance
     *
     * @return string|void
     */
    public function form($instance) {

        // Define the array of defaults
        $defaults = array(
            'title'                        => __('Filter', 'classified-listing'),
            'search_by_category'           => 1,
            'show_icon_image_for_category' => 1,
            'search_by_location'           => 1,
            'search_by_ad_type'            => 1,
            'search_by_custom_fields'      => 1,
            'search_by_price'              => 1,
            'hide_empty'                   => 0,
            'show_count'                   => 1,
            'ajax_load'                    => 1,
            'taxonomy_reset_link'          => 1,
        );

        // Parse incoming $instance into an array and merge it with $defaults
        $instance = wp_parse_args(
            (array)$instance,
            $defaults
        );

        // Display the admin form
        include(RTCL_PATH . "views/widgets/filter.php");

    }

    public function get_category_filter() {
        if (!empty($this->instance['search_by_category'])) {
            $args = [
                'taxonomy' => rtcl()->category,
                'parent'   => 0,
                'instance' => $this->instance
            ];
            return sprintf('<div class="rtcl-category-filter ui-accordion-item is-open">
					                <a class="ui-accordion-title">
					                    <span>%s</span>
					                    <span class="ui-accordion-icon rtcl-icon rtcl-icon-anchor"></span>
					                </a>
					                <div class="ui-accordion-content%s"%s>%s</div>
					            </div>',
                apply_filters('rtcl_widget_filter_category_title', __("Category", "classified-listing")),
                !empty($args['instance']['ajax_load']) ? ' rtcl-ajax-load' : '',
                !empty($args['instance']['ajax_load']) ? sprintf(' data-settings="%s"', htmlspecialchars(wp_json_encode($args))) : '',
                empty($args['instance']['ajax_load']) ? Functions::get_sub_terms_filter_html($args) : ""
            );
        }
    }

    public function get_location_filter() {

        if (!empty($this->instance['search_by_location'])) {
            $args = array(
                'taxonomy' => rtcl()->location,
                'parent'   => 0,
                'instance' => $this->instance
            );

            return sprintf('<div class="rtcl-location-filter ui-accordion-item is-open">
					                <a class="ui-accordion-title">
					                    <span>%s</span>
					                    <span class="ui-accordion-icon rtcl-icon rtcl-icon-anchor"></span>
					                </a>
					                <div class="ui-accordion-content%s"%s>%s</div>
					            </div>',
                apply_filters('rtcl_widget_filter_location_title', __("Location", "classified-listing")),
                !empty($args['instance']['ajax_load']) ? ' rtcl-ajax-load' : '',
                !empty($args['instance']['ajax_load']) ? sprintf(' data-settings="%s"', htmlspecialchars(wp_json_encode($args))) : '',
                empty($args['instance']['ajax_load']) ? Functions::get_sub_terms_filter_html($args) : ""
            );
        }
    }

    /**
     * @return string
     */
    public function get_ad_type_filter() {
        if (!empty($this->instance['search_by_ad_type']) && !Functions::is_ad_type_disabled()) {
            $filters = !empty($_GET['filters']) ? $_GET['filters'] : array();
            $ad_type = !empty($filters['ad_type']) ? esc_attr($filters['ad_type']) : null;
            $field_html = "<ul class='ui-link-tree is-collapsed'>";
            $ad_types = Functions::get_listing_types();
            if (!empty($ad_types)) {
                foreach ($ad_types as $key => $option) {
                    $checked = ($ad_type == $key) ? " checked " : '';
                    $field_html .= "<li class='ui-link-tree-item ad-type-{$key}'>";
                    $field_html .= "<input id='filters-ad-type-values-{$key}' name='filters[ad_type]' {$checked} value='{$key}' type='radio' class='ui-checkbox filter-submit-trigger'>";
                    $field_html .= "<a href='#' class='filter-submit-trigger'>" . Text::string_translation($option) . "</a>";
                    $field_html .= "</li>";
                }
            }
            $field_html .= '<li class="is-opener"><span class="rtcl-more"><i class="rtcl-icon rtcl-icon-plus-circled"></i><span class="text">' . __("Show More",
                    "classified-listing") . '</span></span></li>';
            $field_html .= "</ul>";

            return sprintf('<div class="rtcl-ad-type-filter ui-accordion-item is-open">
									                <a class="ui-accordion-title">
									                    <span>%s</span>
									                    <span class="ui-accordion-icon rtcl-icon rtcl-icon-anchor"></span>
									                </a>
									                <div class="ui-accordion-content">%s</div>
									            </div>',
                apply_filters('rtcl_widget_filter_ad_type_title', __("Type", "classified-listing")),
                $field_html
            );
        }
    }

    /**
     * @return string
     */
    public function get_price_filter() {
        if (!empty($this->instance['search_by_price'])) {
            $filters = !empty($_GET['filters']) ? $_GET['filters'] : array();
            $fMinValue = !empty($filters['price']['min']) ? esc_attr($filters['price']['min']) : null;
            $fMaxValue = !empty($filters['price']['max']) ? esc_attr($filters['price']['max']) : null;
            $field_html = sprintf('<div class="form-group">
							            <div class="row">
							                <div class="col-md-6 col-6">
							                    <input type="number" name="filters[price][min]" class="form-control" placeholder="%s" value="%s">
							                </div>
							                <div class="col-md-6 col-6">
							                    <input type="number" name="filters[price][max]" class="form-control" placeholder="%s" value="%s">
							                </div>
							                <div class="col-md-12">
							                	<div class="ui-buttons has-expanded"><button class="btn btn-primary">%s</button></div>
											</div>
							            </div>
							        </div>',
                __('min', 'classified-listing'),
                $fMinValue,
                __('max', 'classified-listing'),
                $fMaxValue,
                __("Apply filters", 'classified-listing')
            );

            return sprintf('<div class="rtcl-price-filter ui-accordion-item is-open">
									                <a class="ui-accordion-title">
									                    <span>%s</span>
									                    <span class="ui-accordion-icon rtcl-icon rtcl-icon-anchor"></span>
									                </a>
									                <div class="ui-accordion-content">%s</div>
									            </div>',
                apply_filters('rtcl_widget_filter_price_title', __("Price Range", "classified-listing")),
                $field_html
            );
        }
    }

    /**
     * @return string
     */
    public function get_custom_field_filter() {
        if (!empty($this->instance['search_by_custom_fields'])) {
            $html = '';
            $current_term = get_queried_object();
            if (is_a($current_term, \WP_Term::class) && rtcl()->category === $current_term->taxonomy) {
                $filters = !empty($_GET['filters']) ? $_GET['filters'] : array();
                $c_ids = Functions::get_custom_field_ids($current_term->term_id);
                if (!empty($c_ids)) {
                    $i = 1;
                    foreach ($c_ids as $c_id) {
                        $field = new RtclCFGField($c_id);
                        if (in_array($field->getType(), $this->filterTypes) && $field->isSearchable()) {
                            $field_html = $isOpen = null;
                            $metaKey = $field->getMetaKey();
                            if ($field->getType() == "number") {
                                $fMinValue = !empty($filters[$metaKey]['min']) ? esc_attr($filters[$metaKey]['min']) : null;
                                $fMaxValue = !empty($filters[$metaKey]['max']) ? esc_attr($filters[$metaKey]['max']) : null;
                                $isOpen = $fMinValue || $fMaxValue ? ' is-open' : null;
                                $field_html .= sprintf('<div class="form-group row">
                                                                    <div class="col-md-6">
                                                                        <div class="ui-field">
                                                                            <input id="filters[%1$s][min]" name="filters[%1$s][min]" type="number" value="%2$s" class="ui-input form-control" placeholder="%3$s">									
                                                                        </div>											
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="ui-field">
                                                                            <input id="filters[%1$s][max]" name="filters[%1$s][max]" type="number" value="%4$s" class="ui-input form-control" placeholder="%5$s">
                                                                        </div>
                                                                    </div>
                                                                </div>',
                                    $metaKey,
                                    $fMinValue,
                                    esc_html__('Min.', 'classified-listing'),
                                    $fMaxValue,
                                    esc_html__('Max.', 'classified-listing')
                                );
                            } elseif ($field->getType() == "date") {
                                $value = !empty($filters[$metaKey]) ? esc_attr($filters[$metaKey]) : null;
                                $isOpen = $value ? ' is-open' : null;
                                $date_type = $field->getDateType();
                                $field_html .= sprintf('<div class="form-group">
																<div class="ui-field">
																	<input id="filters[%1$s]" autocomplete="false" name="filters[%1$s]" type="text" value="%2$s" data-options="%4$s" class="ui-input form-control rtcl-date" placeholder="%3$s">									
																</div>	
														</div>',
                                    esc_attr($metaKey),
                                    esc_attr($value),
                                    esc_html__('Date', 'classified-listing'),
                                    htmlspecialchars(wp_json_encode($field->getDateFieldOptions(array(
                                        'singleDatePicker' => $field->getDateSearchableType() == 'single' ? true : false,
                                        'autoUpdateInput'  => false
                                    ))))
                                );
                            } elseif (in_array($field->getType(), ["text", "textarea"], true)) {
                                $values = !empty($filters[$metaKey]) ? esc_attr($filters[$metaKey]) : null;
                                $isOpen = $values ? ' is-open' : null;
                                $field_html .= sprintf('<div class="form-group">
                                                                    <input id="filters%1$s" name="filters[%1$s]" type="text" value="%2$s" class="ui-input form-control" placeholder="%3$s">
                                                                </div>',
                                    $metaKey,
                                    $values,
                                    apply_filters('rtcl_filter_custom_text_field_placeholder', sprintf(esc_html__('Search by %s', 'classified-listing'), $field->getLabel()), $field)
                                );
                            } else {
                                $values = !empty($filters[$metaKey]) ? $filters[$metaKey] : array();
                                $isOpen = count($values) ? ' is-open' : null;
                                $options = $field->getOptions();
                                if (!empty($options['choices'])) {
                                    $field_html .= "<ul class='ui-link-tree is-collapsed'>";
                                    foreach ($options['choices'] as $key => $option) {
                                        $checked = in_array($key, $values) ? " checked " : '';
                                        $field_html .= "<li class='ui-link-tree-item {$field->getMetaKey()}-{$key}'>";
                                        $field_html .= "<input id='filters{$metaKey}-values-{$key}' name='filters[{$metaKey}][]' {$checked} value='{$key}' type='checkbox' class='ui-checkbox filter-submit-trigger'>";
                                        $field_html .= "<a href='#' class='filter-submit-trigger'>" . __($option,
                                                'classified-listing') . "</a>";
                                        $field_html .= "</li>";
                                    }
                                    $field_html .= '<li class="is-opener"><span class="rtcl-more"><i class="rtcl-icon rtcl-icon-plus-circled"></i><span class="text">' . __("Show More",
                                            "classified-listing") . '</span></span></li>';
                                    $field_html .= "</ul>";
                                }

                            }

                            $html .= apply_filters('rtcl_widget_filter_custom_field_html', sprintf('<div class="rtcl-custom-field-filter rtcl-custom-field-filter-%s ui-accordion-item %s">
									                <a class="ui-accordion-title">
									                    <span>%s</span>
									                    <span class="ui-accordion-icon rtcl-icon rtcl-icon-anchor"></span>
									                </a>
									                <div class="ui-accordion-content">%s</div>
									            </div>',
                                $field->getType(),
                                $isOpen,
                                __($field->getLabel(), "classified-listing"),
                                $field_html
                            ), $field, $c_id, $filters);
                        }

                        $i++;
                    }
                }
            }

            return $html;
        }
    }

}
