const RtSelect = function (item, options) {
    console.log('Apple');
};
(function ($) {

    $.fn.rtSelect = function (options) {
        options = options || {};

        if (typeof options === 'object') {
            let instanceOptions = $.extend(true, {}, options);
            this.each(function () {
                new RtSelect($(this), instanceOptions);
            });
        } else {
            this.each(function () {
                let instanceOptions = $(this).data('rt-select');
                new RtSelect($(this), instanceOptions);
            });
        }
        return this;
    };
})(jQuery);