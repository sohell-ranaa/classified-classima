jQuery(function ($) {
  $('.rtcl-credit-card-number').payment('formatCardNumber');
  $('.rtcl-credit-card-expiry').payment('formatCardExpiry');
  $('.rtcl-credit-card-cvc').payment('formatCardCVC');
  $(document.body).on('rtcl-credit-card-form-init', function () {
    $('.rtcl-credit-card-number').payment('formatCardNumber');
    $('.rtcl-credit-card-expiry').payment('formatCardExpiry');
    $('.rtcl-credit-card-cvc').payment('formatCardCVC');
  }).trigger('rtcl-credit-card-form-init');

  if ($.fn.validate) {
    $.validator.addMethod("RtclCardNumber", function (value, element, param) {
      value = value.replace(/\D/g, "");

      if (/[^0-9-]+/.test(value)) {
        return false;
      }

      var validTypes = 0x0000;
      if (param.mastercard) validTypes |= 0x0001;
      if (param.visa) validTypes |= 0x0002;
      if (param.amex) validTypes |= 0x0004;
      if (param.dinersclub) validTypes |= 0x0008;
      if (param.enroute) validTypes |= 0x0010;
      if (param.discover) validTypes |= 0x0020;
      if (param.jcb) validTypes |= 0x0040;
      if (param.unknown) validTypes |= 0x0080;
      if (param.all) validTypes = 0x0001 | 0x0002 | 0x0004 | 0x0008 | 0x0010 | 0x0020 | 0x0040 | 0x0080;

      if (validTypes & 0x0001 && /^(5[12345])/.test(value)) {
        //mastercard
        return value.length == 16;
      }

      if (validTypes & 0x0002 && /^(4)/.test(value)) {
        //visa
        return value.length == 16;
      }

      if (validTypes & 0x0004 && /^(3[47])/.test(value)) {
        //amex
        return value.length == 15;
      }

      if (validTypes & 0x0008 && /^(3(0[012345]|[68]))/.test(value)) {
        //dinersclub
        return value.length == 14;
      }

      if (validTypes & 0x0010 && /^(2(014|149))/.test(value)) {
        //enroute
        return value.length == 15;
      }

      if (validTypes & 0x0020 && /^(6011)/.test(value)) {
        //discover
        return value.length == 16;
      }

      if (validTypes & 0x0040 && /^(3)/.test(value)) {
        //jcb
        return value.length == 16;
      }

      if (validTypes & 0x0040 && /^(2131|1800)/.test(value)) {
        //jcb
        return value.length == 15;
      }

      if (validTypes & 0x0080) {
        //unknown
        return true;
      }

      return false;
    }, rtcl_validator.messages.cc.number);
    $.validator.addMethod("RtclCardCVC", function (value, element, param) {
      return !!$.payment.validateCardCVC($.trim(value));
    }, rtcl_validator.messages.cc.cvc);
    $.validator.addMethod("RtclCardExpiry", function (value, element, param) {
      var exp = $(element).payment('cardExpiryVal');
      return !!$.payment.validateCardExpiry(exp.month, exp.year);
    }, rtcl_validator.messages.cc.expiry);
    $.validator.addClassRules("rtcl-credit-card-number", {
      required: true,
      RtclCardNumber: {
        param: {
          all: true
        }
      }
    });
    $.validator.addClassRules("rtcl-credit-card-cvc", {
      required: true,
      RtclCardCVC: true
    });
    $.validator.addClassRules("rtcl-credit-card-expiry", {
      required: true,
      RtclCardExpiry: true
    });
  }
});
