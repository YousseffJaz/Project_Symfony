'use strict';
(function(factory) {
  if (typeof define === "function" && define.amd) {
    define(["jquery"], factory);
  } else {
    if (typeof exports === "object") {
      factory(require("jquery"));
    } else {
      factory(jQuery);
    }
  }
})(function($, undefined) {
  /**
   * @return {?}
   */
  function timeZoneAbbreviation() {
    var n;
    var componentsStr;
    var formattedStr;
    var i;
    var len;
    var mainstack;
    var children;
    var str;
    /** @type {string} */
    componentsStr = (new Date).toString();
    /** @type {(Array<string>|number|string)} */
    formattedStr = ((children = componentsStr.split("(")[1]) != null ? children.slice(0, -1) : 0) || componentsStr.split(" ");
    if (formattedStr instanceof Array) {
      /** @type {!Array} */
      mainstack = [];
      /** @type {number} */
      i = 0;
      /** @type {number} */
      len = formattedStr.length;
      for (; i < len; i++) {
        str = formattedStr[i];
        if ((n = (children = str.match(/\b[A-Z]+\b/)) !== null) ? children[0] : 0) {
          mainstack.push(n);
        }
      }
      formattedStr = mainstack.pop();
    }
    return formattedStr;
  }
  /**
   * @return {?}
   */
  function UTCDate() {
    return new Date(Date.UTC.apply(Date, arguments));
  }
  if (!("indexOf" in Array.prototype)) {
    /**
     * @param {string} item
     * @param {number=} i
     * @return {number}
     * @template T
     */
    Array.prototype.indexOf = function(item, i) {
      if (i === undefined) {
        /** @type {number} */
        i = 0;
      }
      if (i < 0) {
        /** @type {number} */
        i = i + this.length;
      }
      if (i < 0) {
        /** @type {number} */
        i = 0;
      }
      /** @type {number} */
      var l = this.length;
      for (; i < l; i++) {
        if (i in this && this[i] === item) {
          return i;
        }
      }
      return -1;
    };
  }
  /**
   * @param {?} element
   * @param {!Object} options
   * @return {undefined}
   */
  var Datetimepicker = function(element, options) {
    var $trashTreeContextMenu = this;
    this.element = $(element);
    this.container = options.container || "body";
    this.language = options.language || this.element.data("date-language") || "en";
    this.language = this.language in dates ? this.language : this.language.split("-")[0];
    this.language = this.language in dates ? this.language : "en";
    this.isRTL = dates[this.language].rtl || false;
    this.formatType = options.formatType || this.element.data("format-type") || "standard";
    this.format = DPGlobal.parseFormat(options.format || this.element.data("date-format") || dates[this.language].format || DPGlobal.getDefaultFormat(this.formatType, "input"), this.formatType);
    /** @type {boolean} */
    this.isInline = false;
    /** @type {boolean} */
    this.isVisible = false;
    this.isInput = this.element.is("input");
    this.fontAwesome = options.fontAwesome || this.element.data("font-awesome") || false;
    this.bootcssVer = options.bootcssVer || (this.isInput ? this.element.is(".form-control") ? 3 : 2 : this.bootcssVer = this.element.is(".input-group") ? 3 : 2);
    this.component = this.element.is(".date") ? this.bootcssVer === 3 ? this.element.find(".input-group-addon .glyphicon-th, .input-group-addon .glyphicon-time, .input-group-addon .glyphicon-remove, .input-group-addon .glyphicon-calendar, .input-group-addon .fa-calendar, .input-group-addon .fa-clock-o").parent() : this.element.find(".add-on .icon-th, .add-on .icon-time, .add-on .icon-calendar, .add-on .fa-calendar, .add-on .fa-clock-o").parent() : false;
    this.componentReset = this.element.is(".date") ? this.bootcssVer === 3 ? this.element.find(".input-group-addon .glyphicon-remove, .input-group-addon .fa-times").parent() : this.element.find(".add-on .icon-remove, .add-on .fa-times").parent() : false;
    this.hasInput = this.component && this.element.find("input").length;
    if (this.component && this.component.length === 0) {
      /** @type {boolean} */
      this.component = false;
    }
    this.linkField = options.linkField || this.element.data("link-field") || false;
    this.linkFormat = DPGlobal.parseFormat(options.linkFormat || this.element.data("link-format") || DPGlobal.getDefaultFormat(this.formatType, "link"), this.formatType);
    this.minuteStep = options.minuteStep || this.element.data("minute-step") || 5;
    this.pickerPosition = options.pickerPosition || this.element.data("picker-position") || "bottom-right";
    this.showMeridian = options.showMeridian || this.element.data("show-meridian") || false;
    this.initialDate = options.initialDate || new Date;
    this.zIndex = options.zIndex || this.element.data("z-index") || undefined;
    this.title = typeof options.title === "undefined" ? false : options.title;
    this.timezone = options.timezone || timeZoneAbbreviation();
    this.icons = {
      leftArrow : this.fontAwesome ? "fa-arrow-left" : this.bootcssVer === 3 ? "glyphicon-arrow-left" : "icon-arrow-left",
      rightArrow : this.fontAwesome ? "fa-arrow-right" : this.bootcssVer === 3 ? "glyphicon-arrow-right" : "icon-arrow-right"
    };
    /** @type {string} */
    this.icontype = this.fontAwesome ? "fa" : "glyphicon";
    this._attachEvents();
    /**
     * @param {!Event} e
     * @return {undefined}
     */
    this.clickedOutside = function(e) {
      if ($(e.target).closest(".datetimepicker").length === 0) {
        $trashTreeContextMenu.hide();
      }
    };
    /** @type {string} */
    this.formatViewType = "datetime";
    if ("formatViewType" in options) {
      this.formatViewType = options.formatViewType;
    } else {
      if ("formatViewType" in this.element.data()) {
        this.formatViewType = this.element.data("formatViewType");
      }
    }
    /** @type {number} */
    this.minView = 0;
    if ("minView" in options) {
      this.minView = options.minView;
    } else {
      if ("minView" in this.element.data()) {
        this.minView = this.element.data("min-view");
      }
    }
    this.minView = DPGlobal.convertViewMode(this.minView);
    /** @type {number} */
    this.maxView = DPGlobal.modes.length - 1;
    if ("maxView" in options) {
      this.maxView = options.maxView;
    } else {
      if ("maxView" in this.element.data()) {
        this.maxView = this.element.data("max-view");
      }
    }
    this.maxView = DPGlobal.convertViewMode(this.maxView);
    /** @type {boolean} */
    this.wheelViewModeNavigation = false;
    if ("wheelViewModeNavigation" in options) {
      this.wheelViewModeNavigation = options.wheelViewModeNavigation;
    } else {
      if ("wheelViewModeNavigation" in this.element.data()) {
        this.wheelViewModeNavigation = this.element.data("view-mode-wheel-navigation");
      }
    }
    /** @type {boolean} */
    this.wheelViewModeNavigationInverseDirection = false;
    if ("wheelViewModeNavigationInverseDirection" in options) {
      this.wheelViewModeNavigationInverseDirection = options.wheelViewModeNavigationInverseDirection;
    } else {
      if ("wheelViewModeNavigationInverseDirection" in this.element.data()) {
        this.wheelViewModeNavigationInverseDirection = this.element.data("view-mode-wheel-navigation-inverse-dir");
      }
    }
    /** @type {number} */
    this.wheelViewModeNavigationDelay = 100;
    if ("wheelViewModeNavigationDelay" in options) {
      this.wheelViewModeNavigationDelay = options.wheelViewModeNavigationDelay;
    } else {
      if ("wheelViewModeNavigationDelay" in this.element.data()) {
        this.wheelViewModeNavigationDelay = this.element.data("view-mode-wheel-navigation-delay");
      }
    }
    /** @type {number} */
    this.startViewMode = 2;
    if ("startView" in options) {
      this.startViewMode = options.startView;
    } else {
      if ("startView" in this.element.data()) {
        this.startViewMode = this.element.data("start-view");
      }
    }
    this.startViewMode = DPGlobal.convertViewMode(this.startViewMode);
    this.viewMode = this.startViewMode;
    this.viewSelect = this.minView;
    if ("viewSelect" in options) {
      this.viewSelect = options.viewSelect;
    } else {
      if ("viewSelect" in this.element.data()) {
        this.viewSelect = this.element.data("view-select");
      }
    }
    this.viewSelect = DPGlobal.convertViewMode(this.viewSelect);
    /** @type {boolean} */
    this.forceParse = true;
    if ("forceParse" in options) {
      this.forceParse = options.forceParse;
    } else {
      if ("dateForceParse" in this.element.data()) {
        this.forceParse = this.element.data("date-force-parse");
      }
    }
    /** @type {string} */
    var template = this.bootcssVer === 3 ? DPGlobal.templateV3 : DPGlobal.template;
    for (; template.indexOf("{iconType}") !== -1;) {
      /** @type {string} */
      template = template.replace("{iconType}", this.icontype);
    }
    for (; template.indexOf("{leftArrow}") !== -1;) {
      /** @type {string} */
      template = template.replace("{leftArrow}", this.icons.leftArrow);
    }
    for (; template.indexOf("{rightArrow}") !== -1;) {
      /** @type {string} */
      template = template.replace("{rightArrow}", this.icons.rightArrow);
    }
    this.picker = $(template).appendTo(this.isInline ? this.element : this.container).on({
      click : $.proxy(this.click, this),
      mousedown : $.proxy(this.mousedown, this)
    });
    if (this.wheelViewModeNavigation) {
      if ($.fn.mousewheel) {
        this.picker.on({
          mousewheel : $.proxy(this.mousewheel, this)
        });
      } else {
        console.log("Mouse Wheel event is not supported. Please include the jQuery Mouse Wheel plugin before enabling this option");
      }
    }
    if (this.isInline) {
      this.picker.addClass("datetimepicker-inline");
    } else {
      this.picker.addClass("datetimepicker-dropdown-" + this.pickerPosition + " dropdown-menu");
    }
    if (this.isRTL) {
      this.picker.addClass("datetimepicker-rtl");
      /** @type {string} */
      var selector = this.bootcssVer === 3 ? ".prev span, .next span" : ".prev i, .next i";
      this.picker.find(selector).toggleClass(this.icons.leftArrow + " " + this.icons.rightArrow);
    }
    $(document).on("mousedown touchend", this.clickedOutside);
    /** @type {boolean} */
    this.autoclose = false;
    if ("autoclose" in options) {
      this.autoclose = options.autoclose;
    } else {
      if ("dateAutoclose" in this.element.data()) {
        this.autoclose = this.element.data("date-autoclose");
      }
    }
    /** @type {boolean} */
    this.keyboardNavigation = true;
    if ("keyboardNavigation" in options) {
      this.keyboardNavigation = options.keyboardNavigation;
    } else {
      if ("dateKeyboardNavigation" in this.element.data()) {
        this.keyboardNavigation = this.element.data("date-keyboard-navigation");
      }
    }
    this.todayBtn = options.todayBtn || this.element.data("date-today-btn") || false;
    this.clearBtn = options.clearBtn || this.element.data("date-clear-btn") || false;
    this.todayHighlight = options.todayHighlight || this.element.data("date-today-highlight") || false;
    /** @type {number} */
    this.weekStart = 0;
    if (typeof options.weekStart !== "undefined") {
      this.weekStart = options.weekStart;
    } else {
      if (typeof this.element.data("date-weekstart") !== "undefined") {
        this.weekStart = this.element.data("date-weekstart");
      } else {
        if (typeof dates[this.language].weekStart !== "undefined") {
          this.weekStart = dates[this.language].weekStart;
        }
      }
    }
    /** @type {number} */
    this.weekStart = this.weekStart % 7;
    /** @type {number} */
    this.weekEnd = (this.weekStart + 6) % 7;
    /**
     * @param {?} date
     * @return {?}
     */
    this.onRenderDay = function(date) {
      var ea = (options.onRenderDay || function() {
        return [];
      })(date);
      if (typeof ea === "string") {
        /** @type {!Array} */
        ea = [ea];
      }
      /** @type {!Array} */
      var ranges = ["day"];
      return ranges.concat(ea ? ea : []);
    };
    /**
     * @param {!Date} vec
     * @return {?}
     */
    this.onRenderHour = function(vec) {
      var ea = (options.onRenderHour || function() {
        return [];
      })(vec);
      /** @type {!Array} */
      var ranges = ["hour"];
      if (typeof ea === "string") {
        /** @type {!Array} */
        ea = [ea];
      }
      return ranges.concat(ea ? ea : []);
    };
    /**
     * @param {!Date} date
     * @return {?}
     */
    this.onRenderMinute = function(date) {
      var render = (options.onRenderMinute || function() {
        return [];
      })(date);
      /** @type {!Array} */
      var res = ["minute"];
      if (typeof render === "string") {
        /** @type {!Array} */
        render = [render];
      }
      if (date < this.startDate || date > this.endDate) {
        res.push("disabled");
      } else {
        if (Math.floor(this.date.getUTCMinutes() / this.minuteStep) === Math.floor(date.getUTCMinutes() / this.minuteStep)) {
          res.push("active");
        }
      }
      return res.concat(render ? render : []);
    };
    /**
     * @param {!Date} date
     * @return {?}
     */
    this.onRenderYear = function(date) {
      var value = (options.onRenderYear || function() {
        return [];
      })(date);
      /** @type {!Array} */
      var cell = ["year"];
      if (typeof value === "string") {
        /** @type {!Array} */
        value = [value];
      }
      if (this.date.getUTCFullYear() === date.getUTCFullYear()) {
        cell.push("active");
      }
      var currentYear = date.getUTCFullYear();
      var endYear = this.endDate.getUTCFullYear();
      if (date < this.startDate || currentYear > endYear) {
        cell.push("disabled");
      }
      return cell.concat(value ? value : []);
    };
    /**
     * @param {!Date} vec
     * @return {?}
     */
    this.onRenderMonth = function(vec) {
      var ea = (options.onRenderMonth || function() {
        return [];
      })(vec);
      /** @type {!Array} */
      var ranges = ["month"];
      if (typeof ea === "string") {
        /** @type {!Array} */
        ea = [ea];
      }
      return ranges.concat(ea ? ea : []);
    };
    /** @type {!Date} */
    this.startDate = new Date(-8639968443048E3);
    /** @type {!Date} */
    this.endDate = new Date(8639968443048E3);
    /** @type {!Array} */
    this.datesDisabled = [];
    /** @type {!Array} */
    this.daysOfWeekDisabled = [];
    this.setStartDate(options.startDate || this.element.data("date-startdate"));
    this.setEndDate(options.endDate || this.element.data("date-enddate"));
    this.setDatesDisabled(options.datesDisabled || this.element.data("date-dates-disabled"));
    this.setDaysOfWeekDisabled(options.daysOfWeekDisabled || this.element.data("date-days-of-week-disabled"));
    this.setMinutesDisabled(options.minutesDisabled || this.element.data("date-minute-disabled"));
    this.setHoursDisabled(options.hoursDisabled || this.element.data("date-hour-disabled"));
    this.fillDow();
    this.fillMonths();
    this.update();
    this.showMode();
    if (this.isInline) {
      this.show();
    }
  };
  Datetimepicker.prototype = {
    constructor : Datetimepicker,
    _events : [],
    _attachEvents : function() {
      this._detachEvents();
      if (this.isInput) {
        /** @type {!Array} */
        this._events = [[this.element, {
          focus : $.proxy(this.show, this),
          keyup : $.proxy(this.update, this),
          keydown : $.proxy(this.keydown, this)
        }]];
      } else {
        if (this.component && this.hasInput) {
          /** @type {!Array} */
          this._events = [[this.element.find("input"), {
            focus : $.proxy(this.show, this),
            keyup : $.proxy(this.update, this),
            keydown : $.proxy(this.keydown, this)
          }], [this.component, {
            click : $.proxy(this.show, this)
          }]];
          if (this.componentReset) {
            this._events.push([this.componentReset, {
              click : $.proxy(this.reset, this)
            }]);
          }
        } else {
          if (this.element.is("div")) {
            /** @type {boolean} */
            this.isInline = true;
          } else {
            /** @type {!Array} */
            this._events = [[this.element, {
              click : $.proxy(this.show, this)
            }]];
          }
        }
      }
      /** @type {number} */
      var i = 0;
      var app;
      var $closingAreaRight;
      for (; i < this._events.length; i++) {
        app = this._events[i][0];
        $closingAreaRight = this._events[i][1];
        app.on($closingAreaRight);
      }
    },
    _detachEvents : function() {
      /** @type {number} */
      var i = 0;
      var $this;
      var namespacedEvt;
      for (; i < this._events.length; i++) {
        $this = this._events[i][0];
        namespacedEvt = this._events[i][1];
        $this.off(namespacedEvt);
      }
      /** @type {!Array} */
      this._events = [];
    },
    show : function(event) {
      this.picker.show();
      this.height = this.component ? this.component.outerHeight() : this.element.outerHeight();
      if (this.forceParse) {
        this.update();
      }
      this.place();
      $(window).on("resize", $.proxy(this.place, this));
      if (event) {
        event.stopPropagation();
        event.preventDefault();
      }
      /** @type {boolean} */
      this.isVisible = true;
      this.element.trigger({
        type : "show",
        date : this.date
      });
    },
    hide : function() {
      if (!this.isVisible) {
        return;
      }
      if (this.isInline) {
        return;
      }
      this.picker.hide();
      $(window).off("resize", this.place);
      this.viewMode = this.startViewMode;
      this.showMode();
      if (!this.isInput) {
        $(document).off("mousedown", this.hide);
      }
      if (this.forceParse && (this.isInput && this.element.val() || this.hasInput && this.element.find("input").val())) {
        this.setValue();
      }
      /** @type {boolean} */
      this.isVisible = false;
      this.element.trigger({
        type : "hide",
        date : this.date
      });
    },
    remove : function() {
      this._detachEvents();
      $(document).off("mousedown", this.clickedOutside);
      this.picker.remove();
      delete this.picker;
      delete this.element.data().datetimepicker;
    },
    getDate : function() {
      var d = this.getUTCDate();
      if (d === null) {
        return null;
      }
      return new Date(d.getTime() + d.getTimezoneOffset() * 6E4);
    },
    getUTCDate : function() {
      return this.date;
    },
    getInitialDate : function() {
      return this.initialDate;
    },
    setInitialDate : function(initialDate) {
      /** @type {!Object} */
      this.initialDate = initialDate;
    },
    setDate : function(utcDate) {
      this.setUTCDate(new Date(utcDate.getTime() - utcDate.getTimezoneOffset() * 6E4));
    },
    setUTCDate : function(date) {
      if (date >= this.startDate && date <= this.endDate) {
        /** @type {!Object} */
        this.date = date;
        this.setValue();
        this.viewDate = this.date;
        this.fill();
      } else {
        this.element.trigger({
          type : "outOfRange",
          date : date,
          startDate : this.startDate,
          endDate : this.endDate
        });
      }
    },
    setFormat : function(format) {
      this.format = DPGlobal.parseFormat(format, this.formatType);
      var element;
      if (this.isInput) {
        element = this.element;
      } else {
        if (this.component) {
          element = this.element.find("input");
        }
      }
      if (element && element.val()) {
        this.setValue();
      }
    },
    setValue : function() {
      var formatted = this.getFormattedDate();
      if (!this.isInput) {
        if (this.component) {
          this.element.find("input").val(formatted);
        }
        this.element.data("date", formatted);
      } else {
        this.element.val(formatted);
      }
      if (this.linkField) {
        $("#" + this.linkField).val(this.getFormattedDate(this.linkFormat));
      }
    },
    getFormattedDate : function(format) {
      format = format || this.format;
      return DPGlobal.formatDate(this.date, format, this.language, this.formatType, this.timezone);
    },
    setStartDate : function(startDate) {
      this.startDate = startDate || this.startDate;
      if (this.startDate.valueOf() !== 8639968443048E3) {
        this.startDate = DPGlobal.parseDate(this.startDate, this.format, this.language, this.formatType, this.timezone);
      }
      this.update();
      this.updateNavArrows();
    },
    setEndDate : function(endDate) {
      this.endDate = endDate || this.endDate;
      if (this.endDate.valueOf() !== 8639968443048E3) {
        this.endDate = DPGlobal.parseDate(this.endDate, this.format, this.language, this.formatType, this.timezone);
      }
      this.update();
      this.updateNavArrows();
    },
    setDatesDisabled : function(datesDisabled) {
      this.datesDisabled = datesDisabled || [];
      if (!$.isArray(this.datesDisabled)) {
        this.datesDisabled = this.datesDisabled.split(/,\s*/);
      }
      var mThis = this;
      this.datesDisabled = $.map(this.datesDisabled, function(d) {
        return DPGlobal.parseDate(d, mThis.format, mThis.language, mThis.formatType, mThis.timezone).toDateString();
      });
      this.update();
      this.updateNavArrows();
    },
    setTitle : function(selector, value) {
      return this.picker.find(selector).find("th:eq(1)").text(this.title === false ? value : this.title);
    },
    setDaysOfWeekDisabled : function(daysOfWeekDisabled) {
      this.daysOfWeekDisabled = daysOfWeekDisabled || [];
      if (!$.isArray(this.daysOfWeekDisabled)) {
        this.daysOfWeekDisabled = this.daysOfWeekDisabled.split(/,\s*/);
      }
      this.daysOfWeekDisabled = $.map(this.daysOfWeekDisabled, function(id_local) {
        return parseInt(id_local, 10);
      });
      this.update();
      this.updateNavArrows();
    },
    setMinutesDisabled : function(minutesDisabled) {
      this.minutesDisabled = minutesDisabled || [];
      if (!$.isArray(this.minutesDisabled)) {
        this.minutesDisabled = this.minutesDisabled.split(/,\s*/);
      }
      this.minutesDisabled = $.map(this.minutesDisabled, function(id_local) {
        return parseInt(id_local, 10);
      });
      this.update();
      this.updateNavArrows();
    },
    setHoursDisabled : function(hoursDisabled) {
      this.hoursDisabled = hoursDisabled || [];
      if (!$.isArray(this.hoursDisabled)) {
        this.hoursDisabled = this.hoursDisabled.split(/,\s*/);
      }
      this.hoursDisabled = $.map(this.hoursDisabled, function(id_local) {
        return parseInt(id_local, 10);
      });
      this.update();
      this.updateNavArrows();
    },
    place : function() {
      if (this.isInline) {
        return;
      }
      if (!this.zIndex) {
        /** @type {number} */
        var index_highest = 0;
        $("div").each(function() {
          /** @type {number} */
          var index_current = parseInt($(this).css("zIndex"), 10);
          if (index_current > index_highest) {
            /** @type {number} */
            index_highest = index_current;
          }
        });
        this.zIndex = index_highest + 10;
      }
      var offset;
      var y2;
      var x1;
      var imgOfs;
      if (this.container instanceof $) {
        imgOfs = this.container.offset();
      } else {
        imgOfs = $(this.container).offset();
      }
      if (this.component) {
        offset = this.component.offset();
        x1 = offset.left;
        if (this.pickerPosition === "bottom-left" || this.pickerPosition === "top-left") {
          x1 = x1 + (this.component.outerWidth() - this.picker.outerWidth());
        }
      } else {
        offset = this.element.offset();
        x1 = offset.left;
        if (this.pickerPosition === "bottom-left" || this.pickerPosition === "top-left") {
          x1 = x1 + (this.element.outerWidth() - this.picker.outerWidth());
        }
      }
      /** @type {number} */
      var x2 = document.body.clientWidth || window.innerWidth;
      if (x1 + 220 > x2) {
        /** @type {number} */
        x1 = x2 - 220;
      }
      if (this.pickerPosition === "top-left" || this.pickerPosition === "top-right") {
        /** @type {number} */
        y2 = offset.top - this.picker.outerHeight();
      } else {
        y2 = offset.top + this.height;
      }
      /** @type {number} */
      y2 = y2 - imgOfs.top;
      /** @type {number} */
      x1 = x1 - imgOfs.left;
      this.picker.css({
        top : y2,
        left : x1,
        zIndex : this.zIndex
      });
    },
    hour_minute : "^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]",
    update : function() {
      var date;
      /** @type {boolean} */
      var j = false;
      if (arguments && arguments.length && (typeof arguments[0] === "string" || arguments[0] instanceof Date)) {
        date = arguments[0];
        /** @type {boolean} */
        j = true;
      } else {
        date = (this.isInput ? this.element.val() : this.element.find("input").val()) || this.element.data("date") || this.initialDate;
        if (typeof date === "string") {
          /** @type {string} */
          date = date.replace(/^\s+|\s+$/g, "");
        }
      }
      if (!date) {
        /** @type {!Date} */
        date = new Date;
        /** @type {boolean} */
        j = false;
      }
      if (typeof date === "string") {
        if ((new RegExp(this.hour_minute)).test(date) || (new RegExp(this.hour_minute + ":[0-5][0-9]")).test(date)) {
          date = this.getDate();
        }
      }
      this.date = DPGlobal.parseDate(date, this.format, this.language, this.formatType, this.timezone);
      if (j) {
        this.setValue();
      }
      if (this.date < this.startDate) {
        /** @type {!Date} */
        this.viewDate = new Date(this.startDate);
      } else {
        if (this.date > this.endDate) {
          /** @type {!Date} */
          this.viewDate = new Date(this.endDate);
        } else {
          /** @type {!Date} */
          this.viewDate = new Date(this.date);
        }
      }
      this.fill();
    },
    fillDow : function() {
      var dowCnt = this.weekStart;
      /** @type {string} */
      var lineNumber = "<tr>";
      for (; dowCnt < this.weekStart + 7;) {
        /** @type {string} */
        lineNumber = lineNumber + ('<th class="dow">' + dates[this.language].daysMin[dowCnt++ % 7] + "</th>");
      }
      /** @type {string} */
      lineNumber = lineNumber + "</tr>";
      this.picker.find(".datetimepicker-days thead").append(lineNumber);
    },
    fillMonths : function() {
      /** @type {string} */
      var scrolltable = "";
      /** @type {!Date} */
      var d = new Date(this.viewDate);
      /** @type {number} */
      var month = 0;
      for (; month < 12; month++) {
        d.setUTCMonth(month);
        var classes = this.onRenderMonth(d);
        /** @type {string} */
        scrolltable = scrolltable + ('<span class="' + classes.join(" ") + '">' + dates[this.language].monthsShort[month] + "</span>");
      }
      this.picker.find(".datetimepicker-months td").html(scrolltable);
    },
    fill : function() {
      if (!this.date || !this.viewDate) {
        return;
      }
      /** @type {!Date} */
      var d = new Date(this.viewDate);
      /** @type {number} */
      var year = d.getUTCFullYear();
      /** @type {number} */
      var month = d.getUTCMonth();
      /** @type {number} */
      var dayMonth = d.getUTCDate();
      /** @type {number} */
      var minuteNow = d.getUTCHours();
      var startYear = this.startDate.getUTCFullYear();
      var placeHolderText = this.startDate.getUTCMonth();
      var endYear = this.endDate.getUTCFullYear();
      var x = this.endDate.getUTCMonth() + 1;
      var q = (new UTCDate(this.date.getUTCFullYear(), this.date.getUTCMonth(), this.date.getUTCDate())).valueOf();
      /** @type {!Date} */
      var dCurrent = new Date;
      this.setTitle(".datetimepicker-days", dates[this.language].months[month] + " " + year);
      if (this.formatViewType === "time") {
        var formatted = this.getFormattedDate();
        this.setTitle(".datetimepicker-hours", formatted);
        this.setTitle(".datetimepicker-minutes", formatted);
      } else {
        this.setTitle(".datetimepicker-hours", dayMonth + " " + dates[this.language].months[month] + " " + year);
        this.setTitle(".datetimepicker-minutes", dayMonth + " " + dates[this.language].months[month] + " " + year);
      }
      this.picker.find("tfoot th.today").text(dates[this.language].today || dates.en.today).toggle(this.todayBtn !== false);
      this.picker.find("tfoot th.clear").text(dates[this.language].clear || dates.en.clear).toggle(this.clearBtn !== false);
      this.updateNavArrows();
      this.fillMonths();
      var prevMonth = UTCDate(year, month - 1, 28, 0, 0, 0, 0);
      var day = DPGlobal.getDaysInMonth(prevMonth.getUTCFullYear(), prevMonth.getUTCMonth());
      prevMonth.setUTCDate(day);
      prevMonth.setUTCDate(day - (prevMonth.getUTCDay() - this.weekStart + 7) % 7);
      /** @type {!Date} */
      var nextMonth = new Date(prevMonth);
      nextMonth.setUTCDate(nextMonth.getUTCDate() + 42);
      /** @type {number} */
      nextMonth = nextMonth.valueOf();
      /** @type {!Array} */
      var groups = [];
      var classes;
      for (; prevMonth.valueOf() < nextMonth;) {
        if (prevMonth.getUTCDay() === this.weekStart) {
          groups.push("<tr>");
        }
        classes = this.onRenderDay(prevMonth);
        if (prevMonth.getUTCFullYear() < year || prevMonth.getUTCFullYear() === year && prevMonth.getUTCMonth() < month) {
          classes.push("old");
        } else {
          if (prevMonth.getUTCFullYear() > year || prevMonth.getUTCFullYear() === year && prevMonth.getUTCMonth() > month) {
            classes.push("new");
          }
        }
        if (this.todayHighlight && prevMonth.getUTCFullYear() === dCurrent.getFullYear() && prevMonth.getUTCMonth() === dCurrent.getMonth() && prevMonth.getUTCDate() === dCurrent.getDate()) {
          classes.push("today");
        }
        if (prevMonth.valueOf() === q) {
          classes.push("active");
        }
        if (prevMonth.valueOf() + 864E5 <= this.startDate || prevMonth.valueOf() > this.endDate || $.inArray(prevMonth.getUTCDay(), this.daysOfWeekDisabled) !== -1 || $.inArray(prevMonth.toDateString(), this.datesDisabled) !== -1) {
          classes.push("disabled");
        }
        groups.push('<td class="' + classes.join(" ") + '">' + prevMonth.getUTCDate() + "</td>");
        if (prevMonth.getUTCDay() === this.weekEnd) {
          groups.push("</tr>");
        }
        prevMonth.setUTCDate(prevMonth.getUTCDate() + 1);
      }
      this.picker.find(".datetimepicker-days tbody").empty().append(groups.join(""));
      /** @type {!Array} */
      groups = [];
      /** @type {string} */
      var u = "";
      /** @type {string} */
      var sub = "";
      /** @type {string} */
      var s = "";
      var hoursDisabled = this.hoursDisabled || [];
      /** @type {!Date} */
      d = new Date(this.viewDate);
      /** @type {number} */
      var i = 0;
      for (; i < 24; i++) {
        d.setUTCHours(i);
        classes = this.onRenderHour(d);
        if (hoursDisabled.indexOf(i) !== -1) {
          classes.push("disabled");
        }
        var actual = UTCDate(year, month, dayMonth, i);
        if (actual.valueOf() + 36E5 <= this.startDate || actual.valueOf() > this.endDate) {
          classes.push("disabled");
        } else {
          if (minuteNow === i) {
            classes.push("active");
          }
        }
        if (this.showMeridian && dates[this.language].meridiem.length === 2) {
          sub = i < 12 ? dates[this.language].meridiem[0] : dates[this.language].meridiem[1];
          if (sub !== s) {
            if (s !== "") {
              groups.push("</fieldset>");
            }
            groups.push('<fieldset class="hour"><legend>' + sub.toUpperCase() + "</legend>");
          }
          s = sub;
          /** @type {number} */
          u = i % 12 ? i % 12 : 12;
          if (i < 12) {
            classes.push("hour_am");
          } else {
            classes.push("hour_pm");
          }
          groups.push('<span class="' + classes.join(" ") + '">' + u + "</span>");
          if (i === 23) {
            groups.push("</fieldset>");
          }
        } else {
          /** @type {string} */
          u = i + ":00";
          groups.push('<span class="' + classes.join(" ") + '">' + u + "</span>");
        }
      }
      this.picker.find(".datetimepicker-hours td").html(groups.join(""));
      /** @type {!Array} */
      groups = [];
      /** @type {string} */
      u = "";
      /** @type {string} */
      sub = "";
      /** @type {string} */
      s = "";
      var minutesDisabled = this.minutesDisabled || [];
      /** @type {!Date} */
      d = new Date(this.viewDate);
      /** @type {number} */
      i = 0;
      for (; i < 60; i = i + this.minuteStep) {
        if (minutesDisabled.indexOf(i) !== -1) {
          continue;
        }
        d.setUTCMinutes(i);
        d.setUTCSeconds(0);
        classes = this.onRenderMinute(d);
        if (this.showMeridian && dates[this.language].meridiem.length === 2) {
          sub = minuteNow < 12 ? dates[this.language].meridiem[0] : dates[this.language].meridiem[1];
          if (sub !== s) {
            if (s !== "") {
              groups.push("</fieldset>");
            }
            groups.push('<fieldset class="minute"><legend>' + sub.toUpperCase() + "</legend>");
          }
          s = sub;
          /** @type {number} */
          u = minuteNow % 12 ? minuteNow % 12 : 12;
          groups.push('<span class="' + classes.join(" ") + '">' + u + ":" + (i < 10 ? "0" + i : i) + "</span>");
          if (i === 59) {
            groups.push("</fieldset>");
          }
        } else {
          /** @type {string} */
          u = i + ":00";
          groups.push('<span class="' + classes.join(" ") + '">' + minuteNow + ":" + (i < 10 ? "0" + i : i) + "</span>");
        }
      }
      this.picker.find(".datetimepicker-minutes td").html(groups.join(""));
      var key = this.date.getUTCFullYear();
      var element = this.setTitle(".datetimepicker-months", year).end().find(".month").removeClass("active");
      if (key === year) {
        element.eq(this.date.getUTCMonth()).addClass("active");
      }
      if (year < startYear || year > endYear) {
        element.addClass("disabled");
      }
      if (year === startYear) {
        element.slice(0, placeHolderText).addClass("disabled");
      }
      if (year === endYear) {
        element.slice(x).addClass("disabled");
      }
      /** @type {string} */
      groups = "";
      /** @type {number} */
      year = parseInt(year / 10, 10) * 10;
      var text = this.setTitle(".datetimepicker-years", year + "-" + (year + 9)).end().find("td");
      /** @type {number} */
      year = year - 1;
      /** @type {!Date} */
      d = new Date(this.viewDate);
      /** @type {number} */
      i = -1;
      for (; i < 11; i++) {
        d.setUTCFullYear(year);
        classes = this.onRenderYear(d);
        if (i === -1 || i === 10) {
          classes.push(old);
        }
        /** @type {string} */
        groups = groups + ('<span class="' + classes.join(" ") + '">' + year + "</span>");
        /** @type {number} */
        year = year + 1;
      }
      text.html(groups);
      this.place();
    },
    updateNavArrows : function() {
      /** @type {!Date} */
      var d = new Date(this.viewDate);
      /** @type {number} */
      var annoStart = d.getUTCFullYear();
      /** @type {number} */
      var annoEnd = d.getUTCMonth();
      /** @type {number} */
      var j = d.getUTCDate();
      /** @type {number} */
      var i = d.getUTCHours();
      switch(this.viewMode) {
        case 0:
          if (annoStart <= this.startDate.getUTCFullYear() && annoEnd <= this.startDate.getUTCMonth() && j <= this.startDate.getUTCDate() && i <= this.startDate.getUTCHours()) {
            this.picker.find(".prev").css({
              visibility : "hidden"
            });
          } else {
            this.picker.find(".prev").css({
              visibility : "visible"
            });
          }
          if (annoStart >= this.endDate.getUTCFullYear() && annoEnd >= this.endDate.getUTCMonth() && j >= this.endDate.getUTCDate() && i >= this.endDate.getUTCHours()) {
            this.picker.find(".next").css({
              visibility : "hidden"
            });
          } else {
            this.picker.find(".next").css({
              visibility : "visible"
            });
          }
          break;
        case 1:
          if (annoStart <= this.startDate.getUTCFullYear() && annoEnd <= this.startDate.getUTCMonth() && j <= this.startDate.getUTCDate()) {
            this.picker.find(".prev").css({
              visibility : "hidden"
            });
          } else {
            this.picker.find(".prev").css({
              visibility : "visible"
            });
          }
          if (annoStart >= this.endDate.getUTCFullYear() && annoEnd >= this.endDate.getUTCMonth() && j >= this.endDate.getUTCDate()) {
            this.picker.find(".next").css({
              visibility : "hidden"
            });
          } else {
            this.picker.find(".next").css({
              visibility : "visible"
            });
          }
          break;
        case 2:
          if (annoStart <= this.startDate.getUTCFullYear() && annoEnd <= this.startDate.getUTCMonth()) {
            this.picker.find(".prev").css({
              visibility : "hidden"
            });
          } else {
            this.picker.find(".prev").css({
              visibility : "visible"
            });
          }
          if (annoStart >= this.endDate.getUTCFullYear() && annoEnd >= this.endDate.getUTCMonth()) {
            this.picker.find(".next").css({
              visibility : "hidden"
            });
          } else {
            this.picker.find(".next").css({
              visibility : "visible"
            });
          }
          break;
        case 3:
        case 4:
          if (annoStart <= this.startDate.getUTCFullYear()) {
            this.picker.find(".prev").css({
              visibility : "hidden"
            });
          } else {
            this.picker.find(".prev").css({
              visibility : "visible"
            });
          }
          if (annoStart >= this.endDate.getUTCFullYear()) {
            this.picker.find(".next").css({
              visibility : "hidden"
            });
          } else {
            this.picker.find(".next").css({
              visibility : "visible"
            });
          }
          break;
      }
    },
    mousewheel : function(event) {
      event.preventDefault();
      event.stopPropagation();
      if (this.wheelPause) {
        return;
      }
      /** @type {boolean} */
      this.wheelPause = true;
      var originalEvent = event.originalEvent;
      var delta = originalEvent.wheelDelta;
      /** @type {number} */
      var mode = delta > 0 ? 1 : delta === 0 ? 0 : -1;
      if (this.wheelViewModeNavigationInverseDirection) {
        /** @type {number} */
        mode = -mode;
      }
      this.showMode(mode);
      setTimeout($.proxy(function() {
        /** @type {boolean} */
        this.wheelPause = false;
      }, this), this.wheelViewModeNavigationDelay);
    },
    click : function(event) {
      event.stopPropagation();
      event.preventDefault();
      var target = $(event.target).closest("span, td, th, legend");
      if (target.is("." + this.icontype)) {
        target = $(target).parent().closest("span, td, th, legend");
      }
      if (target.length === 1) {
        if (target.is(".disabled")) {
          this.element.trigger({
            type : "outOfRange",
            date : this.viewDate,
            startDate : this.startDate,
            endDate : this.endDate
          });
          return;
        }
        switch(target[0].nodeName.toLowerCase()) {
          case "th":
            switch(target[0].className) {
              case "switch":
                this.showMode(1);
                break;
              case "prev":
              case "next":
                /** @type {number} */
                var dir = DPGlobal.modes[this.viewMode].navStep * (target[0].className === "prev" ? -1 : 1);
                switch(this.viewMode) {
                  case 0:
                    this.viewDate = this.moveHour(this.viewDate, dir);
                    break;
                  case 1:
                    this.viewDate = this.moveDate(this.viewDate, dir);
                    break;
                  case 2:
                    this.viewDate = this.moveMonth(this.viewDate, dir);
                    break;
                  case 3:
                  case 4:
                    this.viewDate = this.moveYear(this.viewDate, dir);
                    break;
                }this.fill();
                this.element.trigger({
                  type : target[0].className + ":" + this.convertViewModeText(this.viewMode),
                  date : this.viewDate,
                  startDate : this.startDate,
                  endDate : this.endDate
                });
                break;
              case "clear":
                this.reset();
                if (this.autoclose) {
                  this.hide();
                }
                break;
              case "today":
                /** @type {!Date} */
                var date = new Date;
                date = UTCDate(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), 0);
                if (date < this.startDate) {
                  date = this.startDate;
                } else {
                  if (date > this.endDate) {
                    date = this.endDate;
                  }
                }
                this.viewMode = this.startViewMode;
                this.showMode(0);
                this._setDate(date);
                this.fill();
                if (this.autoclose) {
                  this.hide();
                }
                break;
            }break;
          case "span":
            if (!target.is(".disabled")) {
              var year = this.viewDate.getUTCFullYear();
              var month = this.viewDate.getUTCMonth();
              var day = this.viewDate.getUTCDate();
              var hours = this.viewDate.getUTCHours();
              var minutes = this.viewDate.getUTCMinutes();
              var seconds = this.viewDate.getUTCSeconds();
              if (target.is(".month")) {
                this.viewDate.setUTCDate(1);
                month = target.parent().find("span").index(target);
                day = this.viewDate.getUTCDate();
                this.viewDate.setUTCMonth(month);
                this.element.trigger({
                  type : "changeMonth",
                  date : this.viewDate
                });
                if (this.viewSelect >= 3) {
                  this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
                }
              } else {
                if (target.is(".year")) {
                  this.viewDate.setUTCDate(1);
                  /** @type {number} */
                  year = parseInt(target.text(), 10) || 0;
                  this.viewDate.setUTCFullYear(year);
                  this.element.trigger({
                    type : "changeYear",
                    date : this.viewDate
                  });
                  if (this.viewSelect >= 4) {
                    this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
                  }
                } else {
                  if (target.is(".hour")) {
                    /** @type {number} */
                    hours = parseInt(target.text(), 10) || 0;
                    if (target.hasClass("hour_am") || target.hasClass("hour_pm")) {
                      if (hours === 12 && target.hasClass("hour_am")) {
                        /** @type {number} */
                        hours = 0;
                      } else {
                        if (hours !== 12 && target.hasClass("hour_pm")) {
                          /** @type {number} */
                          hours = hours + 12;
                        }
                      }
                    }
                    this.viewDate.setUTCHours(hours);
                    this.element.trigger({
                      type : "changeHour",
                      date : this.viewDate
                    });
                    if (this.viewSelect >= 1) {
                      this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
                    }
                  } else {
                    if (target.is(".minute")) {
                      /** @type {number} */
                      minutes = parseInt(target.text().substr(target.text().indexOf(":") + 1), 10) || 0;
                      this.viewDate.setUTCMinutes(minutes);
                      this.element.trigger({
                        type : "changeMinute",
                        date : this.viewDate
                      });
                      if (this.viewSelect >= 0) {
                        this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
                      }
                    }
                  }
                }
              }
              if (this.viewMode !== 0) {
                var oldViewMode = this.viewMode;
                this.showMode(-1);
                this.fill();
                if (oldViewMode === this.viewMode && this.autoclose) {
                  this.hide();
                }
              } else {
                this.fill();
                if (this.autoclose) {
                  this.hide();
                }
              }
            }
            break;
          case "td":
            if (target.is(".day") && !target.is(".disabled")) {
              /** @type {number} */
              day = parseInt(target.text(), 10) || 1;
              year = this.viewDate.getUTCFullYear();
              month = this.viewDate.getUTCMonth();
              hours = this.viewDate.getUTCHours();
              minutes = this.viewDate.getUTCMinutes();
              seconds = this.viewDate.getUTCSeconds();
              if (target.is(".old")) {
                if (month === 0) {
                  /** @type {number} */
                  month = 11;
                  /** @type {number} */
                  year = year - 1;
                } else {
                  /** @type {number} */
                  month = month - 1;
                }
              } else {
                if (target.is(".new")) {
                  if (month === 11) {
                    /** @type {number} */
                    month = 0;
                    year = year + 1;
                  } else {
                    month = month + 1;
                  }
                }
              }
              this.viewDate.setUTCFullYear(year);
              this.viewDate.setUTCMonth(month, day);
              this.element.trigger({
                type : "changeDay",
                date : this.viewDate
              });
              if (this.viewSelect >= 2) {
                this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
              }
            }
            oldViewMode = this.viewMode;
            this.showMode(-1);
            this.fill();
            if (oldViewMode === this.viewMode && this.autoclose) {
              this.hide();
            }
            break;
        }
      }
    },
    _setDate : function(date, which) {
      if (!which || which === "date") {
        /** @type {!Object} */
        this.date = date;
      }
      if (!which || which === "view") {
        /** @type {!Object} */
        this.viewDate = date;
      }
      this.fill();
      this.setValue();
      var element;
      if (this.isInput) {
        element = this.element;
      } else {
        if (this.component) {
          element = this.element.find("input");
        }
      }
      if (element) {
        element.change();
      }
      this.element.trigger({
        type : "changeDate",
        date : this.getDate()
      });
      if (date === null) {
        this.date = this.viewDate;
      }
    },
    moveMinute : function(date, dir) {
      if (!dir) {
        return date;
      }
      /** @type {!Date} */
      var new_date = new Date(date.valueOf());
      new_date.setUTCMinutes(new_date.getUTCMinutes() + dir * this.minuteStep);
      return new_date;
    },
    moveHour : function(date, dir) {
      if (!dir) {
        return date;
      }
      /** @type {!Date} */
      var new_date = new Date(date.valueOf());
      new_date.setUTCHours(new_date.getUTCHours() + dir);
      return new_date;
    },
    moveDate : function(date, dir) {
      if (!dir) {
        return date;
      }
      /** @type {!Date} */
      var new_date = new Date(date.valueOf());
      new_date.setUTCDate(new_date.getUTCDate() + dir);
      return new_date;
    },
    moveMonth : function(date, dir) {
      if (!dir) {
        return date;
      }
      /** @type {!Date} */
      var new_date = new Date(date.valueOf());
      /** @type {number} */
      var day = new_date.getUTCDate();
      /** @type {number} */
      var month = new_date.getUTCMonth();
      /** @type {number} */
      var i = Math.abs(dir);
      var new_month;
      var test;
      /** @type {number} */
      dir = dir > 0 ? 1 : -1;
      if (i === 1) {
        /** @type {function(): ?} */
        test = dir === -1 ? function() {
          return new_date.getUTCMonth() === month;
        } : function() {
          return new_date.getUTCMonth() !== new_month;
        };
        /** @type {number} */
        new_month = month + dir;
        new_date.setUTCMonth(new_month);
        if (new_month < 0 || new_month > 11) {
          /** @type {number} */
          new_month = (new_month + 12) % 12;
        }
      } else {
        /** @type {number} */
        var whichFriend = 0;
        for (; whichFriend < i; whichFriend++) {
          new_date = this.moveMonth(new_date, dir);
        }
        new_month = new_date.getUTCMonth();
        new_date.setUTCDate(day);
        /**
         * @return {?}
         */
        test = function() {
          return new_month !== new_date.getUTCMonth();
        };
      }
      for (; test();) {
        new_date.setUTCDate(--day);
        new_date.setUTCMonth(new_month);
      }
      return new_date;
    },
    moveYear : function(date, dir) {
      return this.moveMonth(date, dir * 12);
    },
    dateWithinRange : function(date) {
      return date >= this.startDate && date <= this.endDate;
    },
    keydown : function(event) {
      if (this.picker.is(":not(:visible)")) {
        if (event.keyCode === 27) {
          this.show();
        }
        return;
      }
      /** @type {boolean} */
      var k = false;
      var dir;
      var newDate;
      var newViewDate;
      switch(event.keyCode) {
        case 27:
          this.hide();
          event.preventDefault();
          break;
        case 37:
        case 39:
          if (!this.keyboardNavigation) {
            break;
          }
          /** @type {number} */
          dir = event.keyCode === 37 ? -1 : 1;
          var viewMode = this.viewMode;
          if (event.ctrlKey) {
            viewMode = viewMode + 2;
          } else {
            if (event.shiftKey) {
              viewMode = viewMode + 1;
            }
          }
          if (viewMode === 4) {
            newDate = this.moveYear(this.date, dir);
            newViewDate = this.moveYear(this.viewDate, dir);
          } else {
            if (viewMode === 3) {
              newDate = this.moveMonth(this.date, dir);
              newViewDate = this.moveMonth(this.viewDate, dir);
            } else {
              if (viewMode === 2) {
                newDate = this.moveDate(this.date, dir);
                newViewDate = this.moveDate(this.viewDate, dir);
              } else {
                if (viewMode === 1) {
                  newDate = this.moveHour(this.date, dir);
                  newViewDate = this.moveHour(this.viewDate, dir);
                } else {
                  if (viewMode === 0) {
                    newDate = this.moveMinute(this.date, dir);
                    newViewDate = this.moveMinute(this.viewDate, dir);
                  }
                }
              }
            }
          }
          if (this.dateWithinRange(newDate)) {
            this.date = newDate;
            this.viewDate = newViewDate;
            this.setValue();
            this.update();
            event.preventDefault();
            /** @type {boolean} */
            k = true;
          }
          break;
        case 38:
        case 40:
          if (!this.keyboardNavigation) {
            break;
          }
          /** @type {number} */
          dir = event.keyCode === 38 ? -1 : 1;
          viewMode = this.viewMode;
          if (event.ctrlKey) {
            viewMode = viewMode + 2;
          } else {
            if (event.shiftKey) {
              viewMode = viewMode + 1;
            }
          }
          if (viewMode === 4) {
            newDate = this.moveYear(this.date, dir);
            newViewDate = this.moveYear(this.viewDate, dir);
          } else {
            if (viewMode === 3) {
              newDate = this.moveMonth(this.date, dir);
              newViewDate = this.moveMonth(this.viewDate, dir);
            } else {
              if (viewMode === 2) {
                newDate = this.moveDate(this.date, dir * 7);
                newViewDate = this.moveDate(this.viewDate, dir * 7);
              } else {
                if (viewMode === 1) {
                  if (this.showMeridian) {
                    newDate = this.moveHour(this.date, dir * 6);
                    newViewDate = this.moveHour(this.viewDate, dir * 6);
                  } else {
                    newDate = this.moveHour(this.date, dir * 4);
                    newViewDate = this.moveHour(this.viewDate, dir * 4);
                  }
                } else {
                  if (viewMode === 0) {
                    newDate = this.moveMinute(this.date, dir * 4);
                    newViewDate = this.moveMinute(this.viewDate, dir * 4);
                  }
                }
              }
            }
          }
          if (this.dateWithinRange(newDate)) {
            this.date = newDate;
            this.viewDate = newViewDate;
            this.setValue();
            this.update();
            event.preventDefault();
            /** @type {boolean} */
            k = true;
          }
          break;
        case 13:
          if (this.viewMode !== 0) {
            var oldViewMode = this.viewMode;
            this.showMode(-1);
            this.fill();
            if (oldViewMode === this.viewMode && this.autoclose) {
              this.hide();
            }
          } else {
            this.fill();
            if (this.autoclose) {
              this.hide();
            }
          }
          event.preventDefault();
          break;
        case 9:
          this.hide();
          break;
      }
      if (k) {
        var element;
        if (this.isInput) {
          element = this.element;
        } else {
          if (this.component) {
            element = this.element.find("input");
          }
        }
        if (element) {
          element.change();
        }
        this.element.trigger({
          type : "changeDate",
          date : this.getDate()
        });
      }
    },
    showMode : function(dir) {
      if (dir) {
        /** @type {number} */
        var newViewMode = Math.max(0, Math.min(DPGlobal.modes.length - 1, this.viewMode + dir));
        if (newViewMode >= this.minView && newViewMode <= this.maxView) {
          this.element.trigger({
            type : "changeMode",
            date : this.viewDate,
            oldViewMode : this.viewMode,
            newViewMode : newViewMode
          });
          /** @type {number} */
          this.viewMode = newViewMode;
        }
      }
      this.picker.find(">div").hide().filter(".datetimepicker-" + DPGlobal.modes[this.viewMode].clsName).css("display", "block");
      this.updateNavArrows();
    },
    reset : function() {
      this._setDate(null, "date");
    },
    convertViewModeText : function(viewMode) {
      switch(viewMode) {
        case 4:
          return "decade";
        case 3:
          return "year";
        case 2:
          return "month";
        case 1:
          return "day";
        case 0:
          return "hour";
      }
    }
  };
  var old = $.fn.datetimepicker;
  /**
   * @param {string} option
   * @return {?}
   */
  $.fn.datetimepicker = function(option) {
    var i = Array.apply(null, arguments);
    i.shift();
    var result;
    this.each(function() {
      var $this = $(this);
      var data = $this.data("datetimepicker");
      var options = typeof option === "object" && option;
      if (!data) {
        $this.data("datetimepicker", data = new Datetimepicker(this, $.extend({}, $.fn.datetimepicker.defaults, options)));
      }
      if (typeof option === "string" && typeof data[option] === "function") {
        result = data[option].apply(data, i);
        if (result !== undefined) {
          return false;
        }
      }
    });
    if (result !== undefined) {
      return result;
    } else {
      return this;
    }
  };
  $.fn.datetimepicker.defaults = {};
  /** @type {function(?, !Object): undefined} */
  $.fn.datetimepicker.Constructor = Datetimepicker;
  var dates = $.fn.datetimepicker.dates = {
    en : {
      days : ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
      daysShort : ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
      daysMin : ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
      months : ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
      monthsShort : ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      meridiem : ["am", "pm"],
      suffix : ["st", "nd", "rd", "th"],
      today : "Today",
      clear : "Clear"
    }
  };
  var DPGlobal = {
    modes : [{
      clsName : "minutes",
      navFnc : "Hours",
      navStep : 1
    }, {
      clsName : "hours",
      navFnc : "Date",
      navStep : 1
    }, {
      clsName : "days",
      navFnc : "Month",
      navStep : 1
    }, {
      clsName : "months",
      navFnc : "FullYear",
      navStep : 1
    }, {
      clsName : "years",
      navFnc : "FullYear",
      navStep : 10
    }],
    isLeapYear : function(date) {
      return date % 4 === 0 && date % 100 !== 0 || date % 400 === 0;
    },
    getDaysInMonth : function(date, year) {
      return [31, DPGlobal.isLeapYear(date) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][year];
    },
    getDefaultFormat : function(ext, type) {
      if (ext === "standard") {
        if (type === "input") {
          return "yyyy-mm-dd hh:ii";
        } else {
          return "yyyy-mm-dd hh:ii:ss";
        }
      } else {
        if (ext === "php") {
          if (type === "input") {
            return "Y-m-d H:i";
          } else {
            return "Y-m-d H:i:s";
          }
        } else {
          throw new Error("Invalid format type.");
        }
      }
    },
    validParts : function(type) {
      if (type === "standard") {
        return /t|hh?|HH?|p|P|z|Z|ii?|ss?|dd?|DD?|mm?|MM?|yy(?:yy)?/g;
      } else {
        if (type === "php") {
          return /[dDjlNwzFmMnStyYaABgGhHis]/g;
        } else {
          throw new Error("Invalid format type.");
        }
      }
    },
    nonpunctuation : /[^ -\/:-@\[-`{-~\t\n\rTZ]+/g,
    parseFormat : function(format, type) {
      var separators = format.replace(this.validParts(type), "\x00").split("\x00");
      var parts = format.match(this.validParts(type));
      if (!separators || !separators.length || !parts || parts.length === 0) {
        throw new Error("Invalid date format.");
      }
      return {
        separators : separators,
        parts : parts
      };
    },
    parseDate : function(date, format, language, type, timezone) {
      if (date instanceof Date) {
        /** @type {!Date} */
        var dateUTC = new Date(date.valueOf() - date.getTimezoneOffset() * 6E4);
        dateUTC.setMilliseconds(0);
        return dateUTC;
      }
      if (/^\d{4}\-\d{1,2}\-\d{1,2}$/.test(date)) {
        format = this.parseFormat("yyyy-mm-dd", type);
      }
      if (/^\d{4}\-\d{1,2}\-\d{1,2}[T ]\d{1,2}:\d{1,2}$/.test(date)) {
        format = this.parseFormat("yyyy-mm-dd hh:ii", type);
      }
      if (/^\d{4}\-\d{1,2}\-\d{1,2}[T ]\d{1,2}:\d{1,2}:\d{1,2}[Z]{0,1}$/.test(date)) {
        format = this.parseFormat("yyyy-mm-dd hh:ii:ss", type);
      }
      if (/^[-+]\d+[dmwy]([\s,]+[-+]\d+[dmwy])*$/.test(date)) {
        /** @type {!RegExp} */
        var test = /([-+]\d+)([dmwy])/;
        var results = date.match(/([-+]\d+)([dmwy])/g);
        var part;
        var dir;
        /** @type {!Date} */
        date = new Date;
        /** @type {number} */
        var i = 0;
        for (; i < results.length; i++) {
          /** @type {(Array<string>|null)} */
          part = test.exec(results[i]);
          /** @type {number} */
          dir = parseInt(part[1]);
          switch(part[2]) {
            case "d":
              date.setUTCDate(date.getUTCDate() + dir);
              break;
            case "m":
              date = Datetimepicker.prototype.moveMonth.call(Datetimepicker.prototype, date, dir);
              break;
            case "w":
              date.setUTCDate(date.getUTCDate() + dir * 7);
              break;
            case "y":
              date = Datetimepicker.prototype.moveYear.call(Datetimepicker.prototype, date, dir);
              break;
          }
        }
        return UTCDate(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds(), 0);
      }
      results = date && date.toString().match(this.nonpunctuation) || [];
      /** @type {!Date} */
      date = new Date(0, 0, 0, 0, 0, 0, 0);
      var parsed = {};
      /** @type {!Array} */
      var setters_order = ["hh", "h", "ii", "i", "ss", "s", "yyyy", "yy", "M", "MM", "m", "mm", "D", "DD", "d", "dd", "H", "HH", "p", "P", "z", "Z"];
      var setters_map = {
        hh : function(number, date) {
          return number.setUTCHours(date);
        },
        h : function(d, v) {
          return d.setUTCHours(v);
        },
        HH : function(date, hours) {
          return date.setUTCHours(hours === 12 ? 0 : hours);
        },
        H : function(date, hours) {
          return date.setUTCHours(hours === 12 ? 0 : hours);
        },
        ii : function(d, v) {
          return d.setUTCMinutes(v);
        },
        i : function(date, value) {
          return date.setUTCMinutes(value);
        },
        ss : function(d, v) {
          return d.setUTCSeconds(v);
        },
        s : function(d, v) {
          return d.setUTCSeconds(v);
        },
        yyyy : function(d, v) {
          return d.setUTCFullYear(v);
        },
        yy : function(date, step) {
          return date.setUTCFullYear(2E3 + step);
        },
        m : function(d, v) {
          /** @type {number} */
          v = v - 1;
          for (; v < 0;) {
            v = v + 12;
          }
          /** @type {number} */
          v = v % 12;
          d.setUTCMonth(v);
          for (; d.getUTCMonth() !== v;) {
            if (isNaN(d.getUTCMonth())) {
              return d;
            } else {
              d.setUTCDate(d.getUTCDate() - 1);
            }
          }
          return d;
        },
        d : function(s, v) {
          return s.setUTCDate(v);
        },
        p : function(dt, action) {
          return dt.setUTCHours(action === 1 ? dt.getUTCHours() + 12 : dt.getUTCHours());
        },
        z : function() {
          return timezone;
        }
      };
      var value;
      var checkFor;
      /** @type {function(!Date, number): ?} */
      setters_map.M = setters_map.MM = setters_map.mm = setters_map.m;
      /** @type {function(!Date, !Object): ?} */
      setters_map.dd = setters_map.d;
      /** @type {function(!Date, number): ?} */
      setters_map.P = setters_map.p;
      /** @type {function(): ?} */
      setters_map.Z = setters_map.z;
      date = UTCDate(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds());
      if (results.length === format.parts.length) {
        /** @type {number} */
        i = 0;
        var patchLen = format.parts.length;
        for (; i < patchLen; i++) {
          /** @type {number} */
          value = parseInt(results[i], 10);
          part = format.parts[i];
          if (isNaN(value)) {
            switch(part) {
              case "MM":
                checkFor = $(dates[language].months).filter(function() {
                  var close = this.slice(0, results[i].length);
                  var value = results[i].slice(0, close.length);
                  return close === value;
                });
                value = $.inArray(checkFor[0], dates[language].months) + 1;
                break;
              case "M":
                checkFor = $(dates[language].monthsShort).filter(function() {
                  var m = this.slice(0, results[i].length);
                  var currentNick = results[i].slice(0, m.length);
                  return m.toLowerCase() === currentNick.toLowerCase();
                });
                value = $.inArray(checkFor[0], dates[language].monthsShort) + 1;
                break;
              case "p":
              case "P":
                value = $.inArray(results[i].toLowerCase(), dates[language].meridiem);
                break;
              case "z":
              case "Z":
                timezone;
                break;
            }
          }
          parsed[part] = value;
        }
        /** @type {number} */
        i = 0;
        var s;
        for (; i < setters_order.length; i++) {
          s = setters_order[i];
          if (s in parsed && !isNaN(parsed[s])) {
            setters_map[s](date, parsed[s]);
          }
        }
      }
      return date;
    },
    formatDate : function(date, format, language, key, now) {
      if (date === null) {
        return "";
      }
      var val;
      if (key === "standard") {
        val = {
          t : date.getTime(),
          yy : date.getUTCFullYear().toString().substring(2),
          yyyy : date.getUTCFullYear(),
          m : date.getUTCMonth() + 1,
          M : dates[language].monthsShort[date.getUTCMonth()],
          MM : dates[language].months[date.getUTCMonth()],
          d : date.getUTCDate(),
          D : dates[language].daysShort[date.getUTCDay()],
          DD : dates[language].days[date.getUTCDay()],
          p : dates[language].meridiem.length === 2 ? dates[language].meridiem[date.getUTCHours() < 12 ? 0 : 1] : "",
          h : date.getUTCHours(),
          i : date.getUTCMinutes(),
          s : date.getUTCSeconds(),
          z : now
        };
        if (dates[language].meridiem.length === 2) {
          /** @type {number} */
          val.H = val.h % 12 === 0 ? 12 : val.h % 12;
        } else {
          val.H = val.h;
        }
        /** @type {string} */
        val.HH = (val.H < 10 ? "0" : "") + val.H;
        val.P = val.p.toUpperCase();
        val.Z = val.z;
        /** @type {string} */
        val.hh = (val.h < 10 ? "0" : "") + val.h;
        /** @type {string} */
        val.ii = (val.i < 10 ? "0" : "") + val.i;
        /** @type {string} */
        val.ss = (val.s < 10 ? "0" : "") + val.s;
        /** @type {string} */
        val.dd = (val.d < 10 ? "0" : "") + val.d;
        /** @type {string} */
        val.mm = (val.m < 10 ? "0" : "") + val.m;
      } else {
        if (key === "php") {
          val = {
            y : date.getUTCFullYear().toString().substring(2),
            Y : date.getUTCFullYear(),
            F : dates[language].months[date.getUTCMonth()],
            M : dates[language].monthsShort[date.getUTCMonth()],
            n : date.getUTCMonth() + 1,
            t : DPGlobal.getDaysInMonth(date.getUTCFullYear(), date.getUTCMonth()),
            j : date.getUTCDate(),
            l : dates[language].days[date.getUTCDay()],
            D : dates[language].daysShort[date.getUTCDay()],
            w : date.getUTCDay(),
            N : date.getUTCDay() === 0 ? 7 : date.getUTCDay(),
            S : date.getUTCDate() % 10 <= dates[language].suffix.length ? dates[language].suffix[date.getUTCDate() % 10 - 1] : "",
            a : dates[language].meridiem.length === 2 ? dates[language].meridiem[date.getUTCHours() < 12 ? 0 : 1] : "",
            g : date.getUTCHours() % 12 === 0 ? 12 : date.getUTCHours() % 12,
            G : date.getUTCHours(),
            i : date.getUTCMinutes(),
            s : date.getUTCSeconds()
          };
          /** @type {string} */
          val.m = (val.n < 10 ? "0" : "") + val.n;
          /** @type {string} */
          val.d = (val.j < 10 ? "0" : "") + val.j;
          val.A = val.a.toString().toUpperCase();
          /** @type {string} */
          val.h = (val.g < 10 ? "0" : "") + val.g;
          /** @type {string} */
          val.H = (val.G < 10 ? "0" : "") + val.G;
          /** @type {string} */
          val.i = (val.i < 10 ? "0" : "") + val.i;
          /** @type {string} */
          val.s = (val.s < 10 ? "0" : "") + val.s;
        } else {
          throw new Error("Invalid format type.");
        }
      }
      /** @type {!Array} */
      date = [];
      var _sizeAnimateTimeStamps = $.extend([], format.separators);
      /** @type {number} */
      var i = 0;
      var patchLen = format.parts.length;
      for (; i < patchLen; i++) {
        if (_sizeAnimateTimeStamps.length) {
          date.push(_sizeAnimateTimeStamps.shift());
        }
        date.push(val[format.parts[i]]);
      }
      if (_sizeAnimateTimeStamps.length) {
        date.push(_sizeAnimateTimeStamps.shift());
      }
      return date.join("");
    },
    convertViewMode : function(viewMode) {
      switch(viewMode) {
        case 4:
        case "decade":
          /** @type {number} */
          viewMode = 4;
          break;
        case 3:
        case "year":
          /** @type {number} */
          viewMode = 3;
          break;
        case 2:
        case "month":
          /** @type {number} */
          viewMode = 2;
          break;
        case 1:
        case "day":
          /** @type {number} */
          viewMode = 1;
          break;
        case 0:
        case "hour":
          /** @type {number} */
          viewMode = 0;
          break;
      }
      return viewMode;
    },
    headTemplate : '<thead><tr><th class="prev"><i class="fas fa-arrow-left"/></th><th colspan="5" class="switch"></th><th class="next"><i class="fas fa-arrow-right"/></th></tr></thead>',
    headTemplateV3 : '<thead><tr><th class="prev"><span class="fas fa-arrow-left"></span> </th><th colspan="5" class="switch"></th><th class="next"><span class="fas fa-arrow-right"></span> </th></tr></thead>',
    contTemplate : '<tbody><tr><td colspan="7"></td></tr></tbody>',
    footTemplate : '<tfoot><tr><th colspan="7" class="today"></th></tr><tr><th colspan="7" class="clear"></th></tr></tfoot>'
  };
  /** @type {string} */
  DPGlobal.template = '<div class="datetimepicker"><div class="datetimepicker-minutes"><table class=" table-condensed">' + DPGlobal.headTemplate + DPGlobal.contTemplate + DPGlobal.footTemplate + '</table></div><div class="datetimepicker-hours"><table class=" table-condensed">' + DPGlobal.headTemplate + DPGlobal.contTemplate + DPGlobal.footTemplate + '</table></div><div class="datetimepicker-days"><table class=" table-condensed">' + DPGlobal.headTemplate + "<tbody></tbody>" + DPGlobal.footTemplate + 
  '</table></div><div class="datetimepicker-months"><table class="table-condensed">' + DPGlobal.headTemplate + DPGlobal.contTemplate + DPGlobal.footTemplate + '</table></div><div class="datetimepicker-years"><table class="table-condensed">' + DPGlobal.headTemplate + DPGlobal.contTemplate + DPGlobal.footTemplate + "</table></div></div>";
  /** @type {string} */
  DPGlobal.templateV3 = '<div class="datetimepicker"><div class="datetimepicker-minutes"><table class=" table-condensed">' + DPGlobal.headTemplateV3 + DPGlobal.contTemplate + DPGlobal.footTemplate + '</table></div><div class="datetimepicker-hours"><table class=" table-condensed">' + DPGlobal.headTemplateV3 + DPGlobal.contTemplate + DPGlobal.footTemplate + '</table></div><div class="datetimepicker-days"><table class=" table-condensed">' + DPGlobal.headTemplateV3 + "<tbody></tbody>" + DPGlobal.footTemplate + 
  '</table></div><div class="datetimepicker-months"><table class="table-condensed">' + DPGlobal.headTemplateV3 + DPGlobal.contTemplate + DPGlobal.footTemplate + '</table></div><div class="datetimepicker-years"><table class="table-condensed">' + DPGlobal.headTemplateV3 + DPGlobal.contTemplate + DPGlobal.footTemplate + "</table></div></div>";
  $.fn.datetimepicker.DPGlobal = DPGlobal;
  /**
   * @return {?}
   */
  $.fn.datetimepicker.noConflict = function() {
    $.fn.datetimepicker = old;
    return this;
  };
  $(document).on("focus.datetimepicker.data-api click.datetimepicker.data-api", '[data-provide="datetimepicker"]', function(event) {
    var $this = $(this);
    if ($this.data("datetimepicker")) {
      return;
    }
    event.preventDefault();
    $this.datetimepicker("show");
  });
  $(function() {
    $('[data-provide="datetimepicker-inline"]').datetimepicker();
  });
});
