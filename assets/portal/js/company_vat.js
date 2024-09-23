const $ = require('jquery');
var company = null;
var company_isVat = null;
$(function() {
    company_isVat = $('#new_company_vat_na');
    company_isVat.on('change', function() {
        if ($(this).is(':checked')) {
        } else {
            checkFlag();
        }
    });
    setupVatScan();
});

function checkFlag() {
    if (setupVatScan() === false) {// && !company_isVat.is(':checked')
        // console.log('checkFlag > false');
        window.setTimeout(checkFlag, 100); /* this checks the flag every 100 milliseconds*/
    } else {
        /* do something*/
        // console.log('checkFlag > true');
    }
}

setupVatScan = function() {
    company = $( '#new_company_vat_number' );
    // console.log('setupVatScan', company);
    if (!company.length > 0) {
        return false;
    }
    company.on('change', function() {
        $('#new_company_vat_number').parent().append("<i class=\"fas fa-spinner fa-pulse\" id=\"loading\"></i>");

        var jqxhr = $.ajax( "/admin/fr?crudAction=getVatInfos&crudControllerFqcn=App%5CController%5CAdmin%5CCompanyCrudController&vat=" + company.val() )
            .done(function(result) {
                // console.log(result);
                $( '#new_company_name' ).val(result.company.name);
                $( '#new_company_billing_street' ).val(result.company.street);
                $( '#new_company_billing_pc' ).val(result.company.pc);
                $( '#new_company_billing_city' ).val(result.company.town);
                $( '#new_company_billing_country' ).val(result.country.isoCode.short);
                $( '#new_company_vat_number' ).val(result.vat);

                if ($('#new_company_street').val() == '') { $('#new_company_street').val($('#new_company_billing_street').val()) }
                if ($('#new_company_pc').val() == '') { $('#new_company_pc').val($('#new_company_billing_pc').val()) }
                if ($('#new_company_city').val() == '') { $('#new_company_city').val($('#new_company_billing_city').val()) }
                if ($('#new_company_country').val() == '') { $('#new_company_country').val($('#new_company_billing_country').val()) }
                
                company.parent().parent().removeClass( 'error' );
                $('#loading').remove();
            })
            .fail(function() {
                company.parent().parent().addClass( 'error' );
                $('#loading').remove();
            });
    });
    return true;
}
