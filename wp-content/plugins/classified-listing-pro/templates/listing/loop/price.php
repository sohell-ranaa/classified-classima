<?php

global $listing;

if ( ! $listing->can_show_price() ) {
	return;
}
?>
<div class="listing-price"><?php $listing->the_price() ?></div>
