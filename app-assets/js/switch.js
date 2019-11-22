/*=========================================================================================
    File Name: switch.js
    Description: Bootstrap switch and switchery are best switches with many options.
    ----------------------------------------------------------------------------------------
    Item Name: Convex - Bootstrap 4 HTML Admin Dashboard Template
    Version: 1.0
    Author: PIXINVENT
    Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/
(function(window, document, $) {
  'use strict';
  var $html = $('html');

    // Switchery
    var i = 0;
    if (Array.prototype.forEach) {

        var elems = $('.switchery');
        $.each( elems, function( key, value ) {
            var $size="", $color="",$sec_color="",$jack_color="",$jack_sec_color="",$sizeClass="", $colorCode="",$colorCodeSec="",$jackColorCode="",$jackColorCodeSec="";
            $size = $(this).data('size');
            var $sizes ={
                'lg' : "large",
                'sm' : "small",
                'xs' : "xsmall"
            };
            if($(this).data('size')!== undefined){
                $sizeClass = "switchery switchery-"+$sizes[$size];
            }
            else{
                $sizeClass = "switchery";
            }

            $color = $(this).data('color');
            $sec_color = $(this).data('color-secondary');
            $jack_color = $(this).data('jack-color');
            $jack_sec_color = $(this).data('jack-color-secondary');
            var $colors ={
                'primary' : "#666EE8",
                'success' : "#28D094",
                'danger' : "#FF4961",
                'warning' : "#FF9149",
                'info' : "#1E9FF2",
                'white' : "#FFFFFF"
            };
            if($color !== undefined){
                $colorCode = $colors[$color];
            }
            else{
                $colorCode = "#28D094";
            }

            if($sec_color !== undefined){
                $colorCodeSec = $colors[$sec_color];
            }
            else{
                $colorCodeSec = "#FFFFFF";
            }

            if($jack_color !== undefined){
                $jackColorCode = $colors[$jack_color];
            }
            else{
                $jackColorCode = "#FFFFFF";
            }

            if($jack_sec_color !== undefined){
                $jackColorCodeSec = $colors[$jack_sec_color];
            }
            else{
                $jackColorCodeSec = "#FFFFFF";
            }

            var switchery = new Switchery($(this)[0], { className: $sizeClass, color: $colorCode, secondaryColor: $colorCodeSec, jackColor: $jackColorCode, jackSecondaryColor: $jackColorCodeSec });
        });
    } else {
        var elems1 = document.querySelectorAll('.switchery');

        for (i = 0; i < elems1.length; i++) {
            var $size = elems1[i].data('size');
            var $color = elems1[i].data('color');
            var switchery = new Switchery(elems1[i], { color: '#28D094' });
        }
    }
    /*  Toggle Ends   */

    // Color 
    /*var primary_elem = document.querySelector('.primary-switch');
    var switchery = new Switchery(primary_elem, { color: theme_color('primary')});
    var success_elem = document.querySelector('.success-switch');
    var switchery = new Switchery(success_elem, { color: theme_color('success')});
    var danger_elem = document.querySelector('.danger-switch');
    var switchery = new Switchery(danger_elem, { color: theme_color('danger')});
    var info_elem = document.querySelector('.info-switch');
    var switchery = new Switchery(info_elem, { color: theme_color('info')});
    var warning_elem = document.querySelector('.warning-switch');
    var switchery = new Switchery(warning_elem, { color: theme_color('warning')});*/

})(window, document, jQuery);