/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/hamburger.js":
/*!*****************************!*\
  !*** ./src/js/hamburger.js ***!
  \*****************************/
/***/ (function() {

/**
 * @file
 * Hamburger menu logic.
 */

(function ($, Drupal, window, document, undefined) {
  $(".mobile-service-toggle").click(function () {
    $('.mobile-navigation-region').toggleClass('open');
    if ($('.mobile-navigation-region').hasClass('open')) {
      $(".hamburger--icon-open").hide();
      $(".hamburger--icon-close").show();
    } else {
      $(".hamburger--icon-close").hide();
      $(".hamburger--icon-open").show();
    }
  });
})(jQuery, Drupal, this, this.document);

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./src/js/hamburger.js"]();
/******/ 	
/******/ })()
;
//# sourceMappingURL=hamburger.js.map