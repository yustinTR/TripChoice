/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************!*\
  !*** ./src/js/student_qoute.js ***!
  \*********************************/
// Select all slides
var slides = document.querySelectorAll(".views-row");

// loop through slides and set each slides translateX property to index * 100%
slides.forEach(function (slide, indx) {
  slide.style.transform = "translateX(".concat(indx * 100, "%)");
});
// current slide counter
var curSlide = 0;
// maximum number of slides
var maxSlide = slides.length - 1;
setInterval(function test() {
  // check if current slide is the last and reset current slide
  if (curSlide === maxSlide) {
    curSlide = 0;
  } else {
    curSlide++;
  }

  //   move slide by -100%
  slides.forEach(function (slide, indx) {
    slide.style.transform = "translateX(".concat(100 * (indx - curSlide), "%)");
  });
}, 5000);
/******/ })()
;
//# sourceMappingURL=student_qoute.js.map