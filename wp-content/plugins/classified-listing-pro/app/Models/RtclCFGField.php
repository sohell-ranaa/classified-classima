<?php

namespace Rtcl\Models;

use DateTime;
use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

class RtclCFGField
{

    protected $_type;
    protected $_label;
    protected $_slug;
    protected $_placeholder;
    protected $_description;
    protected $_message;
    protected $_options = array();
    protected $_required;
    protected $_searchable;
    protected $_listable;
    protected $_default_value;
    protected $_validation;
    protected $_validation_message;
    protected $_rows;
    protected $_min;
    protected $_max;
    protected $_step_size;
    protected $_target;
    protected $_nofollow;
    protected $_field_id;
    protected $_meta_key;
    protected $_date_type;
    protected $_date_format;
    protected $_date_time_format;
    protected $_date_searchable_type;

    public function __construct($field_id = 0) {
        if ($post = get_post($field_id)) {
            $this->_field_id = $post->ID;
            $this->_type = get_post_meta($post->ID, '_type', true);
            $this->_label = get_post_meta($post->ID, '_label', true);
            $this->_slug = get_post_meta($post->ID, '_slug', true);
            $this->_description = get_post_meta($post->ID, '_description', true);
            $this->_message = get_post_meta($post->ID, '_message', true);
            $this->_placeholder = get_post_meta($post->ID, '_placeholder', true);
            $this->_options = get_post_meta($post->ID, '_options', true);
            $this->_validation = get_post_meta($post->ID, '_validation', true);
            $this->_validation_message = get_post_meta($post->ID, '_validation_message', true);
            $this->_required = get_post_meta($post->ID, '_required', true);
            $this->_searchable = get_post_meta($post->ID, '_searchable', true);
            $this->_listable = get_post_meta($post->ID, '_listable', true);
            $this->_default_value = get_post_meta($post->ID, '_default_value', true);
            $this->_rows = get_post_meta($post->ID, '_rows', true);
            $this->_min = get_post_meta($post->ID, '_min', true);
            $this->_max = get_post_meta($post->ID, '_max', true);
            $this->_step_size = get_post_meta($post->ID, '_step_size', true);
            $this->_target = get_post_meta($post->ID, '_target', true);
            $this->_nofollow = get_post_meta($post->ID, '_nofollow', true);
            $this->_date_format = get_post_meta($post->ID, '_date_format', true);
            $this->_date_time_format = get_post_meta($post->ID, '_date_time_format', true);
            $this->_date_type = get_post_meta($post->ID, '_date_type', true);
            $this->_date_searchable_type = get_post_meta($post->ID, '_date_searchable_type', true);
            $types = array_keys(Options::get_custom_field_list());
            $this->_meta_key = '_field_' . $post->ID;
            if (!$this->_type && !in_array($this->_type, $types)) {
                update_post_meta($post->id, '_type', 'text');
                $this->_type = "text";
            }
        } else {
            return false;
        }
    }

    public function get_meta($meta_key, $single = true) {
        if (!$meta_key) {
            return '';
        }

        return get_post_meta($this->getFieldId(), $meta_key, $single);
    }

    public function getAdminMetaValue($meta_key, $options = array()) {
        if (!Functions::meta_exist($this->getFieldId(), $meta_key)) {
            $value = $this->getAdminDefaultValue($options);
        } else {
            $value = $this->$meta_key;
        }

        return $value;
    }

    public function getAdminDefaultValue($options) {
        $default_value = null;
        if (isset($options['default'])) {
            if ($this->getType() == 'checkbox') {
                $default_value = !empty($options['default']) && is_array($options['default']) ? $options['default'] : array();
            } else {
                $default_value = !empty($options['default']) ? trim($options['default']) : null;
            }
        }

        return $default_value;
    }

    public function getValue($post_id) {
        $value = null;
        $type = $this->getType();
        if (!Functions::meta_exist($post_id, $this->getMetaKey()) && $type != 'date') {
            $value = $this->getDefaultValue();
        } else {
            if ($type == 'checkbox') {
                $value = get_post_meta($post_id, $this->getMetaKey());
            } elseif ($type == 'date') {
                $date_type = $this->getDateType();
                if (in_array($date_type, array('date_range', 'date_time_range'))) {
                    $value = [
                        'start' => get_post_meta($post_id, $this->getDateRangeMetaKey('start'), true),
                        'end'   => get_post_meta($post_id, $this->getDateRangeMetaKey('end'), true)
                    ];
                } else {
                    $value = get_post_meta($post_id, $this->getMetaKey(), true);
                }
            } else {
                $value = get_post_meta($post_id, $this->getMetaKey(), true);
            }
        }

        return $value;
    }

    /**
     * @param int $post_id  Listing id
     *
     * @return array|mixed|string|null
     */
    public function getFormattedCustomFieldValue($post_id) {

        $value = $this->getValue($post_id);
        if ('url' == $this->getType() && filter_var($value, FILTER_VALIDATE_URL)) {
            $value = esc_url($value);
//			$nofollow = ! empty( $this->getNofollow() ) ? ' rel="nofollow"' : '';
//			$value    = sprintf( '<a href="%1$s" target="%2$s"%3$s>%1$s</a>', $value,
//				$this->getTarget(),
//				$nofollow );
        } else if (in_array($this->getType(), array('select', 'radio'))) {
            $options = $this->getOptions();
            if (!empty($options['choices']) && !empty($options['choices'][$value])) {
                $value = $options['choices'][$value];
            }
        } else if ('checkbox' == $this->getType() && is_array($value)) {
            $options = $this->getOptions();
            $items = array();
            if (!empty($options['choices'])) {
                foreach ($value as $item) {
                    if (!empty($options['choices'][$item])) {
                        $items[] = $options['choices'][$item];
                    }
                }
            }
            if (!empty($items)) {
                $value = implode(", ", $items);
            }
        } else if ('date' == $this->getType()) {
            $date_format = $this->getDateFullFormat();
            $date_type = $this->getDateType();
            if (($date_type == 'date_range' || $date_type == 'date_time_range') && is_array($value)) {
                $start = isset($value['start']) && !empty($value['start']) ? date($date_format, strtotime($value['start'])) : null;
                $end = isset($value['end']) && !empty($value['start']) ? date($date_format, strtotime($value['end'])) : null;
                $value = $end ? $start . " - " . $end : $start;
            } else {
                $value = !empty($value) ? date($date_format, strtotime($value)) : '';
            }

        } else if ('text' == $this->getType()) {
            $value = esc_html($value);
        } else if ('textarea' == $this->getType()) {
            $value = esc_html($value);
        }

        return apply_filters('rtcl_formatted_custom_field_value', $value, $this);
    }

    public function get_field_data() {
        $html = null;
        // Set right ID if existing field
        $clasess = 'postbox rtcl-cf-postbox';
        $id = $this->_type . '-' . $this->_field_id;
        if ($this->_slug) {
            $clasess = 'closed ' . $clasess;
        }

        $icon = Options::get_custom_field_list()[$this->_type]['symbol'];
        $icon = "<i class='rtcl-icon rtcl-icon-{$icon}'></i>";
        $title = !empty($this->_label) ? $this->_label : __('Untitled', 'classified-listing');
        $title = sprintf(
            '<span class="rtcl-legend-update">%s</span> <span class="description">(%s)</span>',
            stripslashes($title),
            Options::get_custom_field_list()[$this->_type]['name']
        );
        $box_id = sprintf('rtcl-custom-field-%s', $id);
        $html = sprintf(
            '<div id="%s" class="%s" data-id="%s"><div class="postbox-header"><h2 class="hndle ui-sortable-handle">%s%s</h2><div class="handle-actions hide-if-no-js"><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">%s</span><span class="toggle-indicator" aria-hidden="false"></span></button></div></div><div class="inside">%s</div></div>',
            esc_attr($box_id),
            esc_attr($clasess),
            $this->_field_id,
            $icon,
            $title,
            esc_attr__('Toggle for Meta field', 'classified-listing'),
            $this->render()
        );

        return $html;
    }

    private function render() {
        $html = null;
        $field = Options::get_custom_field_list()[$this->_type];
        $options = Options::get_custom_field_list()[$this->_type]['options'];
        if (!empty($options)) {
            foreach ($options as $optName => $option) {
                $id = $this->_type . '-' . rand();
                $html .= "<div class='rtcl-cfg-field-group'>";
                $html .= "<div class='rtcl-cfg-field-label'><label class='rtcl-cfg-label' for='{$id}'>{$option['label']}</label></div>";
                $html .= "<div class='rtcl-cfg-field'>" . $this->createField($optName, $id, $option) . "</div>";
                $html .= "</div>";
            }
        }

        $html .= '<span href="#" class="js-rtcl-field-remove rtcl-field-remove" data-message-confirm="' . __("Are you sure?",
                "classified-listing") . '"><span class="dashicons dashicons-trash"></span> Remove field</a>';

        return $html;
    }

    private function createField($optName, $id, $option = array()) {
        $html = null;
        $type = $option['type'];
        $placeholder = !empty($option['placeholder']) ? " placeholder='{$option['placeholder']}'" : null;
        $class = !empty($option['class']) ? $option['class'] : null;
        switch ($type) {
            case 'true_false':
                $html .= "<input id='{$id}' value='1' class='widefat {$class}' type='checkbox' name='rtcl[fields][{$this->_field_id}][{$optName}]'>";
                break;
            case 'checkbox':
            case 'select':
                $html .= "<div class='rtcl-select-options-wrap' data-type='{$type}'>";
                $html .= "<table class='striped rtcl-select-options-table rtcl-fields-field-value-options'>
									<thead>
										<tr>
											<th> </th>
											<th>" . __('Display text', 'classified-listing') . "</th>
											<th>" . __('Value', 'classified-listing') . "</th>
											<th>" . __('Default', 'classified-listing') . "</th>
											<th> </th>
										</tr>
									</thead>";
                $html .= "<tbody class='rtcl-fields-select-sortable'>";
                $default_name = "rtcl[fields][{$this->_field_id}][{$optName}][default]";
                $default_type = "radio";
                if ($type == 'checkbox') {
                    $default_name = "rtcl[fields][{$this->_field_id}][{$optName}][default][]";
                    $default_type = 'checkbox';
                }
                if (!empty($this->_options['choices'])) {
                    foreach ($this->_options['choices'] as $optId => $option) {
                        $id = uniqid();
                        $checked = !empty($this->_options['default']) && $this->_options['default'] == $optId ? " checked='checked'" : null;
                        if ($type == 'checkbox') {
                            $defaultValues = $this->_options['default'];
                            $checked = !empty($defaultValues) && is_array($defaultValues) && in_array($optId,
                                $defaultValues) == $optId ? " checked='checked'" : null;
                        }
                        $html .= "<tr>
												<td class='num'><span class='js-types-sort-button hndle dashicons dashicons-menu'></span></td>
												<td><input type='text' name='rtcl[fields][{$this->_field_id}][{$optName}][choices][{$id}][title]' value='{$option}' ></td>
												<td><input type='text' name='rtcl[fields][{$this->_field_id}][{$optName}][choices][{$id}][value]' value='{$optId}' ></td>
												<td class='num'><input type='{$default_type}' name='{$default_name}' {$checked} value='{$id}' ></td>
												<td class='num'><span class='rtcl-delete-option dashicons dashicons-trash'></span></td>
											</tr>";
                    }
                } else {
                    $id = uniqid();
                    $html .= "<tr>
											<td class='num'><span class='js-types-sort-button hndle dashicons dashicons-menu'></span></td>
											<td><input type='text' name='rtcl[fields][{$this->_field_id}][{$optName}][choices][{$id}][title]' value='Option title 1' ></td>
											<td><input type='text' name='rtcl[fields][{$this->_field_id}][{$optName}][choices][{$id}][value]' value='option-title-1' ></td>
											<td class='num'><input type='{$default_type}' name='{$default_name}' value='{$id}' ></td>
											<td class='num'><span class='rtcl-delete-option dashicons dashicons-trash'></span></td>
										</tr>";
                }
                $html .= "</tbody>";
                if ($type == 'select') {
                    $ndId = 'select-radio-' . time();
                    $ndChecked = empty($this->_options['default']) ? " checked='checked'" : null;
                    $html .= "<tfoot><td> </td><td> </td><td><label for='{$ndId}'>" . __("No Default",
                            "classified-listing") . "</label></td><td><input id='$ndId' type='radio' name='{$default_name}' $ndChecked value='' ></td><td> </td><tfoot>";
                }
                $html .= "</table>";
                $html .= "<a class='button rtcl-add-new-option' data-name='rtcl[fields][{$this->_field_id}][{$optName}]'>" . __("Add Option",
                        "classified-listing") . "</a>";
                $html .= "</div>";
                break;
            case 'number':
                $html .= "<input $placeholder id='{$id}' value='{$this->$optName}' class='widefat {$class}' type='number' step='any' name='rtcl[fields][{$this->_field_id}][{$optName}]'>";
                break;
            case 'textarea':
                $html .= "<textarea rows='5' $placeholder name='rtcl[fields][{$this->_field_id}][{$optName}]' class='widefat {$class}' id='{$id}'>{$this->$optName}</textarea>";
                break;
            case 'radio':
                if (!empty($option['options'])) {
                    $value = $this->getAdminMetaValue($optName, $option);
                    $html .= "<ul class='rtcl-radio-list radio horizontal'>";
                    foreach ($option['options'] as $optId => $opt) {
                        $checked = $value == $optId ? " checked='checked'" : '';
                        $html .= "<li class='rtcl-radio-item'><label for='{$id}-{$opt}'><input type='radio' id='{$id}-{$opt}' {$checked} name='rtcl[fields][{$this->_field_id}][{$optName}]' value='{$optId}'> {$opt}</label></li>";
                    }
                    $html .= "</ul>";
                }
                break;
            default:
                $html .= "<input $placeholder id='{$id}' value='{$this->$optName}' class='widefat {$class}' type='text' name='rtcl[fields][{$this->_field_id}][{$optName}]'>";
                break;

        }

        return $html;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * @return mixed
     */
    public function getLabel() {
        return $this->_label;
    }

    /**
     * @return mixed
     */
    public function getSlug() {
        return $this->_slug;
    }

    /**
     * @return mixed
     */
    public function getPlaceholder() {
        return $this->_placeholder;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->_description;
    }

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->_message;
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->_options;
    }

    /**
     * @return mixed
     */
    public function getRequired() {
        return $this->_required;
    }

    /**
     * @return mixed
     */
    public function isSearchable() {
        return $this->_searchable;
    }

    /**
     * @return mixed
     */
    public function getListable() {
        return $this->_listable;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue() {
        $default_value = null;
        if (in_array($this->getType(), array('checkbox', 'select', 'radio'))) {
            $options = $this->getOptions();
            if ($this->getType() == 'checkbox') {
                $default_value = !empty($options['default']) && is_array($options['default']) ? $options['default'] : array();
            } else {
                $default_value = !empty($options['default']) ? trim($options['default']) : null;
            }
        } else {
            $default_value = $this->_default_value;
        }

        return $default_value;
    }

    /**
     * @return mixed
     */
    public function getValidation() {
        return $this->_validation;
    }

    /**
     * @return mixed
     */
    public function getValidationMessage() {
        return $this->_validation_message;
    }

    /**
     * @return mixed
     */
    public function getMin() {
        return $this->_min;
    }

    /**
     * @return mixed
     */
    public function getMax() {
        return $this->_max;
    }

    /**
     * @return mixed
     */
    public function getStepSize() {
        return $this->_step_size;
    }

    /**
     * @return mixed
     */
    public function getFieldId() {
        return $this->_field_id;
    }

    /**
     * @return mixed
     */
    public function getRows() {
        return $this->_rows;
    }

    /**
     * @return mixed
     */
    public function getTarget() {
        return $this->_target;
    }

    /**
     * @return mixed
     */
    public function getNofollow() {
        return $this->_nofollow;
    }

    /**
     * @return mixed
     */
    public function getMetaKey() {
        return $this->_meta_key;
    }

    public function getDateRangeMetaKey($key) {
        return $this->getMetaKey() . '_' . $key;
    }

    public function getSanitizedValue($value) {
        switch ($this->getType()) {
            case 'textarea' :
                $value = esc_textarea($value);
                break;
            case 'select' :
            case 'radio'  :
            case 'text' :
                $value = sanitize_text_field($value);
                break;
            case 'checkbox' :
                $value = is_array($value) ? $value : array();
                $value = array_map('esc_attr', $value);
                break;
            case 'url' :
                $value = esc_url_raw($value);
                break;
            case 'date' :
                $value = $this->sanitize_date_field($value);
                break;
            default :
                $value = sanitize_text_field($value);
        }

        return $value;
    }

    public function saveSanitizedValue($post_id, $value) {
        $post_id = $post_id ? absint($post_id) : get_the_ID();
        $value = $this->getSanitizedValue($value);
        switch ($this->getType()) {
            case 'checkbox':
                delete_post_meta($post_id, $this->getMetaKey());
                if (!empty($value) && is_array($value)) {
                    foreach ($value as $val) {
                        if ($val) {
                            add_post_meta($post_id, $this->getMetaKey(), $val);
                        }
                    }
                }
                break;
            case 'date':
                if (is_array($value) && !empty($value)) {
                    foreach ($value as $key => $v) {
                        update_post_meta($post_id, $this->getDateRangeMetaKey($key), $v);
                    }
                } else {
                    update_post_meta($post_id, $this->getMetaKey(), $value);
                }
                break;
            default:
                update_post_meta($post_id, $this->getMetaKey(), $value);
                break;
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getDateFullFormat($type = '') {
        $date_type = $this->getDateType();
        $format = [];
        if ($date_type == 'date' || $date_type == 'date_range') {
            $format[] = $this->getDateFormat();
        }
        if ($date_type == 'date_time' || $date_type == 'date_time_range') {
            $format[] = $this->getDateFormat();
            $format[] = $this->getDateTimeFormat();
        }
        $format = array_filter($format);
        $format = implode(' ', $format);
        $format = $format ? $format : 'Y-d-m';
        if ($type == 'js') {
            $js_options = Options::get_date_js_format_placeholder();
            $find = array_keys($js_options);
            $replace = array_values($js_options);
            $format = str_replace($find, $replace, $format);
        }

        return apply_filters('rtcl_custom_field_date_full_format', $format, $this);
    }

    public function getDateSaveFullFormat() {
        $date_save_format = Functions::get_custom_field_save_date_format();
        $date_type = $this->getDateType();
        if ($date_type == 'date_time' || $date_type == 'date_time_range') {
            $date_save_format = implode(' ', $date_save_format);
        } else {
            $date_save_format = $date_save_format['date'];
        }

        return $date_save_format;
    }

    /**
     * @param       $value
     * @param array $data
     *
     * @return string|string[]
     */
    public function sanitize_date_field($value, $data = []) {
        $date_type = $this->getDateType();
        $input_date_format = $this->getDateFullFormat();
        $save_date_format = $this->getDateSaveFullFormat();

        $date_range = explode(' - ', $value);
        $formatted_date = '';
        $range = in_array($date_type, array('date_range', 'date_time_range'));
        if (!empty($data)) {
            $range = isset($data['range']) ? $data['range'] : $range;
            $input_date_format = isset($data['input_date_format']) ? $data['input_date_format'] : $input_date_format;
            $save_date_format = isset($data['save_date_format']) ? $data['save_date_format'] : $save_date_format;
        }

        try {
            if ($range) {
                $start_date = $end_date = '';
                if (isset($date_range[0]) && !empty($date_range[0])) {
                    $date = DateTime::createFromFormat($input_date_format, $date_range[0]);
                    $start_date = $date->format($save_date_format);
                }
                if (isset($date_range[1]) && !empty($date_range[1])) {
                    $date = DateTime::createFromFormat($input_date_format, $date_range[1]);
                    $end_date = $date->format($save_date_format);
                }
                $formatted_date = [
                    'start' => $start_date,
                    'end'   => $end_date
                ];

            } else {
                if (isset($date_range[0]) && !empty($date_range[0])) {
                    $date = DateTime::createFromFormat($input_date_format, $date_range[0]);
                    $formatted_date = $date->format($save_date_format);
                }
            }
        } catch (\Exception $e) {
            $formatted_date = $range ? ['start' => '', 'end' => ''] : '';
        }

        return $formatted_date;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getDateFieldOptions($data = array()) {
        $date_type = $this->getDateType();
        $js_format = $this->getDateFullFormat('js');
        $options = wp_parse_args($data, [
            'singleDatePicker' => $date_type == 'date' || $date_type == 'date_time',
            'timePicker'       => $date_type == 'date_time' || $date_type == 'date_time_range',
            'timePicker24Hour' => false !== strpos($js_format, 'HH:mm'),
            'locale'           => [
                'format' => $js_format
            ]
        ]);

        return apply_filters('rtcl_custom_field_date_options', $options, $this);
    }

    /**
     * @return mixed
     */
    public function getDateType() {
        return $this->_date_type;
    }

    /**
     * @return mixed
     */
    public function getDateFormat() {
        return $this->_date_format;
    }

    /**
     * @return mixed
     */
    public function getDateTimeFormat() {
        return $this->_date_time_format;
    }

    /**
     * @return mixed
     */
    public function getDateSearchableType() {
        return $this->_date_searchable_type;
    }


}