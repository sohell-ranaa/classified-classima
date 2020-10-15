Stripe.setPublishableKey(rtcl_stripe_array.stripe_publishablekey);
(function ($) {
    var $form = $('form#rtcl-checkout-form'),
        $submitBtn = $("button[type=submit]", $form),
        $stripeform = $('#rtcl-stripe-cc-form', $form),
        $stripe_cardno = $stripeform.find('#stripe-card-number'),
        $stripe_expiry = $stripeform.find('#stripe-card-expiry'),
        $stripe_cvc = $stripeform.find('#stripe-card-cvc'),
        fromHandle;

    $(function () {
        if ($.fn.validate) {
            if ($form.length) {
                $form.validate().destroy();
            }
            $form.validate({
                submitHandler: function (form) {
                    if ($('#gateway-stripe').is(':checked')) {
                        fromHandle = form;
                        if (!$('input.stripe_token', $form).length) {
                            var cardexpiry = $stripe_expiry.payment('cardExpiryVal'),
                                stripebillingname = rtcl_stripe_array.billing_name;

                            var stripedata = {
                                number: $stripe_cardno.val() || '',
                                cvc: $stripe_cvc.val() || '',
                                exp_month: cardexpiry.month || '',
                                exp_year: cardexpiry.year || '',
                                name: stripebillingname || ''
                            };
                            // Create strip token if form is valid
                            $stripeform.addClass('loading');
                            $('.alert.rtcl-response', $form).remove();
                            $submitBtn.prop('disabled', true);
                            $submitBtn.find('.rtcl-icon-spinner').remove();
                            $submitBtn.append("<span class='rtcl-icon-spinner animate-spin'></span>");
                            $('.stripe_token, .payment-errors', $stripeform).remove();
                            Stripe.createToken(stripedata, stripeResponseHandler);
                        }

                        return false;
                    } else {
                        rtcl_make_checkout_request(form);
                    }
                }
            });
        }
    });

    function stripeResponseHandler(status, response) {
        $submitBtn.prop('disabled', false);
        $submitBtn.next('.rtcl-icon-spinner').remove();
        if (response.error) {
            $stripeform.removeClass('loading');
            $('.stripe_token, .payment-errors', $stripeform).remove();
            $stripeform.append('<div class="payment-errors"><div class="alert alert-danger">' + response.error.message + '</div></div>');
            return false;
        } else {
            $stripeform.append('<input type="hidden" class="stripe_token" name="stripe_token" value="' + response.id + '"/>');
            rtcl_make_checkout_request(fromHandle, stripCallback);
        }
    }

    function stripCallback() {
        $stripeform.removeClass('loading');
    }

})(jQuery);