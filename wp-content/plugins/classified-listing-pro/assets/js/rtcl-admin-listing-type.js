(function ($) {
  $(document).on('click', '.listing-type .action span.edit', function () {
    var li = $(this).parents('li');
    li.find('.edit-action').slideToggle();
  });
  $("#input-new-type-form").on('submit', function (e) {
    e.preventDefault();
    var self = $(this),
        form = $(self),
        button = $("#rtcl-add-btn", form),
        type_field = $("#add-input-type", form),
        type = type_field.val();
    form.find('.alert').remove();

    if (type) {
      var data = {
        action: 'rtcl_ajax_add_listing_type',
        type: type
      };
      data[rtcl.nonceId] = rtcl.nonce;
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: "POST",
        beforeSend: function beforeSend() {
          button.addClass('spinner rtcl-loading').prop('disabled', true);
        },
        success: function success(res) {
          if (res.success) {
            type_field.val('');
            renderType(res.data);
          }

          var alert_type = res.success ? 'alert-success' : 'alert-danger',
              alert = $('<div class="col-12 alert alert-dismissible fade show" role="alert" />').append(res.message);
          alert.addClass(alert_type);
          form.append(alert);
          button.removeClass('spinner rtcl-loading').prop('disabled', false);
        },
        error: function error(e) {
          $('<div class="col-12 alert alert-danger alert-dismissible fade show" role="alert" />').append('Server Error !!!').appendTo(form);
          button.removeClass('spinner rtcl-loading').prop('disabled', false);
        }
      });
    } else {
      $('<div class="alert alert-danger alert-dismissible fade show" role="alert" />').append('Please fill type.').appendTo(form);
      type_field.focus();
    }
  });
  $(document).on('submit', '.input-update-type-form', function (e) {
    e.preventDefault();
    var form = $(this),
        wrap = form.closest('li'),
        old_id = wrap.data('id') || '',
        info_id = $('.type-info-id', wrap),
        info_name = $('.type-info-name', wrap),
        button = form.find('button[type=submit]'),
        input_id = form.find('input[name=id]'),
        id = input_id.val() || '',
        input_name = form.find('input[name=name]'),
        name = input_name.val() || '';
    form.find('.alert').remove();

    if (id && name && old_id) {
      var data = {
        action: 'rtcl_ajax_update_listing_type',
        name: name,
        id: id,
        old_id: old_id
      };
      data[rtcl.nonceId] = rtcl.nonce;
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: "POST",
        beforeSend: function beforeSend() {
          button.addClass('spinner rtcl-loading').prop('disabled', true);
          form.find('.alert').remove();
        },
        success: function success(res) {
          input_id.val(res.data.id);
          input_name.val(res.data.name);

          if (res.success) {
            info_id.text(res.data.id);
            info_name.text(res.data.name);
            wrap.data('id', res.data.id);
          }

          button.removeClass('spinner rtcl-loading').prop('disabled', false);
          var alert_type = res.success ? 'alert-success' : 'alert-danger',
              alert = $('<div class="col-12 alert alert-dismissible fade show" role="alert" />').append(res.message);
          alert.addClass(alert_type);
          form.append(alert);
        },
        error: function error(e) {
          console.log(e);
          $('<div class="col-12 alert alert-danger alert-dismissible fade show" role="alert" />').append('Server Error !!!').appendTo(form);
          button.removeClass('spinner rtcl-loading').prop('disabled', false);
        }
      });
    } else {
      $('<div class="col-12 alert alert-danger alert-dismissible fade show" role="alert" />').append('Please fill id and type.').appendTo(form);
    }
  });
  $(document).on('click', '.listing-type .action span.delete:not(.disabled)', function () {
    if (confirm("Are you sure to delete this type?")) {
      var self = $(this),
          li = self.parents('li.listing-type'),
          id = li.data('id');

      if (id) {
        var data = {
          action: 'rtcl_ajax_delete_listing_type',
          id: id
        };
        data[rtcl.nonceId] = rtcl.nonce;
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: "POST",
          beforeSend: function beforeSend() {
            self.addClass('spinner rtcl-loading disabled').prop('disabled', false);
          },
          success: function success(res) {
            if (res.success) {
              li.slideUp('slow', function () {
                $(this).remove();
              });
            } else {
              self.removeClass('spinner rtcl-loading disabled').prop('disabled', false);
              alert(res.message);
            }
          },
          error: function error(e) {
            self.removeClass('spinner rtcl-loading disabled').prop('disabled', false);
            console.log(e);
          }
        });
      } else {
        alert("Type id is required.");
      }
    }
  });

  function renderType(type) {
    var li = generateTypeHtml(type),
        target = $("#rtcl-listing-type-wrap"),
        ul = $(target, "#listing-types");

    if (!ul.length) {
      ul = $("<ul id='listing-types' class='list-group' />");
      target.html(ul);
    }

    ul.append(li);
  }

  function generateTypeHtml(type) {
    var li = '<li class="list-group-item listing-type" data-id="' + type.id + '">' + '<div class="type-details d-flex">' + '<div class="type-info">' + '<div class="type-info-id">' + type.id + '</div>' + '<div class="type-info-name">' + type.name + '</div>' + '</div>' + '<div class="action ml-auto"><span class="btn btn-success btn-sm edit">Edit</span><span class="btn btn-danger btn-sm  delete">Delete</span></div>' + '</div>' + '<div class="edit-action">' + '<form class="row input-update-type-form"><div class="form-group col-6"><label>ID</label><input type="text" name="id" class="form-control" value="' + type.id + '" ></div><div class="form-group col-6"><label>Type</label><input type="text" name="name" class="form-control" value="' + type.name + '" ></div><div class="form-group col-12"><button class="btn btn-primary btn-sm w-100">Update</button></div></form>' + '</div>' + '</div>' + '</li>';
    return li;
  }
})(jQuery);
