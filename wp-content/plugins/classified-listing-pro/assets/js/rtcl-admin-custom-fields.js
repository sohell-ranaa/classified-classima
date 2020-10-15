(function ($) {
  'use strict';

  $(function () {
    rtclFieldSortable();
    rtclBindAutoCreateSlugs();
  });
  var wpcfBody = $('body');
  $(document).on('click', '#rtcl-cf-add-new', function (e) {
    e.preventDefault();
    var it = $(this),
        dialog = $('<div style="display:none;height:450px;" class="rtcl-choose-field">' + it.data('message-loading') + '</div>').appendTo('body');
    dialog.dialog({
      close: function close(event, ui) {
        dialog.remove();
      },
      closeText: false,
      modal: true,
      minWidth: 810,
      maxHeight: .9 * $(window).height(),
      title: it.data('dialog-title'),
      position: {
        my: "center top+50",
        at: "center top",
        of: window
      }
    });

    function add_field_to_fields_list(html) {
      var newField;
      $('#post-body-content #rtcl-cfg').append(html);
      newField = $('#post-body-content #rtcl-cfg .postbox').last();
      $('html, body').animate({
        scrollTop: newField.offset().top - 50
      }, 1000);
      dialog.dialog('close');
      rtclBindAutoCreateSlugs();
      rtclAddPostboxToggles();
      newField.typesFieldOptionsSortable();
      newField.typesMarkExistingField();
      rtclFieldSortable(); //
      // // show bottom "Add new field" and "Save Group Fields" buttons
      // $( '.js-wpcf-fields-add-new, .js-wpcf-second-submit-container' ).removeClass( 'hidden' );
      // wpcf_setup_conditions();
    }

    dialog.load(ajaxurl, {
      action: 'rtcl_edit_field_choose'
    }, function (responseText, textStatus, XMLHttpRequest) {
      var $fields = '';
      var $dialog = $(this).closest('.ui-dialog-content');
      /**
       * choose new field
       */

      $(dialog).on('click', 'span.rtcl-field-button-insert', function () {
        var _it = $(this),
            type = _it.data('type');

        $.ajax({
          url: ajaxurl,
          method: "POST",
          data: {
            action: 'rtcl_edit_field_insert',
            type: type,
            id: parseInt($("#post_ID").val(), 10),
            __rtcl_wpnonce: rtcl_cfg.__rtcl_wpnonce
          },
          beforeSend: function beforeSend() {
            _it.rtclBlock();
          },
          success: function success(data) {
            _it.rtclUnblock();

            if (!data.error) {
              add_field_to_fields_list(data.data);
            } else {
              alert(data.msg);
            }
          },
          error: function error(jqXHR, exception) {
            _it.rtclUnblock();

            alert('Uncaught Error.\n' + jqXHR.responseText);
          }
        });
      });
    });
    return false;
  });
  $(document).on('click', '.js-rtcl-field-remove', function () {
    if (confirm($(this).data('message-confirm'))) {
      var _it = $(this),
          target = _it.closest('.postbox'),
          id = parseInt(target.data('id'), 10);

      if (id) {
        $.ajax({
          url: ajaxurl,
          method: "POST",
          data: {
            action: 'rtcl_edit_field_delete',
            id: id,
            __rtcl_wpnonce: rtcl_cfg.__rtcl_wpnonce
          },
          beforeSend: function beforeSend() {
            target.rtclBlock();
          },
          success: function success(data) {
            target.rtclUnblock();

            if (!data.error) {
              target.slideUp(function () {
                $(this).remove();
              });
            } else {
              alert(data.msg);
            }
          },
          error: function error(jqXHR, exception) {
            target.rtclUnblock();
            alert('Uncaught Error.\n' + jqXHR.responseText);
          }
        });
      } else {
        alert('Field id not selected');
      }
    }

    return false;
  });
  $(document).on('click', '.rtcl-cfg-field .rtcl-select-options-wrap .rtcl-add-new-option', function (e) {
    e.preventDefault();

    var _self = $(this),
        wrap = _self.parent('.rtcl-select-options-wrap'),
        target = $('table.rtcl-fields-field-value-options tbody', wrap),
        type = wrap.data('type') || 'select',
        name = _self.data('name'),
        item = $("<tr />"),
        id = Number(new Date()),
        count = $("tr", target).length + 1,
        default_name = name + "[default]",
        default_type = 'radio';

    if (type == 'checkbox') {
      default_name = name + "[default][]";
      default_type = 'checkbox';
    }

    item.append("<td class='num'><span class='js-types-sort-button hndle dashicons dashicons-menu'></span></td>");
    item.append("<td><input type='text' name='" + name + "[choices][" + id + "][title]' value='Option title " + count + "' ></td>");
    item.append("<td><input type='text' name='" + name + "[choices][" + id + "][value]' value='option-title-" + count + "' ></td>");
    item.append("<td><input type='" + default_type + "' name='" + default_name + "' value='" + id + "' ></td>");
    item.append("<td class='num'><span class='rtcl-delete-option dashicons dashicons-trash'></span></td>");
    target.append(item);
    target.typesFieldOptionsSortable();
    return false;
  });
  $(document).on('click', '.rtcl-cfg-field .rtcl-select-options-wrap .rtcl-delete-option', function (e) {
    e.preventDefault();

    if (confirm("Are you sure?")) {
      var _self = $(this),
          target = _self.parents('tr');

      target.remove();
    }

    return false;
  });

  function rtclBindAutoCreateSlugs() {
    jQuery(document).on('blur focus click', '.js-rtcl-slugize', function () {
      var slug = jQuery(this).val();

      if ('' == slug) {
        slug = jQuery('.js-rtcl-slugize-source', jQuery(this).closest('.postbox')).val();
      }

      if ('' != slug) {
        var validSlug = rtcl_slugize(slug);

        if (validSlug != slug || jQuery(this).val() == '') {
          jQuery(this).val(validSlug.substring(0, 200));
        }
      }
    });
  }

  function rtcl_slugize(val) {
    /**
     * not a string or empty - thank you
     */
    if ('string' != typeof val || '' == val) {
      return;
    }

    val = val.toLowerCase();
    val = val.replace(/[^a-z0-9A-Z_]+/g, '-');
    val = val.replace(/\-+/g, '-');
    val = val.replace(/^\-/g, '');
    val = val.replace(/\-$/g, '');
    return val;
  }

  function rtclFieldSortable() {
    $("#rtcl-cfg").sortable({
      cursor: 'ns-resize',
      axis: 'y',
      handle: 'h2.hndle',
      forcePlaceholderSize: true,
      tolerance: 'pointer',
      start: function start(e, ui) {
        ui.placeholder.height(ui.item.height() + 23);
      }
    });
  } // Sort and Drag


  $.fn.typesFieldOptionsSortable = function () {
    $('.rtcl-fields-radio-sortable, .rtcl-fields-select-sortable, .rtcl-fields-checkboxes-sortable', this).sortable({
      cursor: 'ns-resize',
      axis: 'y',
      handle: '.js-types-sort-button',
      start: function start(e, ui) {
        ui.placeholder.height(ui.item.height() - 2);
      }
    });
    $('.rtcl-fields-checkboxes-sortable', this).sortable({
      start: function start(e, ui) {
        ui.placeholder.height(ui.item.height() + 13);
      }
    });
  };

  $.fn.typesMarkExistingField = function () {
    var slug = $('.rtcl-forms-field-slug', this);
    if (slug.length && slug.val() != '') slug.attr('data-types-existing-field', slug.val());
  };

  $('body').typesFieldOptionsSortable();

  function rtclAddPostboxToggles() {
    $('.postbox .hndle, .postbox .handlediv').unbind('click.postboxes');
    postboxes.add_postbox_toggles();
  }

  wpcfBody.on('keyup', '.rtcl-forms-set-legend', function () {
    var val = $(this).val();

    if (val) {
      val = val.replace(/</, '&lt;');
      val = val.replace(/>/, '&gt;');
      val = val.replace(/'/, '&#39;');
      val = val.replace(/"/, '&quot;');
    }

    $(this).parents('.postbox').find('.rtcl-legend-update').html(val);
  });
})(jQuery);

(function ($) {
  // on dialogopen
  $(document).on('dialogopen', '.ui-dialog', function (e, ui) {
    // normalize primary buttons
    $('button.button-primary, button.wpcf-ui-dialog-cancel').blur().addClass('button').removeClass('ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only');
  }); // resize

  var resizeTimeout;
  $(window).on('resize scroll', function () {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(dialogResize, 200);
  });

  function dialogResize() {
    $('.ui-dialog').each(function () {
      $(this).css({
        'maxWidth': '100%',
        'top': $(window).scrollTop() + 50 + 'px',
        'left': ($('body').innerWidth() - $(this).outerWidth()) / 2 + 'px'
      });
    });
  }
})(jQuery);
