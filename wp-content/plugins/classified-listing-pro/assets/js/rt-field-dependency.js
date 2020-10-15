function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  /**
   * Use on your css file
   * [data-rt-depends] {
   *    display : none;
   * }
   * @param options
   * @constructor
   */
  $.fn.rtFieldDependency = function (options) {
    this._targets = $(this);
    this._settings = $.extend({
      'attribute': 'rt-depends',
      'rules': {}
    }, options);
    var that = this;
    /**
     * Check array exists on array
     * @param needleArray
     * @param haystackArray
     * @param strict
     * @returns {boolean}
     */

    var arrayInArraysHelper = function arrayInArraysHelper(needleArray, haystackArray, strict) {
      if (typeof strict == 'undefined') {
        strict = false;
      }

      if (needleArray == null) {
        needleArray = [];
      }

      if (strict == true) {
        return needleArray.sort().join(',').toLowerCase() == haystackArray.sort().join(',').toLowerCase();
      } else {
        for (var i = 0; i < needleArray.length; i++) {
          if (haystackArray.indexOf(needleArray[i]) >= 0) {
            return true;
          }
        }

        return false;
      }
    };
    /**
     * Check string exist on array value
     * @param needleString
     * @param haystackArray
     * @returns {boolean}
     */


    var stringInArraysHelper = function stringInArraysHelper(needleString, haystackArray) {
      return $.inArray(needleString, haystackArray) >= 0 && $.isArray(haystackArray);
    };
    /**
     * Check value is empty or not
     * @param value
     * @returns {boolean}
     */


    var isEmpty = function isEmpty(value) {
      if (typeof value == 'null' || typeof value == 'undefined') {
        return true;
      }

      if (typeof value == 'string') {
        return $.trim(value) === '';
      }

      if ((typeof value === 'undefined' ? 'undefined' : _typeof(value)) == 'object') {
        if ($.isArray(value)) {
          var _tmp = $.map(value, function (val, i) {
            return $.trim(val) === '' ? null : val;
          });

          return $.isEmptyObject(_tmp);
        } else {
          return $.isEmptyObject(value);
        }
      }

      return false;
    };
    /**
     * For Regular Expression Dependency
     * @param selector
     * @param depObject
     */


    this.typeRegExpDependency = function (selector, depObject) {
      if (typeof useEvent == 'undefined') {
        useEvent = false;
      }

      if (typeof $(parent).prop("tagName") == 'undefined') {
        return false;
      }

      var tag = $(parent).prop("tagName").toLowerCase();
      var type = $(parent).prop("type").toLowerCase();
      var name = tag + ':' + type;
      var value = $.trim($(parent).val());

      switch (name) {
        case "input:text":
        case "input:password":
        case "input:number":
        case "input:date":
        case "input:email":
        case "input:url":
        case "input:tel":
        case "textarea:textarea":
          var modifier = typeof depObject.modifier == 'undefined' ? '' : depObject.modifier;
          var pattern = new RegExp(depObject.pattern, modifier);

          if (pattern.test(value)) {
            $(element).show();
          } else {
            $(element).hide();
          }

          break;
      }

      if (useEvent) {
        $(document.body).on('input', $(parent), function (e) {
          typeRegExpDependency(element, depObject, parent, false);
        });
      }
    };
    /**
     * For Empty TextBox
     * @param selector
     * @param depObject
     */


    this.typeEmptyDependency = function (selector, depObject) {
      if (typeof $(selector).prop("tagName") == 'undefined') {
        return false;
      }

      var trigger = false;
      var tag = $(selector).prop("tagName").toLowerCase();
      var type = $(selector).prop("type").toLowerCase();
      var name = tag + ':' + type;
      var value = $(selector).val();

      switch (name) {
        case "input:text":
        case "input:password":
        case "input:number":
        case "input:date":
        case "input:email":
        case "input:url":
        case "input:tel":
        case "textarea:textarea":
        case "select:select-one":
          if ($.trim(value) === '') {
            trigger = true;
          }

          break;

        case "input:checkbox":
          if (!$(selector).is(':checked') && $.trim(value) === '') {
            trigger = true;
          }

          break;

        case "select:select-multiple":
          if (isEmpty(value)) {
            trigger = true;
          }

          break;
      }

      return trigger;
    };
    /**
     * For non empty TextBox
     * @param selector
     * @param depObject
     */


    this.typeNotEmptyDependency = function (selector, depObject) {
      if (typeof $(parent).prop("tagName") == 'undefined') {
        return false;
      }

      var trigger = false;
      var tag = $(parent).prop("tagName").toLowerCase();
      var type = $(parent).prop("type").toLowerCase();
      var name = tag + ':' + type;
      var value = $(parent).val();

      switch (name) {
        case "input:text":
        case "input:password":
        case "input:number":
        case "input:date":
        case "input:email":
        case "input:url":
        case "input:tel":
        case "textarea:textarea":
        case "select:select-one":
          if ($.trim(value) != '') {
            trigger = true;
          }

          break;

        case "input:checkbox":
          if ($(parent).is(':checked') && $.trim(value) != '') {
            trigger = true;
          }

          break;

        case "select:select-multiple":
          if (isEmpty(value)) {
            trigger = true;
          }

          break;
      }

      return trigger;
    };
    /**
     * TextBox value matched with value or with array values
     * @param selector
     * @param depObject
     */


    this.typeEqualDependency = function (selector, depObject) {
      if (typeof $(selector).prop("tagName") == 'undefined') {
        return false;
      }

      var trigger = false;
      var tag = $(selector).prop("tagName").toLowerCase();
      var type = $(selector).prop("type").toLowerCase();
      var name = tag + ':' + type;
      var val = $(selector).val();
      var equalLike = typeof depObject.like != 'undefined'; // show if empty?. default false

      depObject.empty = typeof depObject.empty == 'undefined' ? false : depObject.empty;
      depObject.strict = typeof depObject.strict == 'undefined' ? false : depObject.strict;

      if (equalLike) {
        var eqtag = $(depObject.like).prop("tagName").toLowerCase();
        var eqtype = $(depObject.like).prop("type").toLowerCase();
        var eqname = eqtag + ':' + eqtype;

        if (eqname == 'input:checkbox' || eqname == 'input:radio') {
          depObject.value = $(depObject.like + ':checked').map(function () {
            return this.value;
          }).get();
        } else {
          depObject.value = $(depObject.like).val();

          if (!showOnEmptyValue) {
            depObject.value = $.trim($(depObject.like).val()) == '' ? null : $(depObject.like).val();
          }
        }
      }

      switch (name) {
        case "input:text":
        case "input:password":
        case "input:number":
        case "input:date":
        case "input:email":
        case "input:url":
        case "input:tel":
        case "textarea:textarea":
        case "select:select-one":
          if ($.trim(val) === depObject.value) {
            trigger = true;
          } else if (stringInArraysHelper(val, depObject.value)) {
            trigger = true;
          } else {
            if ($.trim(val) === '' && depObject.empty) {
              trigger = true;
            }
          }

          break;

        case "input:checkbox":
        case "input:radio":
          var valList = $(selector + ':checked').map(function () {
            return this.value;
          }).get();

          if (valList === depObject.value) {
            trigger = true;
          } else if (stringInArraysHelper(valList, depObject.value)) {
            trigger = true;
          } else if (arrayInArraysHelper(valList, depObject.value, depObject.strict)) {
            trigger = true;
          } else {
            if (isEmpty(valList) && depObject.empty) {
              trigger = true;
            }
          }

          break;

        case "select:select-multiple":
          if (arrayInArraysHelper(value, depObject.value, depObject.strict)) {
            trigger = true;
          } else {
            if (val == null && depObject.empty) {
              trigger = true;
            }
          }

          break;
      }

      return trigger;
    };
    /**
     * TextBox value not equal with value or with array values
     * @param selector
     * @param depObject
     */


    this.typeNotEqualDependency = function (selector, depObject) {
      if (typeof $(selector).prop("tagName") == 'undefined') {
        return false;
      }

      var trigger = false;
      var tag = $(selector).prop("tagName").toLowerCase();
      var type = $(selector).prop("type").toLowerCase();
      var name = tag + ':' + type;
      var value = $(selector).val();
      var equalLike = typeof depObject.like == 'undefined' ? false : true;
      depObject.strict = typeof depObject.strict == 'undefined' ? false : depObject.strict; // show if empty? default is true

      depObject.empty = typeof depObject.empty == 'undefined' ? true : depObject.empty;

      if (equalLike) {
        var eqtag = $(depObject.like).prop("tagName").toLowerCase();
        var eqtype = $(depObject.like).prop("type").toLowerCase();
        var eqname = eqtag + ':' + eqtype;

        if (eqname == 'input:checkbox' || eqname == 'input:radio') {
          depObject.value = $(depObject.like + ':checked').map(function () {
            return this.value;
          }).get();
        } else {
          depObject.value = $(depObject.like).val();

          if (!showOnEmptyValue) {
            depObject.value = $.trim($(depObject.like).val()) == '' ? null : $(depObject.like).val();
          }
        }
      }

      switch (name) {
        case "input:text":
        case "input:password":
        case "input:number":
        case "input:date":
        case "input:email":
        case "input:url":
        case "input:tel":
        case "textarea:textarea":
        case "select:select-one":
          if (value == depObject.value) {
            trigger = false;
          } else if (stringInArraysHelper(value, depObject.value)) {
            trigger = false;
          } else {
            if ($.trim(value) == '' && !depObject.empty) {
              trigger = false;
            } else {
              trigger = true;
            }
          }

          break;

        case "input:checkbox":
        case "input:radio":
          value = $(selector + ':checked').map(function () {
            return this.value;
          }).get();

          if (typeof depObject.strict == 'undefined') {
            depObject.strict = false;
          }

          if (value == depObject.value) {
            trigger = false;
          } else if (stringInArraysHelper(value, depObject.value)) {
            trigger = false;
          } else if (arrayInArraysHelper(value, depObject.value, depObject.strict)) {
            trigger = false;
          } else {
            if (isEmpty(value) && !depObject.empty) {
              trigger = false;
            } else {
              trigger = true;
            }
          }

          break;

        case "select:select-multiple":
          if (arrayInArraysHelper(value, depObject.value, depObject.strict)) {
            trigger = false;
          } else {
            if (value == null && !depObject.empty) {
              trigger = false;
            } else {
              trigger = true;
            }
          }

          break;
      }

      return trigger;
    };
    /**
     * TextBox value compare
     * @param selector
     * @param depObject
     */


    this.typeCompareDependency = function (selector, depObject) {
      var trigger = false;

      if (typeof $(selector).prop("tagName") == 'undefined') {
        return false;
      }

      var tag = $(selector).prop("tagName").toLowerCase();
      var type = $(selector).prop("type").toLowerCase();
      var name = tag + ':' + type;
      var value = parseInt($(selector).val());
      depObject.value = parseInt(depObject.value);

      switch (depObject.sign) {
        case "<":
        case "lt":
        case "lessthen":
        case "less-then":
        case "LessThen":
          if (value < depObject.value) {
            trigger = true;
          }

          break;

        case "<=":
        case "lteq":
        case "lessthenequal":
        case "less-then-equal":
        case "LessThenEqual":
        case "eqlt":
          if (value <= depObject.value) {
            trigger = true;
          }

          break;

        case ">=":
        case "gteq":
        case "greaterthenequal":
        case "greater-then-equal":
        case "GreaterThenEqual":
        case "eqgt":
          if (value >= depObject.value) {
            trigger = true;
          }

          break;

        case ">":
        case "gt":
        case "greaterthen":
        case "greater-then":
        case "GreaterThen":
          if (value > depObject.value) {
            trigger = true;
          }

          break;
      }

      return trigger;
    };
    /**
     * TextBox value range
     * @param selector
     * @param depObject
     */


    this.typeRangeDependency = function (selector, depObject) {
      if (typeof $(selector).prop("tagName") == 'undefined') {
        return false;
      }

      var trigger = false;
      var tag = $(selector).prop("tagName").toLowerCase();
      var type = $(selector).prop("type").toLowerCase();
      var name = tag + ':' + type;
      var value = parseInt($(selector).val());
      var min, max;

      if ($.isArray(depObject.value)) {
        min = parseInt(depObject.value[0]);
        max = parseInt(depObject.value[1]);
      }

      if (typeof depObject.value == 'undefined') {
        min = parseInt(depObject.min);
        max = parseInt(depObject.max);
      }

      if (min < value && value < max) {
        trigger = true;
      }

      return trigger;
    };
    /**
     * TextBox value length
     * @param depObject
     * @param selector
     */


    this.typeLengthDependency = function (selector, depObject) {
      if (typeof $(parent).prop("tagName") == 'undefined') {
        return false;
      }

      var trigger = false;
      var tag = $(parent).prop("tagName").toLowerCase();
      var type = $(parent).prop("type").toLowerCase();
      var name = tag + ':' + type;
      var value = $(parent).val().length;
      depObject.value = parseInt(depObject.value);

      switch (depObject.sign) {
        case "<":
        case "lt":
        case "lessthen":
        case "less-then":
        case "LessThen":
          if (value < depObject.value) {
            trigger = true;
          }

          break;

        case "<=":
        case "lteq":
        case "lessthenequal":
        case "less-then-equal":
        case "LessThenEqual":
        case "eqlt":
          if (value <= depObject.value) {
            trigger = true;
          }

          break;

        case ">=":
        case "gteq":
        case "greaterthenequal":
        case "greater-then-equal":
        case "GreaterThenEqual":
        case "eqgt":
          if (value >= depObject.value) {
            trigger = true;
          }

          break;

        case ">":
        case "gt":
        case "greaterthen":
        case "greater-then":
        case "GreaterThen":
          if (value > depObject.value) {
            trigger = true;
          }

          break;
      }

      return trigger;
    };

    this.useRuleType = function (target, data) {
      var trigger = false;
      var that = this;
      $.each(data.rules, function (selector, depObject) {
        switch (depObject.type) {
          case "empty":
            trigger = that.typeEmptyDependency(selector, depObject);
            break;

          case "notempty":
          case "not-empty":
          case "notEmpty":
          case "!empty":
            trigger = that.typeNotEmptyDependency(selector, depObject);
            break;

          case "equal":
          case "==":
          case "=":
            trigger = that.typeEqualDependency(selector, depObject);
            break;

          case "!equal":
          case "notequal":
          case "!=":
          case "not-equal":
          case "notEqual":
            trigger = that.typeNotEqualDependency(selector, depObject);
            break;

          case "regexp":
          case "expression":
          case "reg":
          case "exp":
            trigger = that.typeRegExpDependency(selector, depObject);
            break;

          case "compare":
          case "comp":
            trigger = that.typeCompareDependency(selector, depObject);
            break;

          case "length":
          case "lng":
            trigger = that.typeLengthDependency(selector, depObject);
            break;

          case "range":
            trigger = that.typeRangeDependency(selector, depObject);
            break;
        }

        if (data.relation.toLocaleLowerCase() === 'and' && trigger === true) {
          return false;
        }

        if (data.relation.toLocaleLowerCase() === 'or' && trigger === false) {
          return false;
        }
      });

      if (trigger) {
        $(target).show('slow');
      } else {
        $(target).hide('slow');
      }
    };

    return this._targets.each(function () {
      var target = $(this);
      var data = target.data(that._settings.attribute.replace('data-', '').trim());

      if (data) {
        target.addClass('has-dependent-data');

        var _options = $.extend({
          'rules': {},
          'relation': 'or'
        }, data);

        var optionsKeys = Object.keys(_options.rules);

        if (optionsKeys.length) {
          that.useRuleType(target, _options);
          $(document.body).on('input change', $(optionsKeys.join(',')), function (e) {
            that.useRuleType(target, _options);
          });
        }
      }
    });
  };
})(jQuery);
