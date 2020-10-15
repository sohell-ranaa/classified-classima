;(function ($) {

    $(function () {
        $("#pricing-type").on('change', function () {
           var val = $(this).val();
            if(val == "membership"){
                $(".form-group.allowed").slideUp();
                $(".form-group.regular-ads").slideDown();
                $(".form-group.membership-categories").slideDown();
                $(".form-group.rtcl-membership-promotions").slideDown();
            }else{
                $(".form-group.rtcl-promotions").slideUp();
                $(".form-group.membership-categories").slideUp();
                $(".form-group.rtcl-membership-promotions").slideUp();
                $(".form-group.allowed").slideDown();
            }
        });
    });

}(jQuery));