<?php
/**
 * @var array $instance
 */
?>
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'classified-listing'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
           name="<?php echo $this->get_field_name('title'); ?>" type="text"
           value="<?php echo esc_attr($instance['title']); ?>">
</p>

<p>
    <label for="<?php echo $this->get_field_id('location'); ?>"><?php _e('Filter by Location',
            'classified-listing'); ?></label>
    <input class="rt-autocomplete" id="<?php echo $this->get_field_id('location'); ?>"
           name="<?php echo $this->get_field_name('location'); ?>" type="hidden"
           value="<?php echo esc_attr($instance['location']); ?>">
    <?php
    wp_dropdown_categories(array(
        'show_option_none'  => '-- ' . __('Select a Location', 'classified-listing') . ' --',
        'taxonomy'          => rtcl()->location,
        'name'              => $this->get_field_name('location'),
        'class'             => 'widefat',
        'orderby'           => 'name',
        'selected'          => (int)$instance['location'],
        'option_none_value' => '',
        'hierarchical'      => true,
        'depth'             => 10,
        'show_count'        => false,
        'hide_empty'        => false,
    ));
    ?>
</p>

<p>
    <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Filter by Category',
            'classified-listing'); ?></label>
    <?php
    wp_dropdown_categories(array(
        'show_option_none'  => '-- ' . __('Select a Category', 'classified-listing') . ' --',
        'option_none_value' => '',
        'taxonomy'          => rtcl()->category,
        'name'              => $this->get_field_name('category'),
        'class'             => 'widefat',
        'orderby'           => 'name',
        'selected'          => (int)$instance['category'],
        'hierarchical'      => true,
        'depth'             => 10,
        'show_count'        => false,
        'hide_empty'        => false,
    ));
    ?>
</p>

<p>
    <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Filter by Type', 'classified-listing'); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id('type'); ?>"
            name="<?php echo $this->get_field_name('type'); ?>">
        <?php
        $options = array(
            'featured_only' => __('Featured only', 'classified-listing'),
            'top_only'      => __('Top Only', 'classified-listing'),
            'feature_top'   => __('Featured and Top', 'classified-listing'),
            'all'           => __('All Type', 'classified-listing')
        );

        foreach ($options as $key => $value) {
            printf('<option value="%s"%s>%s</option>', $key, selected($key, $instance['type']), $value);
        }
        ?>
    </select>
</p>

<p>
    <input <?php checked($instance['related_listings']); ?>
            id="<?php echo $this->get_field_id('related_listings'); ?>"
            name="<?php echo $this->get_field_name('related_listings'); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id('related_listings'); ?>"><?php _e('Related Listings',
            'classified-listing'); ?></label>
</p>

<p>
    <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Limit / Listing per page(pagination)', 'classified-listing'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>"
           name="<?php echo $this->get_field_name('limit'); ?>" type="text"
           value="<?php echo esc_attr($instance['limit']); ?>">
</p>
<div class="rtcl-listing-widget-general-options"
     style="<?php echo esc_attr($instance['view'] === 'map') ? "display:none" : '' ?>">
    <p>
        <label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order By',
                'classified-listing'); ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id('orderby'); ?>"
                name="<?php echo $this->get_field_name('orderby'); ?>">
            <?php
            $options = array(
                'title' => __('Title', 'classified-listing'),
                'date'  => __('Date posted', 'classified-listing'),
                'price' => __('Price', 'classified-listing'),
                'views' => __('Views count', 'classified-listing'),
                'rand'  => __('Random', 'classified-listing')
            );

            foreach ($options as $key => $value) {
                printf('<option value="%s"%s>%s</option>', $key, selected($key, $instance['orderby']), $value);
            }
            ?>
        </select>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order', 'classified-listing'); ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id('order'); ?>"
                name="<?php echo $this->get_field_name('order'); ?>">
            <?php
            $options = array(
                'asc'  => __('ASC', 'classified-listing'),
                'desc' => __('DESC', 'classified-listing')
            );

            foreach ($options as $key => $value) {
                printf('<option value="%s"%s>%s</option>', $key, selected($key, $instance['order']), $value);
            }
            ?>
        </select>
    </p>
</div>
<div class="widget-title" style="background: #fafafa; border: 1px solid #e5e5e5;">
    <h4 style="text-transform: uppercase;"><?php _e('Display Options', 'classified-listing'); ?></h4>
</div>

<p>
    <label for="<?php echo $this->get_field_id('view'); ?>"><?php _e('View', 'classified-listing'); ?></label>
    <select class="widefat rtcl-listing-widget-view-option" id="<?php echo $this->get_field_id('view'); ?>"
            name="<?php echo $this->get_field_name('view'); ?>">
        <?php
        $options = array(
            'grid'   => __('Grid', 'classified-listing'),
            'slider' => __('Slider', 'classified-listing'),
            'map'    => __('Map', 'classified-listing')
        );

        foreach ($options as $key => $value) {
            printf('<option value="%s"%s>%s</option>', $key, selected($key, $instance['view']), $value);
        }
        ?>
    </select>
</p>
<div class="rtcl-listing-widget-general-options"
     style="<?php echo esc_attr($instance['view'] === 'map') ? "display:none" : '' ?>">
    <p>
        <label for="<?php echo $this->get_field_id('columns'); ?>"><?php _e('Number of columns / Items to display at slider',
                'classified-listing'); ?></label>
        <select class="widefat" name="<?php echo $this->get_field_name('columns'); ?>"
                id="<?php echo $this->get_field_id('columns'); ?>">
            <?php for ($i = 1; $i <= 8; $i++) {
                echo sprintf('<option value="%1$d"%2$s>%1$d</option>',
                    $i,
                    $instance['columns'] === $i ? ' selected' : ''
                );
            } ?>
        </select>
    </p>
    <div class="rtcl-listing-widget-slider-options">
        <p>
            <label for="<?php echo $this->get_field_id('tab_items'); ?>"><?php _e('Number of items at Tab (Slider)',
                    'classified-listing'); ?></label>
            <select class="widefat" name="<?php echo $this->get_field_name('tab_items'); ?>"
                    id="<?php echo $this->get_field_id('tab_items'); ?>">
                <?php for ($i = 1; $i <= 8; $i++) {
                    echo sprintf('<option value="%1$d"%2$s>%1$d</option>',
                        $i,
                        $instance['tab_items'] === $i ? ' selected' : ''
                    );
                } ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('mobile_items'); ?>"><?php _e('Number of items at Mobile (Slider)',
                    'classified-listing'); ?></label>
            <select class="widefat" name="<?php echo $this->get_field_name('mobile_items'); ?>"
                    id="<?php echo $this->get_field_id('mobile_items'); ?>">
                <?php for ($i = 1; $i <= 8; $i++) {
                    echo sprintf('<option value="%1$d"%2$s>%1$d</option>',
                        $i,
                        $instance['mobile_items'] === $i ? ' selected' : ''
                    );
                } ?>
            </select>
        </p>
    </div>
    <p>
        <input <?php checked($instance['show_image']); ?> id="<?php echo $this->get_field_id('show_image'); ?>"
                                                          name="<?php echo $this->get_field_name('show_image'); ?>"
                                                          type="checkbox"/>
        <label for="<?php echo $this->get_field_id('show_image'); ?>"><?php _e('Show Image',
                'classified-listing'); ?></label>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('image_position'); ?>"><?php _e('Image Position',
                'classified-listing'); ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id('image_position'); ?>"
                name="<?php echo $this->get_field_name('image_position'); ?>">
            <?php
            $options = array(
                'top'  => __('Top', 'classified-listing'),
                'left' => __('Left', 'classified-listing')
            );

            foreach ($options as $key => $value) {
                printf('<option value="%s"%s>%s</option>', $key, selected($key, $instance['image_position']),
                    $value);
            }
            ?>
        </select>
    </p>

    <p>
        <input <?php checked($instance['show_category']); ?>
                id="<?php echo $this->get_field_id('show_category'); ?>"
                name="<?php echo $this->get_field_name('show_category'); ?>"
                type="checkbox"/>
        <label for="<?php echo $this->get_field_id('show_category'); ?>"><?php _e('Show Category',
                'classified-listing'); ?></label>
    </p>

    <p>
        <input <?php checked($instance['show_location']); ?>
                id="<?php echo $this->get_field_id('show_location'); ?>"
                name="<?php echo $this->get_field_name('show_location'); ?>" type="checkbox"/>
        <label for="<?php echo $this->get_field_id('show_location'); ?>"><?php _e('Show Location',
                'classified-listing'); ?></label>
    </p>
    <p>
        <input <?php checked($instance['show_labels']); ?>
                id="<?php echo $this->get_field_id('show_labels'); ?>"
                name="<?php echo $this->get_field_name('show_labels'); ?>" type="checkbox"/>
        <label for="<?php echo $this->get_field_id('show_labels'); ?>"><?php _e('Show Labels',
                'classified-listing'); ?></label>
    </p>

    <p>
        <input <?php checked($instance['show_price']); ?> id="<?php echo $this->get_field_id('show_price'); ?>"
                                                          name="<?php echo $this->get_field_name('show_price'); ?>"
                                                          type="checkbox"/>
        <label for="<?php echo $this->get_field_id('show_price'); ?>"><?php _e('Show Price',
                'classified-listing'); ?></label>
    </p>

    <p>
        <input <?php checked($instance['show_date']); ?> id="<?php echo $this->get_field_id('show_date'); ?>"
                                                         name="<?php echo $this->get_field_name('show_date'); ?>"
                                                         type="checkbox"/>
        <label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show Date',
                'classified-listing'); ?></label>
    </p>

    <p>
        <input <?php checked($instance['show_user']); ?> id="<?php echo $this->get_field_id('show_user'); ?>"
                                                         name="<?php echo $this->get_field_name('show_user'); ?>"
                                                         type="checkbox"/>
        <label for="<?php echo $this->get_field_id('show_user'); ?>"><?php _e('Show User',
                'classified-listing'); ?></label>
    </p>

    <p>
        <input <?php checked($instance['show_views']); ?> id="<?php echo $this->get_field_id('show_views'); ?>"
                                                          name="<?php echo $this->get_field_name('show_views'); ?>"
                                                          type="checkbox"/>
        <label for="<?php echo $this->get_field_id('show_views'); ?>"><?php _e('Show Views',
                'classified-listing'); ?></label>
    </p>
</div>