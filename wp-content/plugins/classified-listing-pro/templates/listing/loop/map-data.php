<?php

global $listing;
$data = $listing->get_map_data('content');
?>
<div class="rtcl-search-map-lat-long hidden"
     data-id="<?php echo absint($listing->get_id()) ?>"
     data-latitude="<?php echo esc_attr($data['latitude']) ?>"
     data-longitude="<?php echo esc_attr($data['longitude']) ?>"
     data-icon="<?php echo esc_url($data['icon']) ?>"></div>
