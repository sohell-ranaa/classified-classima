(function ($) {
  // Display the media uploader when "Upload Image" button clicked in the custom taxonomy "rtcl_categories"
  $('.rtcl-categories-upload-image').on('click', function (e) {
    e.preventDefault();

    var _this = $(this),
        wrapper = _this.parents('.rtcl-term-group-wrap'),
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
      json = file_frame.state().get('selection').first().toJSON(); // console.log(json);
      // First, make sure that we have the URL of an image to display

      if (0 > $.trim(json.url.length)) {
        return;
      }

      var imgUrl = typeof json.sizes.thumbnail === "undefined" ? json.url : json.sizes.thumbnail.url;
      wrapper.find('.rtcl-category-image-id').val(json.id);
      wrapper.find('.rtcl-categories-image-wrapper').html('<img src="' + imgUrl + '" />');
    }); // Now display the actual file_frame

    file_frame.open();
  }); // Delete the image when "Remove Image" button clicked in the custom taxonomy "rtcl_categories"

  $('.rtcl-categories-remove-image').on('click', function (e) {
    e.preventDefault();

    if (confirm('Are you sure to delete?')) {
      var _this = $(this),
          wrapper = _this.parents('.rtcl-term-group-wrap');

      wrapper.find('.rtcl-category-image-id').val('');
      wrapper.find('.rtcl-categories-image-wrapper').html('');
    }
  }); // Clear the image field after the custom taxonomy "rtcl_categories" term was created.

  $(document).ajaxComplete(function (event, xhr, settings) {
    if ($("#tag-rtcl-order").length) {
      var queryStringArr = settings.data ? settings.data.split('&') : '';

      if ($.inArray('action=add-tag', queryStringArr) !== -1) {
        var xml = xhr.responseXML;
        var response = $(xml).find('term_id').text();

        if (response != "") {
          $('#tag-rtcl-order').val(0);
          $('.rtcl-category-image-id').val('');
          $('#rtcl-category-types input:checkbox').attr('checked', false);
          $('#rtcl-category-types input:checkbox[value=sell]').attr('checked', true);
          $('.rtcl-categories-image-wrapper').html('');
          $('#tag-rtcl-icon').prop('selectedIndex', 0);
        }
      }
    }
  });
  $(function () {
    if ($.fn.select2) {
      var iformat = function iformat(icon) {
        var originalOption = icon.element;
        return '<i class="rtcl-icon rtcl-icon-' + $(originalOption).data('icon') + '"></i> ' + icon.text;
      };

      $('.rtcl-select2').select2({
        dropdownAutoWidth: true,
        width: '100%'
      });
      $('.rtcl-select2-icon').select2({
        dropdownAutoWidth: true,
        width: '100%',
        templateSelection: iformat,
        templateResult: iformat,
        escapeMarkup: function escapeMarkup(text) {
          return text;
        }
      });
    }
  });
})(jQuery);
