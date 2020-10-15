;(function ($) {
    $(function () {

        $(".store-email-label").on('click', function () {
            var self = $(this);
            self.parents('.store-email').find('#store-email-area').slideDown();
        });

        $(".fade-anchor .fade-anchor-text").on('click', function (e) {
            e.preventDefault();
            $('#store-details-modal').modal('show');
            return false;
        });
        $('.rtcl-promotions-heading').on('click', function () {
            var _self = $(this),
                id = _self.attr('id');
            if ($(this).hasClass('active')) {
                if ('rtcl-regular-promotions-heading' === id) {
                    $("#rtcl-regular-promotions-heading").removeClass('active');
                    $("#rtcl-checkout-form").slideUp();
                    $("#rtcl-membership-promotions-heading").addClass('active');
                    $(".rtcl-membership-promotions-form-wrap").slideDown();
                } else {
                    $("#rtcl-membership-promotions-heading").removeClass('active');
                    $(".rtcl-membership-promotions-form-wrap").slideUp();
                    $("#rtcl-regular-promotions-heading").addClass('active');
                    $("#rtcl-checkout-form").slideDown();
                }
            } else {
                if ('rtcl-regular-promotions-heading' === id) {
                    $("#rtcl-regular-promotions-heading").addClass('active');
                    $("#rtcl-checkout-form").slideDown();
                    $("#rtcl-membership-promotions-heading").removeClass('active');
                    $(".rtcl-membership-promotions-form-wrap").slideUp();
                } else {
                    $("#rtcl-membership-promotions-heading").addClass('active');
                    $(".rtcl-membership-promotions-form-wrap").slideDown();
                    $("#rtcl-regular-promotions-heading").removeClass('active');
                    $("#rtcl-checkout-form").slideUp();
                }
            }
        });

        $(document).on('rtcl_recaptcha_loaded', function () {
            var store_contact = $("#rtcl-store-contact-g-recaptcha");
            if (store_contact.length) {
                if ($.inArray("store_contact", rtcl.recaptchas) != -1) {
                    rtcl.recaptcha_responce['store_contact'] = grecaptcha.render('rtcl-store-contact-g-recaptcha', {
                        'sitekey': rtcl.recaptcha_site_key
                    });
                    rtcl.recaptcha_store_contact = 1;
                }
            } else {
                rtcl.recaptcha_store_contact = 0;
            }
        });

        if ($.fn.validate) {
            // Membership promotion
            $("#rtcl-membership-promotions-form").validate({
                submitHandler: function (form) {
                    var $form = $(form),
                        fromData = new FormData(form);
                    fromData.append('action', 'rtcl_store_ajax_membership_promotion');
                    fromData.append('__rtcl_wpnonce', rtcl_store_public.__rtcl_wpnonce);
                    $.ajax({
                        type: "POST",
                        url: rtcl_store_public.ajaxurl,
                        data: fromData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $form.rtclBlock();
                        },
                        success: function (res) {
                            console.log(res);
                            $form.rtclUnblock();
                            if (res.success) {
                                toastr.success(res.data.message);
                                if (res.data.redirect_url) {
                                    if (res.data.redirect_utl === window.location.href) {
                                        window.location.reload(true);
                                    } else {
                                        window.location = res.data.redirect_url + '?t=' + new Date().getTime();
                                    }
                                }
                            } else {
                                toastr.error(res.data);
                            }
                        },
                        error: function (jqXHR, exception) {
                            $form.rtclUnblock();
                            toastr.error(rtcl_validator.messages.server_error);
                        }
                    });
                }
            });
            // User account form
            $("#store-email-area form").validate({
                submitHandler: function (form) {
                    var $form = $(form),
                        targetBtn = $form.find('.sc-submit'),
                        responseHolder = $form.find('.rtcl-response'),
                        msgHolder = $("<div class='alert'></div>"),
                        data = {},
                        sc_response = '';
                    if (rtcl.recaptcha_store_contact > 0) {
                        sc_response = grecaptcha.getResponse(rtcl.recaptcha_responce['store_contact']);
                        if (0 == sc_response.length) {
                            responseHolder.removeClass('text-success').addClass('text-danger').html(rtcl.recaptcha_invalid_message);
                            grecaptcha.reset(rtcl.recaptcha_responce['store_contact']);
                            return false;
                        }
                    }
                    data = {
                        action: "rtcl_send_mail_to_store_owner",
                        store_id: rtcl_store_public.store_id || 0,
                        name: $form.find("#sc-name").val(),
                        email: $form.find("#sc-email").val(),
                        phone: $form.find("#sc-phone").val() || '',
                        message: $form.find("#sc-message").val(),
                        'g-recaptcha-response': sc_response,
                        __rtcl_wpnonce: rtcl.__rtcl_wpnonce
                    };

                    $.ajax({
                        url: rtcl_store_public.ajaxurl,
                        data: data,
                        type: 'POST',
                        beforeSend: function () {
                            $form.addClass("rtcl-loading");
                            $form.find('input textarea').prop("disabled", true);
                            targetBtn.prop("disabled", true);
                            responseHolder.html('');
                            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(targetBtn);
                        },
                        success: function (response) {
                            targetBtn.prop("disabled", false).next('.rtcl-icon-spinner').remove();
                            $form.find('input textarea').prop("disabled", false);
                            $form.removeClass("rtcl-loading");
                            if (!response.error) {
                                msgHolder.removeClass('alert-danger').addClass('alert-success').html(response.message).appendTo(responseHolder);
                                $form[0].reset();
                                if ($form.parent("#store-email-area").parent().data('hide') !== 0) {
                                    setTimeout(function () {
                                        responseHolder.html('');
                                        $form.parent("#store-email-area").slideUp();
                                    }, 1000);
                                }
                            } else {
                                msgHolder.removeClass('alert-success').addClass('alert-danger').html(response.message).appendTo(responseHolder);
                            }
                            if (rtcl.recaptcha_store_contact > 0) {
                                grecaptcha.reset(rtcl.recaptcha_responce['store_contact']);
                            }
                        },
                        error: function (e) {
                            $form.find('input textarea').prop("disabled", false);
                            msgHolder.removeClass('alert-success').addClass('alert-danger').html(e.responseText).appendTo(responseHolder);
                            targetBtn.prop("disabled", false).next('.rtcl-icon-spinner').remove();
                            $form.removeClass("rtcl-loading");
                        }
                    });
                }
            });
        }
        if ($.fn.owlCarousel) {
            $('.rtcl-store-slider').each(function () {
                var $storeSlider = $(this),
                    settings = $storeSlider.data('settings');
                $storeSlider.addClass("owl-carousel").owlCarousel({
                    responsive: {
                        0: {
                            items: 2
                        },
                        200: {
                            items: 2
                        },
                        400: {
                            items: 2
                        },
                        600: {
                            items: 3
                        },
                        800: {
                            items: settings.items || 4
                        }
                    },
                    margin: 15,
                    rtl: rtcl_store_public.is_rtl ? true : false,
                    nav: true,
                    navText: ['<i class="rtcl-icon-angle-left"></i>', '<i class="rtcl-icon-angle-right"></i>'],
                });
            });
        }

        // Single store ad listing infinity scroll
        var store_ads_wrapper = $(".store-ad-listing-wrapper"), pagination;
        if (store_ads_wrapper.length) {
            var wrapper = $(".rtcl-listing-wrapper", store_ads_wrapper);
            pagination = wrapper.data('pagination') || {};
            pagination.disable = false;
            pagination.loading = false;

            $(window).on('scroll load', function () {
                infinite_scroll(wrapper);
            });
        }

        function infinite_scroll(wrapper) {
            var ajaxVisible = store_ads_wrapper.offset().top + store_ads_wrapper.outerHeight(true),
                ajaxScrollTop = $(window).scrollTop() + $(window).height();
            if (ajaxVisible <= (ajaxScrollTop) && (ajaxVisible + $(window).height()) > ajaxScrollTop) {
                if (pagination.max_num_pages > pagination.current_page && !pagination.loading && !pagination.disable) {
                    var data = {
                        action: "rtcl_store_ad_load_more",
                        current_page: pagination.current_page,
                        max_num_pages: pagination.max_num_pages,
                        found_posts: pagination.found_posts,
                        posts_per_page: pagination.posts_per_page,
                        store_id: rtcl_store_public.store_id
                    }
                    $.ajax({
                        url: rtcl_store_public.ajaxurl,
                        data: data,
                        type: 'POST',
                        beforeSend: function () {
                            pagination.loading = true;
                            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(wrapper);
                        },
                        success: function (response) {
                            wrapper.next('.rtcl-icon-spinner').remove();
                            pagination.loading = false;
                            pagination.current_page = response.current_page;
                            if (pagination.max_num_pages === response.current_page) {
                                pagination.disable = true;
                            }
                            if (response.complete && response.html) {
                                wrapper.append(response.html)
                            }
                        },
                        error: function (e) {
                            pagination.loading = false;
                            wrapper.next('.rtcl-icon-spinner').remove();
                        }
                    });
                }

            }
        }
    });
}(jQuery));