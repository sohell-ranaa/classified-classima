<?php
/**
 * @var array $instance
 */

use Rtcl\Helpers\Functions;

?>
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Title', 'classified-listing'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
           name="<?php echo $this->get_field_name('title'); ?>" type="text"
           value="<?php echo esc_attr($instance['title']); ?>">
</p>

<p>
    <input <?php checked($instance['search_by_category']); ?>
            id="<?php echo $this->get_field_id('search_by_category'); ?>"
            name="<?php echo $this->get_field_name('search_by_category'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('search_by_category'); ?>"><?php esc_html_e('Search by Category',
            'classified-listing'); ?></label>
</p>

<p>
    <input <?php checked($instance['show_icon_image_for_category']); ?>
            id="<?php echo $this->get_field_id('show_icon_image_for_category'); ?>"
            name="<?php echo $this->get_field_name('show_icon_image_for_category'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('show_icon_image_for_category'); ?>"><?php esc_html_e('Show category image / icon',
            'classified-listing'); ?></label>
</p>

<p>
    <input <?php checked($instance['search_by_location']); ?>
            id="<?php echo $this->get_field_id('search_by_location'); ?>"
            name="<?php echo $this->get_field_name('search_by_location'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('search_by_location'); ?>"><?php esc_html_e('Search by Location',
            'classified-listing'); ?></label>
</p>
<?php if (!Functions::is_ad_type_disabled()): ?>
    <p>
        <input <?php checked($instance['search_by_ad_type']); ?>
                id="<?php echo $this->get_field_id('search_by_ad_type'); ?>"
                name="<?php echo $this->get_field_name('search_by_ad_type'); ?>" type="checkbox"/>
        <label for="<?php echo $this->get_field_id('search_by_ad_type'); ?>"><?php esc_html_e('Search by ad type',
                'classified-listing'); ?></label>
    </p>
<?php endif; ?>
<p>
    <input <?php checked($instance['search_by_custom_fields']); ?>
            id="<?php echo $this->get_field_id('search_by_custom_fields'); ?>"
            name="<?php echo $this->get_field_name('search_by_custom_fields'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('search_by_custom_fields'); ?>"><?php esc_html_e('Search by Custom Fields',
            'classified-listing'); ?></label>
</p>

<p>
    <input <?php checked($instance['search_by_price']); ?>
            id="<?php echo $this->get_field_id('search_by_price'); ?>"
            name="<?php echo $this->get_field_name('search_by_price'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('search_by_price'); ?>"><?php esc_html_e('Search by Price',
            'classified-listing'); ?></label>
</p>

<p>
    <input <?php checked($instance['hide_empty']); ?>
            id="<?php echo $this->get_field_id('hide_empty'); ?>"
            name="<?php echo $this->get_field_name('hide_empty'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php esc_html_e('Hide empty Category / Location',
            'classified-listing'); ?></label>
</p>

<p>
    <input <?php checked($instance['show_count']); ?>
            id="<?php echo $this->get_field_id('show_count'); ?>"
            name="<?php echo $this->get_field_name('show_count'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('show_count'); ?>"><?php esc_html_e('Show count for Category / Location',
            'classified-listing'); ?></label>
</p>

<p>
    <input <?php checked($instance['ajax_load']); ?>
            id="<?php echo $this->get_field_id('ajax_load'); ?>"
            name="<?php echo $this->get_field_name('ajax_load'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('ajax_load'); ?>"><?php esc_html_e('Ajax load for Category / Location to increase PageSpeed.',
            'classified-listing'); ?></label>
</p>

<p>
    <input <?php checked($instance['taxonomy_reset_link']); ?>
            id="<?php echo $this->get_field_id('taxonomy_reset_link'); ?>"
            name="<?php echo $this->get_field_name('taxonomy_reset_link'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('taxonomy_reset_link'); ?>"><?php esc_html_e('All Categories / All Locations link', 'classified-listing'); ?></label>
</p>