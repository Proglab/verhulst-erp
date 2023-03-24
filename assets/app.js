/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

$('#sidebar-toogle')
    .click(function() {
        var content = $('#sidebar-toogle').html();
        if ('<i class="fa-solid fa-arrow-right"></i>' == content) {
            $('#sidebar-toogle').html('<i class="fa-solid fa-arrow-left"></i>');
        } else {
            $('#sidebar-toogle').html('<i class="fa-solid fa-arrow-right"></i>');
        }
        $('#sidebar-resizer-handler').trigger( "click" );
    });

console.log($('body').hasClass('ea-sidebar-width-normal'))
if ($('body').hasClass('ea-sidebar-width-normal')) {
    $('#sidebar-toogle').html('<i class="fa-solid fa-arrow-left"></i>');
} else {
    $('#sidebar-toogle').html('<i class="fa-solid fa-arrow-right"></i>');
}
