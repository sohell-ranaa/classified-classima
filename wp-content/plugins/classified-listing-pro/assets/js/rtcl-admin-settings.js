(function ($) {
  'use-strict';

  $(function () {
    if ($.fn.select2) {
      $('.rtcl-select2').select2();
    }

    if ($.fn.wpColorPicker) {
      $('.rtcl-color').wpColorPicker();
    }

    if ($.fn.rtFieldDependency) {
      $('[data-rt-depends]').rtFieldDependency();
    }

    $('.rtcl-setting-image-wrap').on('click', '.rtcl-add-image', function (e) {
      e.preventDefault();
      var self = $(this),
          target = self.parents('.rtcl-setting-image-wrap'),
          file_frame,
          image_data,
          json; // If an instance of file_frame already exists, then we can open it rather than creating a new instance

      if (undefined !== file_frame) {
        file_frame.open();
        return;
      } // Here, use the wp.media library to define the settings of the media uploader


      file_frame = wp.media.frames.file_frame = wp.media({
        frame: 'post',
        state: 'insert',
        multiple: false
      }); // Setup an event handler for what to do when an image has been selected

      file_frame.on('insert', function () {
        // Read the JSON data returned from the media uploader
        json = file_frame.state().get('selection').first().toJSON(); // First, make sure that we have the URL of an image to display

        if (0 > $.trim(json.url.length)) {
          return;
        }

        var imgUrl = typeof json.sizes.medium === "undefined" ? json.url : json.sizes.medium.url;
        target.find('.rtcl-setting-image-id').val(json.id);
        target.find('.image-preview-wrapper').html('<img src="' + imgUrl + '" alt="' + json.title + '" />');
      }); // Now display the actual file_frame

      file_frame.open();
    }); // Delete the image when "Remove Image" button clicked

    $('.rtcl-setting-image-wrap').on('click', '.rtcl-remove-image', function (e) {
      e.preventDefault();
      var self = $(this),
          target = self.parents('.rtcl-setting-image-wrap');

      if (confirm('Are you sure to delete?')) {
        target.find('.rtcl-setting-image-id').val('');
        target.find('.image-preview-wrapper img').attr('src', target.find('.image-preview-wrapper').data('placeholder'));
      }
    });
    var enable_verification = $("#rtcl_account_settings-user_verification");

    if (enable_verification.is(":checked")) {
      $('.rtcl_account_settings-verify_max_resend_allowed').show('slow');
    }

    enable_verification.on('change', function () {
      if ($(this).is(":checked")) {
        $('.rtcl_account_settings-verify_max_resend_allowed').show('slow');
      } else {
        $('.rtcl_account_settings-verify_max_resend_allowed').hide('slow');
      }
    });
    var enable_terms_conditions = $("#rtcl_account_settings-enable_terms_conditions");

    if (enable_terms_conditions.is(":checked")) {
      $('.rtcl_account_settings-terms_conditions').show('slow');
    }

    enable_terms_conditions.on('change', function () {
      if ($(this).is(":checked")) {
        $('.rtcl_account_settings-terms_conditions').show('slow');
      } else {
        $('.rtcl_account_settings-terms_conditions').hide('slow');
      }
    });
  });
  $(".rtcl-license-wrapper").on('click', '.rt-licensing-btn', function (e) {
    e.preventDefault();
    var self = $(this),
        parent_wrap = self.closest('td'),
        action = self.data('action'),
        type = self.hasClass('license_activate') ? 'license_activate' : 'license_deactivate';

    if (action) {
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: action,
          type: type
        },
        beforeSend: function beforeSend() {
          parent_wrap.rtclBlock();
        },
        success: function success(response) {
          parent_wrap.rtclUnblock();

          if (!response.error) {
            self.text(response.value).removeClass(type).addClass(response.type);

            if (response.type === 'license_deactivate') {
              self.removeClass('button-primary').addClass('danger');
            } else if (response.type === 'license_activate') {
              self.removeClass('danger').addClass('button-primary');
            }

            toastr.success(response.msg);
          } else if (response.msg) {
            toastr.error(response.msg);
          }

          self.blur();
        },
        error: function error(jqXHR, exception) {
          parent_wrap.rtclUnblock();

          if (jqXHR.status === 0) {
            toastr.error('', 'Not connect.\n Verify Network.');
          } else if (jqXHR.status === 404) {
            toastr.error('', 'Requested page not found. [404]');
          } else if (jqXHR.status === 500) {
            toastr.error('', 'Internal Server Error [500].');
          } else if (exception === 'parsererror') {
            toastr.error('', 'Requested JSON parse failed.');
          } else if (exception === 'timeout') {
            toastr.error('', 'Time out error.');
          } else if (exception === 'abort') {
            toastr.error('', 'Ajax request aborted.');
          } else {
            toastr.error('', 'Uncaught Error.\n' + jqXHR.responseText);
          }
        }
      });
    } else {
      toastr.error('', 'Action not defined!!');
    }
  });
})(jQuery);
