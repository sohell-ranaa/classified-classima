<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Classima;

/*-------------------------------------
INDEX
=======================================
#. EL: Owl Nav
#. EL: Title
#. EL: Info Box
#. EL: Text With Button
#. EL: Post
#. EL: Counter
#. EL: CTA
#. EL: Pricing Box
#. EL: Accordian
#. EL: Contact
#. EL: Testimonial
#. EL: Listing
#. EL: Listing Search
#. EL: Listing Category Slider
#. EL: Listing Category Box
#. EL: Listing Location Box
#. EL: Listing Store List
-------------------------------------*/

$primary_color    = Helper::get_primary_color(); // #1aa78e
$secondary_color  = Helper::get_secondary_color(); // #fcaf01
$primary_rgb      = Helper::hex2rgb( $primary_color ); // 26, 167, 142
$secondary_rgb    = Helper::hex2rgb( $secondary_color ); // 252, 175, 1
?>

<?php /* EL: Title */ ?>
.rt-el-title.rtin-style-2 .rtin-title:after {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-title.rtin-style-3 .rtin-subtitle {
	color: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: Owl Nav */ ?>
.owl-custom-nav-area .owl-custom-nav-title:after {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.owl-custom-nav-area .owl-custom-nav .owl-prev,
.owl-custom-nav-area .owl-custom-nav .owl-next {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.owl-custom-nav-area .owl-custom-nav .owl-prev:hover,
.owl-custom-nav-area .owl-custom-nav .owl-next:hover {
	background-color: <?php echo esc_html( $secondary_color ); ?>;
}

<?php /* EL: Info Box */ ?>
.rt-el-info-box .rtin-icon i {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-info-box .rtin-icon svg {
	fill: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-info-box .rtin-title a:hover {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-title.rtin-style-4 .rtin-title:after {
    background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-info-box-2:hover .rtin-number {
    color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-info-box-2 .rtin-icon i {
    color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-info-box-2:hover .rtin-icon {
    background-color: <?php echo esc_html( $primary_color ); ?>;
    box-shadow: 0px 11px 35px 0px rgba(<?php echo esc_html( $primary_rgb ); ?>, 0.6)
}

<?php /* EL: Text With Button */ ?>
.rt-el-text-btn .rtin-item .rtin-left {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-text-btn .rtin-item .rtin-btn a:hover {
	color: <?php echo esc_html( $primary_color ); ?> !important;
}

<?php /* EL: Post */ ?>
.rt-el-post-1 .rtin-each .post-date {
	color: <?php echo esc_html( $secondary_color ); ?>;
}
.rt-el-post-1 .rtin-each .post-title a:hover {
	color: <?php echo esc_html( $primary_color ); ?>;
}

.rt-el-post-2 .rtin-each .post-title a:hover {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-post-2 .rtin-each .post-meta li a:hover {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-post-3 .rtin-each .post-date {
    color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-post-3 .rtin-each .post-meta .post-author .author-name i {
    color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-post-3 .rtin-each .post-title a:hover {
    color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-post-3 .rtin-each .post-meta .post-author .author-name a:hover {
    color: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: Counter */ ?>
.rt-el-counter .rtin-item .rtin-left .fa {
	color: <?php echo esc_html( $primary_color ); ?>;
}

.rt-el-counter .rtin-item .rtin-left svg {
	fill: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: CTA */ ?>
.rt-el-cta-1 {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-cta-1 .rtin-right a:hover {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-cta-2 .rtin-btn a {
    background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-cta-2 .rtin-btn a:hover {
    background-color: <?php echo esc_html( $secondary_color ); ?>;
}

<?php /* EL: Pricing Box */ ?>
.rt-el-pricing-box .rtin-button a {
	background: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-pricing-box .rtin-button a:hover {
	background: <?php echo esc_html( $secondary_color ); ?>;
}

.rt-el-pricing-box-2 .rtin-price {
	background: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-pricing-box-2:hover .rtin-price {
	background: <?php echo esc_html( $secondary_color ); ?>;
}
.rt-el-pricing-box-2 .rtin-button a {
	background: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-pricing-box-2 .rtin-button a:hover {
	background: <?php echo esc_html( $secondary_color ); ?>;
}

<?php /* EL: Accordian */ ?>
.rt-el-accordian .card .card-header a {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: Contact */ ?>
.rt-el-contact ul li i {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-contact ul li a:hover {
	color: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: Testimonial */ ?>
.rt-el-testimonial-1:hover {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-testimonial-2 .rtin-thumb img {
	border-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-testimonial-nav .owl-theme .owl-nav .owl-prev,
.rt-el-testimonial-nav .owl-theme .owl-nav .owl-next {
	border-color: <?php echo esc_html( $primary_color ); ?>;
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-testimonial-nav .owl-theme .owl-nav .owl-prev:hover,
.rt-el-testimonial-nav .owl-theme .owl-nav .owl-next:hover {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-testimonial-3:hover {
    background-color: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: Listing */ ?>
.rt-el-listing-isotope .rtin-btn a:hover,
.rt-el-listing-isotope .rtin-btn a.current {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: Listing Search */ ?>
.rt-el-listing-search.rtin-light {
	border-color: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: Listing Category Slider */ ?>
.rt-el-listing-cat-slider .rtin-item .rtin-icon .rtcl-icon {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-slider .rtin-item .rtin-icon svg {
	fill: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-slider .rtin-item:hover {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-slider.rtin-light .rtin-item:hover {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}

.rt-el-listing-cat-slider .owl-theme .owl-nav button.owl-prev,
.rt-el-listing-cat-slider .owl-theme .owl-nav button.owl-next {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-slider .owl-theme .owl-nav button.owl-prev:hover,
.rt-el-listing-cat-slider .owl-theme .owl-nav button.owl-next:hover {
	color: <?php echo esc_html( $secondary_color ); ?>;
}
<?php /* EL: Listing Category Box */ ?>
.rt-el-listing-cat-box .rtin-item .rtin-title-area .rtin-icon .rtcl-icon:before {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-box .rtin-item .rtin-title-area:hover .rtin-icon .rtcl-icon:before {
	border-color: <?php echo esc_html( $primary_color ); ?>;
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-box .rtin-item .rtin-sub-cats a:before {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-box .rtin-item .rtin-sub-cats a:hover {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-box-2 .rtin-item .rtin-icon .rtcl-icon:before {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-box-2 .rtin-item:hover {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}

.rt-el-listing-cat-box-3 .rtin-item:hover {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-box-3 .rtin-item .rtin-title-area .rtin-icon .rtcl-icon:before {
	color: <?php echo esc_html( $primary_color ); ?>;
}

.rt-el-listing-cat-box-4 .rtin-item .rtin-icon .rtcl-icon:before {
	color: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-box-4 .rtin-item .rtin-icon svg {
	fill: <?php echo esc_html( $primary_color ); ?>;
}
.rt-el-listing-cat-box-4 .rtin-item .rtin-title a:hover {
    color: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: Listing Store List */ ?>
.rt-el-listing-store-list .rtin-item .rtin-title a:hover {
	color: <?php echo esc_html( $primary_color ); ?>;
}

<?php /* EL: Listing Store Grid */ ?>
.rt-el-listing-store-grid .rtin-item:hover {
	background-color: <?php echo esc_html( $primary_color ); ?>;
}