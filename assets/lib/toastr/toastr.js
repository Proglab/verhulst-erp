//Toastr

const toastr = require("toastr");
global.toastr = toastr;

toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-bottom-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

$(".toast").each(function(){

    if (window.matchMedia("(max-width: 767px)").matches) {
        toastr.options.positionClass = "toast-top-right";
    } else {
        toastr.options.positionClass = "toast-bottom-right";
    }

    toastr[$(this).data("label")]($(this).data("message"));
    $(this).remove();
});

require("toastr/build/toastr.min.css");
require("./toastr.scss");