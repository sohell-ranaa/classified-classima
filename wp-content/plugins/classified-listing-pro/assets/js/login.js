;

(function ($) {
  $(document).on('click', "#rtcl-resend-verify-link", function (e) {
    e.preventDefault();

    if (confirm(rtcl.re_send_confirm_text)) {
      var login = $(this).data('login'),
          parent = $(this).parent();
      $.ajax({
        url: rtcl.ajaxurl,
        data: {
          action: 'rtcl_resend_verify',
          user_login: login,
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        },
        type: "POST",
        dataType: 'JSON',
        beforeSend: function beforeSend() {
          parent.rtclBlock();
        },
        success: function success(response) {
          parent.rtclUnblock();
          alert(response.data.message);
        },
        error: function error(e) {
          parent.rtclUnblock();
          alert("Server Error!!!");
        }
      });
    }

    return false;
  });
})(jQuery);
