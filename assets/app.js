// import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './less/main.less';
import './js/main.js';
import './js/complect.js';
import './js/pslider.js';
import 'owl.carousel/dist/assets/owl.carousel.css';
import 'owl.carousel';

$(document).ready(function() {
    $('.owl-carousel').owlCarousel({
        loop: false,
        nav: true,
        navText: ['', ''],
        dots: false,
        autoWidth: true
    });
});