/**
 * @file
 * Hamburger menu logic.
 */

(function ($, Drupal, window, document, undefined) {

  $(".mobile-service-toggle").click(function(){
    $('.mobile-navigation-region').toggleClass('open');
  });

})(jQuery, Drupal, this, this.document);
