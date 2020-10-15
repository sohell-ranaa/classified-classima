(function ($) {
  'use strict'; // Single listing Comment form

  $('body') // Star ratings for comments
  .on('init', '#rating', function () {
    $('#rating').hide().before('<p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p>');
  }).on('click', '#respond p.stars a', function () {
    var $star = $(this),
        $rating = $(this).closest('#respond').find('#rating'),
        ratingWrap = $rating.parent('.form-group'),
        $container = $(this).closest('.stars');
    $rating.val($star.text());
    $star.siblings('a').removeClass('active');
    $star.addClass('active');
    $container.addClass('selected');
    ratingWrap.removeClass('has-danger');
    ratingWrap.find('.with-errors').remove();
    return false;
  }).on('change', '.rtcl-ordering select.orderby', function () {
    $(this).closest('form').submit();
  }); // Init Tabs and Star Ratings

  $('#rating').trigger('init');
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

  window.rtcl_make_checkout_request = function (form, callback) {
    var $form = $(form),
        $submitBtn = $("button[type=submit]", $form),
        msgHolder = $("<div class='alert rtcl-response'></div>"),
        data = $form.serialize();
    $.ajax({
      url: rtcl.ajaxurl,
      data: data,
      type: "POST",
      dataType: 'JSON',
      beforeSend: function beforeSend() {
        $submitBtn.prop('disabled', true);
        $form.find('.alert.rtcl-response').remove();
        $form.rtclBlock();
      },
      success: function success(response) {
        $submitBtn.prop('disabled', false);
        $form.rtclUnblock();
        var msg = '';

        if (response.success) {
          if (response.success_message.length) {
            response.success_message.map(function (message) {
              msg += "<p>" + message + "</p>";
            });
          }

          if (msg) {
            msgHolder.removeClass('alert-danger').addClass('alert-success').html(msg).appendTo($form);
          }
        } else {
          if (response.error_message.length) {
            response.error_message.map(function (message) {
              msg += "<p>" + message + "</p>";
            });
          }

          if (msg) {
            msgHolder.removeClass('alert-success').addClass('alert-danger').html(msg).appendTo($form);
          }

          if (typeof callback === 'function') {
            callback();
          }
        }

        setTimeout(function () {
          if (response.redirect_url) {
            window.location = response.redirect_url;
          }
        }, 600);
      },
      error: function error(e) {
        $submitBtn.prop('disabled', false);
        $form.rtclUnblock();

        if (typeof callback === 'function') {
          callback();
        }
      }
    });
  };

  window.rtcl_on_recaptcha_load = function () {
    if ('' != rtcl.recaptcha_site_key) {
      // Add reCAPTCHA in registration form
      if ($("#rtcl-registration-g-recaptcha").length) {
        if ($.inArray("registration", rtcl.recaptchas) != -1) {
          rtcl.recaptcha_registration = 1;
          rtcl.recaptcha_responce['registration'] = grecaptcha.render('rtcl-registration-g-recaptcha', {
            'sitekey': rtcl.recaptcha_site_key
          });
          $("#rtcl-registration-g-recaptcha").addClass('mb-2');
        }
      } else {
        rtcl.recaptcha_registration = 0;
      } // Add reCAPTCHA in listing form


      if ($("#rtcl-listing-g-recaptcha").length) {
        if ($.inArray("listing", rtcl.recaptchas) != -1) {
          rtcl.recaptcha_listing = 1;
          rtcl.recaptcha_responce['listing'] = grecaptcha.render('rtcl-listing-g-recaptcha', {
            'sitekey': rtcl.recaptcha_site_key
          });
          grecaptcha.reset();
        }
      } else {
        rtcl.recaptcha_listing = 0;
      } // Add reCAPTCHA in contact form


      if ($("#rtcl-contact-g-recaptcha").length) {
        if ($.inArray("contact", rtcl.recaptchas) != -1) {
          rtcl.recaptcha_responce['contact'] = grecaptcha.render('rtcl-contact-g-recaptcha', {
            'sitekey': rtcl.recaptcha_site_key
          });
          rtcl.recaptcha_contact = 1;
        }
      } else {
        rtcl.recaptcha_contact = 0;
      } // Add reCAPTCHA in report abuse form


      if ($("#rtcl-report-abuse-g-recaptcha").length) {
        if ($.inArray("report_abuse", rtcl.recaptchas) != -1) {
          rtcl.recaptcha_responce['report_abuse'] = grecaptcha.render('rtcl-report-abuse-g-recaptcha', {
            'sitekey': rtcl.recaptcha_site_key
          });
          rtcl.recaptcha_report_abuse = 1;
        }
      } else {
        rtcl.recaptcha_report_abuse = 0;
      }

      $(document).trigger('rtcl_recaptcha_loaded');
    }
  };

  function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split('=');

      if (sParameterName[0] === sParam) {
        return sParameterName[1] === undefined ? true : sParameterName[1];
      }
    }
  }

  function equalHeight() {
    $(".rtcl-equal-height").each(function () {
      var $equalItemWrap = $(this),
          equalItems = $equalItemWrap.find('.equal-item');
      equalItems.height('auto');

      if ($(window).width() > 767) {
        var maxH = 0;
        equalItems.each(function () {
          var itemH = $(this).outerHeight();

          if (itemH > maxH) {
            maxH = itemH;
          }
        });
        equalItems.height(maxH + 'px');
      } else {
        equalItems.height('auto');
      }
    });
  } // On load function


  $(function () {
    $(".rtcl-delete-listing").on('click', function (e) {
      e.preventDefault();

      if (confirm(rtcl.confirm_text)) {
        var _self = $(this),
            wrapper = _self.parents(".rtcl-listing-item"),
            data = {
          action: 'rtcl_delete_listing',
          post_id: parseInt(_self.attr("data-id"), 10),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };

        if (data.post_id) {
          $.ajax({
            url: rtcl.ajaxurl,
            data: data,
            type: "POST",
            beforeSend: function beforeSend() {
              wrapper.rtclBlock();
            },
            success: function success(data) {
              wrapper.rtclUnblock();

              if (data.success) {
                wrapper.animate({
                  height: 0,
                  opacity: 0
                }, 'slow', function () {
                  $(this).remove();
                });
              }
            },
            error: function error() {
              wrapper.rtclUnblock();
            }
          });
        }
      }

      return false;
    });
    $(".rtcl-delete-favourite-listing").on('click', function (e) {
      e.preventDefault();

      if (confirm(rtcl.confirm_text)) {
        var _self = $(this),
            data = {
          action: 'rtcl_public_add_remove_favorites',
          post_id: parseInt(_self.attr("data-id"), 10),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };

        if (data.post_id) {
          $.ajax({
            url: rtcl.ajaxurl,
            data: data,
            type: "POST",
            beforeSend: function beforeSend() {
              $("<span class='rtcl-icon-spinner animate-spin'></span>").insertAfter(_self);
            },
            success: function success(data) {
              _self.next('.rtcl-icon-spinner').remove();

              if (data.success) {
                _self.parents(".rtcl-listing-item").animate({
                  height: 0,
                  opacity: 0
                }, 'slow', function () {
                  $(this).remove();
                });
              }
            },
            error: function error(e) {
              _self.next('.rtcl-icon-spinner').remove();
            }
          });
        }
      }

      return false;
    });
    $("#rtcl-checkout-form").on('click', 'input[name="pricing_id"]', function (e) {
      if ($(this).val() == 0) {
        $("#rtcl-payment-methods, #rtcl-checkout-submit-btn").slideUp(250);
      } else {
        $("#rtcl-payment-methods, #rtcl-checkout-submit-btn").slideDown(250);
      }
    });
    $("#rtcl-checkout-form").on('change', 'input[name="payment_method"]', function (e) {
      var target_payment_box = $('div.payment_box.payment_method_' + $(this).val());

      if ($(this).is(':checked') && !target_payment_box.is(':visible')) {
        $('#rtcl-checkout-form div.payment_box').filter(':visible').slideUp(250);

        if ($(this).is(':checked')) {
          target_payment_box.slideDown(250);
        }
      }
    }); // Profile picture upload

    $(".rtcl-media-upload-pp .rtcl-media-action").on('click', 'span.add', function () {
      var addBtn = $(this);
      var ppFile = $("<input type='file' style='position:absolute;left:-9999px' />");
      $('body').append(ppFile);

      if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
        ppFile.trigger('change');
      } else {
        ppFile.trigger('click');
      }

      ppFile.on('change', function () {
        var fileItem = $(this);
        var pp_wrap = addBtn.parents(".rtcl-media-upload-pp");
        var pp_thumb_holder = $('.rtcl-media-item', pp_wrap);
        var form = new FormData();
        var pp = fileItem[0].files[0];
        var allowed_image_types = rtcl.image_allowed_type.map(function (type) {
          return 'image/' + type;
        });
        var max_image_size = parseInt(rtcl.max_image_size);

        if ($.inArray(pp.type, allowed_image_types) !== -1) {
          if (pp.size <= max_image_size) {
            form.append('pp', pp);
            form.append('__rtcl_wpnonce', rtcl.__rtcl_wpnonce);
            form.append('action', 'rtcl_ajax_user_profile_picture_upload');
            $.ajax({
              url: rtcl.ajaxurl,
              data: form,
              cache: false,
              contentType: false,
              processData: false,
              type: 'POST',
              beforeSend: function beforeSend() {
                pp_wrap.rtclBlock();
              },
              success: function success(response) {
                pp_wrap.rtclUnblock();

                if (!response.error) {
                  pp_wrap.removeClass('no-media').addClass('has-media').parents('.rtcl-profile-picture-wrap').find('.rtcl-gravatar-wrap').hide();
                  pp_thumb_holder.html("<img class='rtcl-thumbnail' src='" + response.data.src + "'/>");
                }
              },
              error: function error(jqXhr, json, errorThrown) {
                pp_wrap.rtclUnblock();
                console.log('error');
              }
            });
          } else {
            alert(rtcl.error_image_size);
          }
        } else {
          alert(rtcl.error_image_extension);
        }
      });
    }).on('click', 'span.remove', function () {
      var self = $(this);
      var pp_wrap = self.parents(".rtcl-media-upload-pp");
      var media_holder = $('.rtcl-media-item', pp_wrap);

      if (confirm(rtcl.confirm_text)) {
        $.ajax({
          url: rtcl.ajaxurl,
          data: {
            action: 'rtcl_ajax_user_profile_picture_delete',
            __rtcl_wpnonce: rtcl.__rtcl_wpnonce
          },
          type: 'POST',
          beforeSend: function beforeSend() {
            pp_wrap.rtclBlock();
          },
          success: function success(response) {
            pp_wrap.rtclUnblock();

            if (!response.error) {
              pp_wrap.removeClass('has-media').addClass('no-media').parents('.rtcl-profile-picture-wrap').find('.rtcl-gravatar-wrap').show();
              media_holder.html("");
            }
          },
          error: function error(jqXhr, json, errorThrown) {
            pp_wrap.rtclUnblock();
            console.log('error');
          }
        });
      }
    }); // Toggle password fields in user account form

    $('#rtcl-change-password').on('change', function () {
      var $checked = $(this).is(":checked");

      if ($checked) {
        $('.rtcl-password-fields').show().find('input[type="password"]').attr("disabled", false);
      } else {
        $('.rtcl-password-fields').hide().find('input[type="password"]').attr("disabled", "disabled");
      }
    }).trigger('change'); // Report abuse [on modal closed]

    $('#rtcl-report-abuse-modal').on('hidden.bs.modal', function (e) {
      $('#rtcl-report-abuse-message').val('');
      $('#rtcl-report-abuse-message-display').html('');
      $(this).find('.modal-dialog').removeClass('modal-vertical-centered');
    });
    $('#rtcl-report-abuse-modal').on('shown.bs.modal', function () {
      $(this).find('.modal-dialog').addClass('modal-vertical-centered');
    }); // Alert users to login (only if applicable)

    $('.rtcl-require-login').on('click', function (e) {
      e.preventDefault();
      alert(rtcl.user_login_alert_message);
    }); // Contact do email

    $('.rtcl-do-email').on('click', 'a', function (e) {
      e.preventDefault();

      var _self = $(this),
          wrap = _self.parents('.rtcl-do-email');

      $("#rtcl-contact-form", wrap).slideToggle("slow");
      return false;
    }); // Add or Remove from favourites

    $(document).on('click', 'a.rtcl-favourites', function (e) {
      e.preventDefault();

      var _self = $(this),
          data = {
        action: 'rtcl_public_add_remove_favorites',
        post_id: parseInt(_self.attr("data-id"), 10),
        __rtcl_wpnonce: rtcl.__rtcl_wpnonce
      };

      if (data.post_id) {
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: "POST",
          beforeSend: function beforeSend() {
            $("<span class='rtcl-icon-spinner animate-spin'></span>").insertAfter(_self);
          },
          success: function success(data) {
            _self.next('.rtcl-icon-spinner').remove();

            if (data.success) {
              _self.replaceWith(data.data);
            }
          },
          error: function error(e) {
            _self.next('.rtcl-icon-spinner').remove();
          }
        });
      }
    });
    /**
     * Slider Class.
     */

    var RtclSlider = function RtclSlider($target, args) {
      this.$target = $target;
      this.slider_enabled = $.isFunction($.fn.owlCarousel);
      this.options = this.$target.data('options') || {};

      this.initSlider = function () {
        if (!this.slider_enabled) {
          return;
        }

        this.$target.owlCarousel({
          responsive: {
            0: {
              items: 1
            },
            320: {
              items: this.options.mobile_items ? parseInt(this.options.mobile_items, 10) : 1
            },
            768: {
              items: this.options.tab_items ? parseInt(this.options.tab_items, 10) : 3
            },
            992: {
              items: this.options.items ? parseInt(this.options.items, 10) : 4
            }
          },
          margin: this.options.margin ? parseInt(this.options.margin, 10) : 0,
          rtl: !!parseInt(rtcl.is_rtl),
          nav: !!this.options.nav,
          dots: !!this.options.dots,
          autoplay: !!this.options.autoplay,
          smartSpeed: this.options.smart_speed ? parseInt(this.options.smart_speed, 10) : 250,
          autoplaySpeed: this.options.autoplay_speed ? this.options.autoplay_speed : false,
          navSpeed: this.options.nav_speed ? this.options.nav_speed : false,
          dotsSpeed: this.options.dots_speed ? this.options.dots_speed : false,
          navText: ['<i class="rtcl-icon-angle-left"></i>', '<i class="rtcl-icon-angle-right"></i>']
        });
      };

      this.imagesLoaded = function () {
        var that = this;

        if (!$.isFunction($.fn.imagesLoaded) || $.fn.imagesLoaded.done) {
          this.$target.trigger('rtcl_slider_loading', this);
          this.$target.trigger('rtcl_slider_loaded', this);
          return;
        }

        this.$target.imagesLoaded().progress(function (instance, image) {
          that.$target.trigger('rtcl_slider_loading', [that]);
        }).done(function (instance) {
          that.$target.trigger('rtcl_slider_loaded', [that]);
        });
      };

      this.start = function () {
        var that = this;
        this.$target.on('rtcl_slider_loaded', this.init.bind(this));
        setTimeout(function () {
          that.imagesLoaded();
        }, 1);
      };

      this.init = function () {
        this.initSlider();
      };

      this.start();
    };

    $.fn.rtcl_slider = function (args) {
      new RtclSlider(this, args);
      return this;
    };

    $('.rtcl-carousel-slider').each(function () {
      $(this).addClass("owl-carousel").rtcl_slider();
    }); // Populate child terms dropdown

    $('.rtcl-terms').on('change', 'select', function (e) {
      e.preventDefault();
      var $this = $(this),
          taxonomy = $this.data('taxonomy'),
          parent = $this.data('parent'),
          value = $this.val(),
          slug = $this.find(':selected').attr('data-slug') || '',
          classes = $this.attr('class'),
          termHolder = $this.closest('.rtcl-terms').find('input.rtcl-term-hidden'),
          termValueHolder = $this.closest('.rtcl-terms').find('input.rtcl-term-hidden-value');
      termHolder.val(value).attr("data-slug", slug);
      termValueHolder.val(slug);
      $this.parent().find('div:first').remove();

      if (parent !== value) {
        $this.parent().append('<div class="rtcl-spinner"><span class="rtcl-icon-spinner animate-spin"></span></div>');
        var data = {
          'action': 'rtcl_child_dropdown_terms',
          'taxonomy': taxonomy,
          'parent': value,
          'class': classes
        };
        $.post(rtcl.ajaxurl, data, function (response) {
          $this.parent().find('div:first').remove();
          $this.parent().append(response);
        });
      }
    });
    var listObj = {
      active: null,
      target: null,
      loc: {
        items: [],
        selected: null,
        parents: [],
        text: rtcl.location_text
      },
      cat: {
        items: [],
        selected: null,
        parents: [],
        text: rtcl.category_text
      }
    };
    $(".rtcl-widget-search-form .rtcl-search-input-category").on("click", function () {
      listObj.active = 'cat';
      listObj.target = $(this);
      var modal = new RtclModal({
        footer: false
      });

      if (!listObj.cat.items.length) {
        $.ajax({
          url: rtcl.ajaxurl,
          type: "POST",
          data: {
            action: 'rtcl_get_all_cat_list_for_modal'
          },
          beforeSend: function beforeSend() {
            modal.addModal().addLoading();
          },
          success: function success(response) {
            modal.removeLoading();

            if (response.success) {
              listObj.cat.items = response.categories;
              listObj.cat.selected = null;
              listObj.cat.parent = null;
              modal.content(generate_list());
            }
          },
          error: function error(e) {
            modal.removeLoading();
            modal.content(rtcl_validator.server_error);
          }
        });
      } else {
        modal.addModal();
        modal.content(generate_list());
      }
    });
    $(".rtcl-widget-search-form .rtcl-search-input-location").on("click", function () {
      listObj.active = 'loc';
      listObj.target = $(this);
      var modal = new RtclModal({
        footer: false
      });

      if (!listObj.loc.items.length) {
        $.ajax({
          url: rtcl.ajaxurl,
          type: "POST",
          data: {
            action: 'rtcl_get_all_location_list_for_modal'
          },
          beforeSend: function beforeSend() {
            modal.addModal().addLoading();
          },
          success: function success(response) {
            modal.removeLoading();

            if (response.success) {
              listObj.loc.items = response.locations;
              listObj.loc.selected = null;
              listObj.loc.parent = null;
              modal.content(generate_list());
            } else {
              modal.content(rtcl_validator.server_error);
            }
          },
          error: function error(e) {
            modal.removeLoading();
            modal.content(rtcl_validator.server_error);
          }
        });
      } else {
        modal.addModal();
        modal.content(generate_list());
      }
    });

    if ($.fn.autocomplete && $('.rtcl-autocomplete').length) {
      $(".rtcl-widget-search-form .rtcl-autocomplete").autocomplete({
        minChars: 2,
        search: function search(event, ui) {
          if (!$(event.target).parent().find('.rtcl-icon-spinner').length) {
            $("<span class='rtcl-icon-spinner animate-spin'></span>").insertAfter(event.target);
          }
        },
        response: function response(event, ui) {
          $(event.target).parent().find('.rtcl-icon-spinner').remove();
        },
        source: function source(req, response) {
          req.location_slug = rtcl.rtcl_location || '';
          req.category_slug = rtcl.rtcl_category || '';
          req.__rtcl_wpnonce = rtcl.__rtcl_wpnonce;
          req.type = $(this.element).data('type') || 'listing';
          req.action = 'rtcl_inline_search_autocomplete';
          $.ajax({
            dataType: "json",
            type: "POST",
            url: rtcl.ajaxurl,
            data: req,
            success: response
          });
        },
        select: function select(event, ui) {
          var _self = $(event.target);

          _self.next('input').val(ui.item.target);
        }
      }).data("ui-autocomplete")._renderItem = function (ul, item) {
        return $("<li />").data("item.autocomplete", item).append(item.label).appendTo(ul);
      };
    }

    $('.rtcl-ajax-load').each(function () {
      var _self = $(this),
          settings = _self.data('settings') || {};

      settings.action = 'rtcl_ajax_taxonomy_filter_get_sub_level_html';
      settings.__rtcl_wpnonce = rtcl.__rtcl_wpnonce;
      $.ajax({
        url: rtcl.ajaxurl,
        type: "POST",
        dataType: 'json',
        data: settings,
        beforeSend: function beforeSend() {
          _self.rtclBlock();
        },
        success: function success(response) {
          _self.html(response.data).rtclUnblock();
        },
        complete: function complete() {
          _self.rtclUnblock();
        },
        error: function error(request, status, _error) {
          _self.rtclUnblock();

          if (status === 500) {
            console.error('Error while adding comment');
          } else if (status === 'timeout') {
            console.error('Error: Server doesn\'t respond.');
          } else {
            // process WordPress errors
            var wpErrorHtml = request.responseText.split("<p>"),
                wpErrorStr = wpErrorHtml[1].split("</p>");
            console.error(wpErrorStr[0]);
          }
        }
      });
    });
    $(document).on('click', '.rtcl-ui-select-list li.has-sub a', function (e) {
      e.preventDefault();

      var type = listObj.active,
          items = listObj[type].items,
          _self = $(this),
          _item = _self.data('item'),
          list = [],
          wrap = _self.parents('.rtcl-ui-select-list-wrap'),
          list_wrap = $('.rtcl-ui-select-list', wrap),
          action = $('.rtcl-select-action', wrap),
          title = $('h4', wrap),
          ul = _self.parents('ul'),
          selectedItemId = parseInt(_item.id, 10),
          selectedItem;

      if (listObj[type].selected) {
        selectedItem = listObj[type].selected.sub.find(function (item) {
          return item.id === selectedItemId;
        });
        listObj[type].parent = listObj[type].selected.id;
      } else {
        selectedItem = items.find(function (item) {
          return item.id === selectedItemId;
        });
      }

      listObj[type].selected = selectedItem;

      if (selectedItem.parent) {
        listObj[type].parents.push(selectedItem.parent);
      }

      if (selectedItem.hasOwnProperty("sub") && selectedItem.sub.length) {
        ul.remove();
        list_wrap.html(get_list(selectedItem.sub));
        var a = $('<a href="javascript:;" />');
        a.append(selectedItem.name);
        a.attr("data-item", JSON.stringify(get_safe_term_item(selectedItem)));

        if (title.find('span').length) {
          title.find('span').html(a);
        } else {
          var wrapItem = $('<span class="rtcl-icon-angle-right rtcl-selected-term-item" />').append(a);
          title.append(wrapItem);
        }

        action.html("<div class='go-back'>" + rtcl.go_back + "</div>");
      }
    });

    function findSelectedItemFromListByIds(ids, list) {
      function findSelectedItem(id) {
        if (selectedItem.sub) {
          selectedItem = selectedItem.sub;
        }

        return selectedItem.find(function (item) {
          return id === item.id;
        });
      }

      var selectedItem = list;

      if (ids.length) {
        for (var i = 0; i < ids.length; i++) {
          selectedItem = findSelectedItem(ids[i], selectedItem);
        }
      }

      return selectedItem;
    }

    $(document).on('click', '.rtcl-select-action .go-back', function (e) {
      e.preventDefault();

      var type = listObj.active,
          _self = $(this),
          wrap = _self.parents('.rtcl-ui-select-list-wrap'),
          list_wrap = $('.rtcl-ui-select-list', wrap),
          title = $('h4', wrap),
          action = $('.rtcl-select-action', wrap),
          list,
          selectedItem,
          level = 0;

      if (listObj[type].parents.length) {
        selectedItem = findSelectedItemFromListByIds(listObj[type].parents, listObj[type].items);
        list = selectedItem.sub;
        listObj[type].parents.pop();
        listObj[type].selected = selectedItem;
        level = 1;
      } else {
        listObj[type].selected = null;
        list = listObj[type].items;
      }

      list_wrap.html('');
      list_wrap.append(get_list(list));

      if (level) {
        var a = $('<a href="javascript:;" />');
        a.append(selectedItem.name);
        a.attr("data-item", JSON.stringify(get_safe_term_item(selectedItem)));

        if (title.find('span').length) {
          title.find('span').html(a);
        } else {
          var wrapItem = $('<span class="rtcl-icon-angle-right rtcl-selected-term-item" />').append(a);
          title.append(wrapItem);
        }
      } else {
        title.find('span').remove();
        action.find('.go-back').remove();
      }
    });
    $(document).on('click', '.rtcl-ui-select-list li:not(.has-sub) a, .rtcl-selected-term-item a', function (e) {
      e.preventDefault();

      var _self = $(this),
          _item = _self.data('item') || null;

      if (_item && listObj.target.length) {
        listObj.target.find('.search-input-label').text(_item.name);
        listObj.target.find('input.rtcl-term-field').val(_item.slug);
        $('body > .rtcl-ui-modal').remove(); // TODO need to make this dynamic

        $('body').removeClass('rtcl-modal-open');
        listObj.target.closest('form').submit();
      }

      return false;
    });

    function generate_list() {
      var type = listObj.active,
          items = listObj[type].items,
          ul = get_list(items);
      var container = $('<div class="rtcl-ui-select-list-wrap"><h4>' + listObj[type].text + '</h4><div class="rtcl-select-action"></div><div class="rtcl-ui-select-list"></div></div>');
      container.find('.rtcl-ui-select-list').append(ul);
      return container;
    }

    function get_list(items) {
      var ul = $('<ul />');
      items.forEach(function (item) {
        var a = $('<a href="javascript:;" />'),
            li = $('<li />');

        if (item.hasOwnProperty("sub")) {
          li.addClass('has-sub');
        }

        if (item.hasOwnProperty("icon")) {
          a.html(item.icon);
        }

        a.append(item.name);
        a.attr("data-item", JSON.stringify(get_safe_term_item(item)));
        li.append(a);
        ul.append(li);
      });
      return ul;
    }

    function get_safe_term_item(item) {
      var safe_item = Object.assign({
        icon: '',
        sub: ''
      }, item);
      delete safe_item['icon'];
      delete safe_item['sub'];
      return safe_item;
    }

    $(document).on("click", ".ul-list-group.is-parent > ul > li > a", function (e) {
      e.preventDefault();
      var self = $(this),
          li = self.parent('li'),
          parent = li.parent('ul'),
          target = $(".col-md-6.sub-wrapper"),
          list = li.find('.ul-list-group.is-sub').clone() || '',
          a = self.clone(),
          wrap = $("<li />");
      a = wrap.append(a);
      list.find('ul').prepend(a);
      target.addClass('is-active');
      target.html(list);
      parent.find("> li").removeClass('is-active');
      li.addClass('is-active');
      return false;
    });
    $(document).on("click", '.rtcl-filter-form .filter-list .is-parent.has-sub .arrow', function (e) {
      e.preventDefault();
      var self = $(this),
          li = self.closest('li'),
          parent = self.closest('.ui-accordion-content'),
          is_ajax_load = parent.hasClass('rtcl-ajax-load'),
          settings = parent.data('settings') || {},
          target = li.find('> ul.sub-list');

      if (li.hasClass('is-open')) {
        target.slideUp(function () {
          li.removeClass('is-open');
        });
      } else {
        if (is_ajax_load && settings.taxonomy && li.hasClass('has-sub') && !li.hasClass('is-loaded')) {
          if (!parent.hasClass('rtcl-loading')) {
            settings.parent = li.data('id') || -1;
            settings.action = 'rtcl_ajax_taxonomy_filter_get_sub_level_html';
            $.ajax({
              url: rtcl.ajaxurl,
              type: "POST",
              dataType: 'json',
              data: settings,
              beforeSend: function beforeSend() {
                parent.rtclBlock();
              },
              success: function success(response) {
                li.append(response.data);
                parent.rtclUnblock();
                target.slideDown();
                li.addClass('is-open is-loaded');
              },
              complete: function complete() {
                parent.rtclUnblock();
              },
              error: function error(request, status, _error2) {
                parent.rtclUnblock();

                if (status === 500) {
                  console.error('Error while adding comment');
                } else if (status === 'timeout') {
                  console.error('Error: Server doesn\'t respond.');
                } else {
                  // process WordPress errors
                  var wpErrorHtml = request.responseText.split("<p>"),
                      wpErrorStr = wpErrorHtml[1].split("</p>");
                  console.error(wpErrorStr[0]);
                }
              }
            });
          }
        } else {
          target.slideDown();
          li.addClass('is-open');
        }
      }
    });
    $(".rtcl-filter-form .ui-accordion-item").on('click', '.ui-accordion-title', function () {
      var self = $(this),
          holder = self.parents('.ui-accordion-item'),
          target = $(".ui-accordion-content", holder);

      if (holder.hasClass('is-open')) {
        target.slideUp(function () {
          holder.removeClass('is-open');
        });
      } else {
        target.slideDown();
        holder.addClass('is-open');
      }
    });
    $(".rtcl-filter-form").on("click", '.filter-submit-trigger', function (e) {
      var r,
          i,
          self = $(this);

      if (!self.is(':checkbox')) {
        e.preventDefault();
        r = self.siblings("input");
        i = r.prop("checked");
        r.prop("checked", !i);
      }

      self.closest('form').submit();
    });
    $(document).on('click', 'ul.filter-list.is-collapsed li.is-opener, ul.sub-list.is-collapsed li.is-opener, ul.ui-link-tree.is-collapsed li.is-opener', function () {
      $(this).parent('ul').removeClass('is-collapsed').addClass('is-open');
    });
    /* REVEAL PHONE */

    $('.reveal-phone').on('click', function (e) {
      var $this = $(this),
          isMobile = $this.hasClass('rtcl-mobile');

      if (!$this.hasClass('revealed')) {
        e.preventDefault();
        var options = $this.data('options') || {};
        var $numbers = $this.find('.numbers');
        var aPhone = '';
        var wPhone = '';

        if (options.safe_phone && options.phone_hidden) {
          var purePhone = options.safe_phone.replace(rtcl.phone_number_placeholder, options.phone_hidden);
          aPhone = $('<a href="#" />').attr('href', "tel:" + purePhone).text(purePhone);
          $this.attr('data-tel', 'tel:' + purePhone);
        }

        if (options.safe_whatsapp_number && options.whatsapp_hidden) {
          var pureWPhone = options.safe_whatsapp_number.replace(rtcl.phone_number_placeholder, options.whatsapp_hidden);
          wPhone = $('<a class="revealed-whatsapp-number" href="#" />').attr('href', "https://wa.me/" + pureWPhone.replace(/\D/g, "").replace(/^0+/, "")).html('<i class="rtcl-icon rtcl-icon-whatsapp"></i>').append(pureWPhone);
        }

        $numbers.html(aPhone).append(wPhone);
        $this.addClass('revealed');
      } else {
        if (isMobile) {
          var tel = $this.attr("data-tel");

          if (tel) {
            window.location = tel;
          }
        }
      }
    });
    var option = getUrlParameter('option') || '',
        gateway = getUrlParameter('gateway') || '';

    if (option) {
      $("input[name='pricing_id'][value='" + option + "']").prop('checked', true);
    } else {
      $("input[name='pricing_id'][value='0']").prop('checked', true);
    }

    if (gateway) {
      $("label[for='gateway-" + gateway + "']").trigger('click');
    }

    rtclInitDateField();
  });

  if ($.fn.validate) {
    $('#rtcl-lost-password-form, #rtcl-password-reset-form').validate(); // Comment validation

    $(".rtcl #commentform").validate({
      submitHandler: function submitHandler(form) {
        var f = $(form),
            $rating = f.find('#rating'),
            ratingWrap = $rating.parent('.form-group'),
            rating = $rating.val(),
            responseWrapper = $('<div class="alert" />'),
            comments = $('#comments'),
            commentlist = $('.comment-list'),
            cancelreplylink = $('#cancel-comment-reply-link'),
            button = f.find('.btn'),
            addedCommentHTML;

        if ($rating.length > 0 && !rating) {
          ratingWrap.addClass('has-danger');
          ratingWrap.find('.with-errors').remove();
          ratingWrap.append('<div class="with-errors help-block">' + rtcl.i18n_required_rating_text + '</div>');
          return false;
        } // Post via AJAX


        var data = f.serialize() + '&action=rtcl_ajax_submit_comment';
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function beforeSend() {
            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(f.find('.btn'));
            button.val('Loading...').prop("disabled", true);
            f.next('.alert').remove();
          },
          success: function success(response) {
            f.find('.btn').next('.rtcl-icon-spinner').remove();
            button.prop("disabled", false);
            responseWrapper.html(response.message).insertAfter(f);

            if (response.error) {
              responseWrapper.addClass('alert-danger');
            } else {
              responseWrapper.addClass('alert-success');

              if (response.comment_id) {
                $("#li-comment-" + response.comment_id).slideUp(250, function () {
                  $(this).remove();
                });
              }

              if (commentlist.length > 0) {
                commentlist.append(response.comment_html);
              } else {
                // if no comments yet
                addedCommentHTML = '<ol class="comment-list">' + response.comment_html + '</ol>';
                comments.append($(addedCommentHTML));
              }

              f[0].reset();
              f.find('p.stars').removeClass('selected');
            }
          },
          complete: function complete() {
            // what to do after a comment has been added
            button.val('Submit').prop("disabled", false);
          },
          error: function error(request, status, _error3) {
            f.next('.rtcl-icon-spinner').remove();
            button.val('Submit').prop("disabled", false);

            if (status === 500) {
              alert('Error while adding comment');
            } else if (status === 'timeout') {
              alert('Error: Server doesn\'t respond.');
            } else {
              // process WordPress errors
              var wpErrorHtml = request.responseText.split("<p>"),
                  wpErrorStr = wpErrorHtml[1].split("</p>");
              alert(wpErrorStr[0]);
            }
          }
        });
        return false;
      }
    }); // Check out validation

    $("#rtcl-checkout-form").validate({
      submitHandler: function submitHandler(form) {
        rtcl_make_checkout_request(form);
        return false;
      }
    }); //Login form

    $('form#rtcl-login-form,form.rtcl-login-form').validate({
      submitHandler: function submitHandler(form) {
        var $form = $(form),
            fromData = new FormData(form);
        fromData.append('action', 'rtcl_login_request');
        fromData.append('__rtcl_wpnonce', rtcl.__rtcl_wpnonce);
        $.ajax({
          url: rtcl.ajaxurl,
          type: 'POST',
          dataType: 'json',
          cache: false,
          processData: false,
          contentType: false,
          data: fromData,
          beforeSend: function beforeSend() {
            $form.find('.rtcl-error').remove();
            $form.rtclBlock();
          },
          success: function success(res) {
            $form.rtclUnblock();

            if (res.success) {
              $form.append('<div class="rtcl-error alert alert-success" role="alert"><p>' + res.data.message + '</p></div>');
              $form[0].reset();
              window.location.reload(true);
            } else {
              $form.append('<div class="rtcl-error alert alert-danger" role="alert"><p>' + res.data + '</p></div>');
            }
          },
          error: function error() {
            $form.rtclUnblock().append('<div class="rtcl-error alert alert-danger" role="alert"><p>' + rtcl_validator.messages.server_error + '</p></div>');
          }
        });
      }
    }); // Validate registration form

    $('form#rtcl-register-form').validate({
      submitHandler: function submitHandler(form) {
        if (rtcl.recaptcha_registration > 0) {
          var response = grecaptcha.getResponse(rtcl.recaptcha_responce['registration']);

          if (0 === response.length) {
            $('#rtcl-registration-g-recaptcha-message').addClass('text-danger').html(rtcl.recaptcha_invalid_message);
            grecaptcha.reset(rtcl.recaptcha_responce['registration']);
            return false;
          }
        }

        var $form = $(form),
            fromData = new FormData(form);
        fromData.append('action', 'rtcl_registration_request');
        fromData.append('__rtcl_wpnonce', rtcl.__rtcl_wpnonce);
        $.ajax({
          url: rtcl.ajaxurl,
          type: 'POST',
          dataType: 'json',
          cache: false,
          processData: false,
          contentType: false,
          data: fromData,
          beforeSend: function beforeSend() {
            $form.find('.rtcl-error').remove();
            $form.rtclBlock();
          },
          success: function success(res) {
            $form.rtclUnblock();

            if (res.success) {
              $form.append('<div class="rtcl-error alert alert-success" role="alert"><p>' + res.data.message + '</p></div>');
              $form[0].reset();

              if (res.data.redirect_url && res.data.redirect_utl !== window.location.href) {
                window.location = res.data.redirect_url + '?t=' + new Date().getTime();
              }
            } else {
              $form.append('<div class="rtcl-error alert alert-danger" role="alert"><p>' + res.data + '</p></div>');
            }
          },
          error: function error() {
            $form.rtclUnblock().append('<div class="rtcl-error alert alert-danger" role="alert"><p>' + rtcl_validator.messages.server_error + '</p></div>');
          }
        });
      }
    }); // Validate report abuse form

    $('#rtcl-report-abuse-form').validate({
      submitHandler: function submitHandler(form) {
        if (rtcl.recaptcha_report_abuse > 0) {
          var response = grecaptcha.getResponse(rtcl.recaptcha_responce['report_abuse']);

          if (0 === response.length) {
            $('#rtcl-report-abuse-message-display').removeClass('text-success').addClass('text-danger').html(rtcl.recaptcha_invalid_message);
            grecaptcha.reset(rtcl.recaptcha_responce['report_abuse']);
            return false;
          }
        } // Post via AJAX


        var data = {
          'action': 'rtcl_public_report_abuse',
          'post_id': rtcl.post_id || 0,
          'message': $('#rtcl-report-abuse-message').val(),
          'g-recaptcha-response': response
        },
            targetBtn = $(form).find('.btn.btn-primary');
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function beforeSend() {
            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(targetBtn);
          },
          success: function success(response) {
            targetBtn.next('.rtcl-icon-spinner').remove();

            if (response.error) {
              $('#rtcl-report-abuse-message-display').removeClass('text-success').addClass('text-danger').html(response.message);
            } else {
              $(form)[0].reset();
              $('#rtcl-report-abuse-message-display').removeClass('text-danger').addClass('text-success').html(response.message);
              setTimeout(function () {
                $('#rtcl-report-abuse-modal').modal('hide');
              }, 1500);
            }

            if (rtcl.recaptcha_contact > 0) {
              grecaptcha.reset(rtcl.recaptcha_responce['report_abuse']);
            }
          },
          error: function error(e) {
            $('#rtcl-report-abuse-message-display').removeClass('text-success').addClass('text-danger').html(e);
            targetBtn.next('.rtcl-icon-spinner').remove();
          }
        });
      }
    });
    $('#rtcl-contact-form').validate({
      submitHandler: function submitHandler(form) {
        var f = $(form);

        if (rtcl.recaptcha_contact > 0) {
          var response = grecaptcha.getResponse(rtcl.recaptcha_responce['contact']);

          if (0 == response.length) {
            $('#rtcl-contact-message-display').addClass('text-danger').html(rtcl.recaptcha_invalid_message);
            grecaptcha.reset(rtcl.recaptcha_responce['contact']);
            return false;
          }
        } // Post via AJAX


        var data = {
          'action': 'rtcl_public_send_contact_email',
          'post_id': rtcl.post_id || 0,
          'name': $('#rtcl-contact-name').val(),
          'email': $('#rtcl-contact-email').val(),
          'message': $('#rtcl-contact-message').val(),
          'g-recaptcha-response': response
        };
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function beforeSend() {
            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(f.find('.btn'));
          },
          success: function success(response) {
            f.find('.btn').next('.rtcl-icon-spinner').remove();

            if (response.error) {
              $('#rtcl-contact-message-display').removeClass('text-success').addClass('text-danger').html(response.message);
            } else {
              f[0].reset();
              $('#rtcl-contact-message-display').removeClass('text-danger').addClass('text-success').html(response.message);

              if (f.parent().data('hide') !== 0) {
                setTimeout(function () {
                  f.slideUp();
                }, 800);
              }
            }

            if (rtcl.recaptcha_contact > 0) {
              grecaptcha.reset(rtcl.recaptcha_responce['contact']);
            }
          },
          error: function error(e) {
            $('#rtcl-contact-message-display').removeClass('text-success').addClass('text-danger').html(e);
            f.find('.btn').next('.rtcl-icon-spinner').remove();
          }
        });
      }
    }); // User account form

    $("#rtcl-user-account").validate({
      submitHandler: function submitHandler(form) {
        var $form = $(form),
            targetBtn = $form.find('input[type=submit]'),
            responseHolder = $form.find('.rtcl-response'),
            msgHolder = $("<div class='alert'></div>"),
            data = {
          action: "rtcl_update_user_account",
          first_name: $form.find("input[name='first_name']").val(),
          last_name: $form.find("input[name='last_name']").val(),
          email: $form.find("input[name='email']").val(),
          change_password: !!$form.find("input[name='change_password']").is(":checked"),
          pass1: $form.find("input[name='pass1']").val(),
          pass2: $form.find("input[name='pass2']").val(),
          phone: $form.find("input[name='phone']").val(),
          whatsapp_number: $form.find("input[name='whatsapp_number']").val(),
          website: $form.find("input[name='website']").val(),
          zipcode: $form.find("input[name='zipcode']").val(),
          address: $form.find("textarea[name='address']").val(),
          latitude: $form.find("input[name='latitude']").val(),
          longitude: $form.find("input[name='longitude']").val(),
          location: $form.find("select[name='location']").val(),
          sub_location: $form.find("select[name='sub_location']").val(),
          sub_sub_location: $form.find("select[name='sub_sub_location']").val(),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function beforeSend() {
            $form.addClass("rtcl-loading");
            targetBtn.prop("disabled", true);
            responseHolder.html('');
            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(targetBtn);
          },
          success: function success(response) {
            targetBtn.prop("disabled", false).next('.rtcl-icon-spinner').remove();
            $form.removeClass("rtcl-loading");

            if (!response.error) {
              $form.find("input[name=pass1]").val('');
              $form.find("input[name=pass2]").val('');
              msgHolder.removeClass('alert-danger').addClass('alert-success').html(response.message).appendTo(responseHolder);
              setTimeout(function () {
                responseHolder.html('');
              }, 1000);
            } else {
              msgHolder.removeClass('alert-success').addClass('alert-danger').html(response.message).appendTo(responseHolder);
            }
          },
          error: function error(e) {
            msgHolder.removeClass('alert-success').addClass('alert-danger').html(e.responseText).appendTo(responseHolder);
            targetBtn.prop("disabled", false).next('.rtcl-icon-spinner').remove();
            $form.removeClass("rtcl-loading");
          }
        });
      }
    });
  }

  window.rtclInitDateField = function () {
    if ($.fn.daterangepicker) {
      $('.rtcl-date').each(function () {
        var input = $(this);
        var options = input.data('options') || {};
        $(this).daterangepicker(options);

        if (options.autoUpdateInput === false) {
          input.on('apply.daterangepicker', function (ev, picker) {
            if (picker.singleDatePicker) {
              $(this).val(picker.startDate.format(picker.locale.format));
            } else {
              $(this).val(picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format));
            }
          });
          input.on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
          });
        }
      });
    }
  }; // Window load and resize function


  $(window).on('resize load', equalHeight); // single page animate scroll

  $('body').on('click', '.rtcl-animate', function (e) {
    e.preventDefault();
    var position = $($(this).attr('href')).offset();
    $('html,body').stop().animate({
      scrollTop: position.top - 120
    }, 500);
  });
})(jQuery);
