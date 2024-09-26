const $ = require('jquery');

$(function() {
    console.log('sponsoring.js > ready');
    
    var commission_percent = $("#ProductSponsoring_percent_vr");
    var price_buy = $("#ProductSponsoring_pa");
    var price_sale = $("#ProductSponsoring_ca");
    
    commission_percent.on('change', function() {
        if ('' === $(this).val()) { return; }
        console.log('commission_percent.on(change);');
        if (price_buy.val()) {
            var value = parseFloat(price_buy.val()) * ((100 + parseFloat(commission_percent.val())) / 100);
            price_sale.val(value.toFixed(2));
        }
    });
    price_buy.on('change', function() {
        if ('' === $(this).val()) { return; }
        console.log('price_buy.on(change);');
        if (commission_percent.val()) {
            var value = parseFloat(price_buy.val()) * ((100 + parseFloat(commission_percent.val())) / 100);
            price_sale.val(value.toFixed(2));
        } else if (price_sale.val()) {
            var value = (parseFloat(price_sale.val()) - parseFloat(price_buy.val())) / 100;
            commission_percent.val(value.toFixed(2));
        }
    });
    price_sale.on('change', function() {
        if ('' === $(this).val()) { return; }
        console.log('price_sale.on(change);');
        if (price_buy.val()) {
            var value = (parseFloat(price_sale.val()) / parseFloat(price_buy.val()) * 100) - 100;
            commission_percent.val(value.toFixed(2));
        }
    });
});
