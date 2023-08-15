(function ($, Drupal){
$( document ).ready(function() {
// Select all slides
  const slides = document.querySelectorAll(".city-slide ");
  let curNumber= 1;
  let maxNumber = slides.length;
  $(".current-number").html("0" + 1);
  $(".max-numbers").html("/0" + maxNumber);
// loop through slides and set each slides translateX property to index * 100%
  slides.forEach((slide, indx) => {
    slide.style.transform = `translateX(${indx * 100}%)`;
  });
// current slide counter
  let curSlide = 0;
// maximum number of slides
  let maxSlide = slides.length - 1;
  setInterval(function test() {
    console.log(curNumber);
    console.log(maxNumber);
    // check if current slide is the last and reset current slide
    if (curSlide === maxSlide) {
      curSlide = 0;
      curNumber = 1;
      $(".current-number").html("0" +curNumber)
    } else {
      curSlide++;
      curNumber ++

      $(".current-number").html("0" +curNumber)
    }

//   move slide by -100%
    slides.forEach((slide, indx) => {
      slide.style.transform = `translateX(${100 * (indx - curSlide)}%)`;
    });
  }, 5000);
});

})(jQuery, Drupal);
