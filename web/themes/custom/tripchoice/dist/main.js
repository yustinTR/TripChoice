/******/ (() => { // webpackBootstrap
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other entry modules.
(() => {
/*!************************!*\
  !*** ./src/js/main.js ***!
  \************************/
(function ($, Drupal) {
  $(document).ready(function () {
    // Select all slides
    var slides = document.querySelectorAll(".city-slide ");
    var curNumber = 1;
    var maxNumber = slides.length;
    $(".current-number").html("0" + 1);
    $(".max-numbers").html("/0" + maxNumber);
    // loop through slides and set each slides translateX property to index * 100%
    slides.forEach(function (slide, indx) {
      slide.style.transform = "translateX(".concat(indx * 100, "%)");
    });
    // current slide counter
    var curSlide = 0;
    // maximum number of slides
    var maxSlide = slides.length - 1;
    setInterval(function test() {
      console.log(curNumber);
      console.log(maxNumber);
      // check if current slide is the last and reset current slide
      if (curSlide === maxSlide) {
        curSlide = 0;
        curNumber = 1;
        $(".current-number").html("0" + curNumber);
      } else {
        curSlide++;
        curNumber++;
        $(".current-number").html("0" + curNumber);
      }

      //   move slide by -100%
      slides.forEach(function (slide, indx) {
        slide.style.transform = "translateX(".concat(100 * (indx - curSlide), "%)");
      });
    }, 5000);
  });
})(jQuery, Drupal);
})();

// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!****************************!*\
  !*** ./src/scss/main.scss ***!
  \****************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

})();

/******/ })()
;
//# sourceMappingURL=main.js.map