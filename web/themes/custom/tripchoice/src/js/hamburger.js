/**
 * @file
 * Hamburger menu logic.
 */

(function ($, Drupal, window, document, undefined) {

  $(".mobile-service-toggle").click(function(){
    $('.mobile-navigation-region').toggleClass('open');
    if ($('.mobile-navigation-region').hasClass('open')){
      $(".hamburger--icon-open").hide();
      $(".hamburger--icon-close").show();
    }
    else{
      $(".hamburger--icon-close").hide();
      $(".hamburger--icon-open").show();
    }
  });

})(jQuery, Drupal, this, this.document);
