(function ($) {
  $(document).on('change', '.rtcl-listing-widget-view-option', function () {
    var _this = $(this),
        view = _this.val(),
        target = _this.parents('.widget-content').find('.rtcl-listing-widget-general-options');

    if ("map" === view) {
      target.slideUp(250);
    } else {
      target.slideDown(250);
    }
  });
})(jQuery);
