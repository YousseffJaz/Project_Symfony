'use strict';
!function(global, factory) {
  if ("object" == typeof exports && "object" == typeof module) {
    module.exports = factory(require("moment"), require("fullcalendar"));
  } else {
    if ("function" == typeof define && define.amd) {
      define(["moment", "fullcalendar"], factory);
    } else {
      if ("object" == typeof exports) {
        factory(require("moment"), require("fullcalendar"));
      } else {
        factory(global.moment, global.FullCalendar);
      }
    }
  }
}("undefined" != typeof self ? self : this, function(__WEBPACK_EXTERNAL_MODULE_61__, formlyUsability) {
  return function(e) {
    /**
     * @param {number} i
     * @return {?}
     */
    function t(i) {
      if (n[i]) {
        return n[i].exports;
      }
      var module = n[i] = {
        i : i,
        l : false,
        exports : {}
      };
      return e[i].call(module.exports, module, module.exports, t), module.l = true, module.exports;
    }
    var n = {};
    return t.m = e, t.c = n, t.d = function(e, n, val) {
      if (!t.o(e, n)) {
        Object.defineProperty(e, n, {
          configurable : false,
          enumerable : true,
          get : val
        });
      }
    }, t.n = function(module) {
      /** @type {function(): ?} */
      var n = module && module.__esModule ? function() {
        return module.default;
      } : function() {
        return module;
      };
      return t.d(n, "a", n), n;
    }, t.o = function(e, x) {
      return Object.prototype.hasOwnProperty.call(e, x);
    }, t.p = "", t(t.s = 135);
  }({
    0 : function(module, data) {
      /** @type {!Function} */
      module.exports = __WEBPACK_EXTERNAL_MODULE_61__;
    },
    1 : function(module, data) {
      /** @type {!Function} */
      module.exports = formlyUsability;
    },
    135 : function(docPath, sandbox, require) {
      Object.defineProperty(sandbox, "__esModule", {
        value : true
      });
      require(136);
      var i18n = require(1);
      i18n.datepickerLocale("fr", "fr", {
        closeText : "Fermer",
        prevText : "Précédent",
        nextText : "Suivant",
        currentText : "Aujourd'hui",
        monthNames : ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
        monthNamesShort : ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."],
        dayNames : ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
        dayNamesShort : ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
        dayNamesMin : ["D", "L", "M", "M", "J", "V", "S"],
        weekHeader : "Sem.",
        dateFormat : "dd/mm/yy",
        firstDay : 1,
        isRTL : false,
        showMonthAfterYear : false,
        yearSuffix : ""
      });
      i18n.locale("fr", {
        buttonText : {
          year : "Année",
          month : "Mois",
          week : "Semaine",
          day : "Jour",
          list : "Mon planning"
        },
        allDayHtml : "Toute la<br/>journée",
        eventLimitText : "en plus",
        noEventsMessage : "Aucun événement à afficher"
      });
    },
    136 : function(onerror, define, cb) {
      !function(addedRenderer, setter) {
        setter(cb(0));
      }(0, function(moment) {
        return moment.defineLocale("fr", {
          months : "Janvier_Février_Mars_Avril_Mai_Juin_Juillet_Août_Septembre_Octobre_Novembre_Décembre".split("_"),
          monthsShort : "Janv._Févr._Mars_Avr._Mai_Juin_Juil._Aoùt_Sept._Oct._Nov._Déc.".split("_"),
          monthsParseExact : true,
          weekdays : "dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi".split("_"),
          weekdaysShort : "dim._lun._mar._mer._jeu._ven._sam.".split("_"),
          weekdaysMin : "di_lu_ma_me_je_ve_sa".split("_"),
          weekdaysParseExact : true,
          longDateFormat : {
            LT : "HH:mm",
            LTS : "HH:mm:ss",
            L : "DD/MM/YYYY",
            LL : "D MMMM YYYY",
            LLL : "D MMMM YYYY HH:mm",
            LLLL : "dddd D MMMM YYYY HH:mm"
          },
          calendar : {
            sameDay : "[Aujourd'hui à] LT",
            nextDay : "[Demain à] LT",
            nextWeek : "dddd [à] LT",
            lastDay : "[Hier à] LT",
            lastWeek : "dddd [dernier à] LT",
            sameElse : "L"
          },
          relativeTime : {
            future : "dans %s",
            past : "il y a %s",
            s : "quelques secondes",
            ss : "%d secondes",
            m : "une minute",
            mm : "%d minutes",
            h : "une heure",
            hh : "%d heures",
            d : "un jour",
            dd : "%d jours",
            M : "un mois",
            MM : "%d mois",
            y : "un an",
            yy : "%d ans"
          },
          dayOfMonthOrdinalParse : /\d{1,2}(er|)/,
          ordinal : function(number, period) {
            switch(period) {
              case "D":
                return number + (1 === number ? "er" : "");
              default:
              case "M":
              case "Q":
              case "DDD":
              case "d":
                return number + (1 === number ? "er" : "e");
              case "w":
              case "W":
                return number + (1 === number ? "re" : "e");
            }
          },
          week : {
            dow : 1,
            doy : 4
          }
        });
      });
    }
  });
});
