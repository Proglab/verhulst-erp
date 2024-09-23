const $ = require('jquery');

$(function() {
    var company = $( "#Company_vat_number" );
    company.on('change', function() {

        $('#Company_vat_number').parent().append("<i class=\"fas fa-spinner fa-pulse\" id=\"loading\"></i>");

        var jqxhr = $.ajax( "/admin/fr?crudAction=getVatInfos&crudControllerFqcn=App%5CController%5CAdmin%5CCompanyCrudController&vat=" + company.val() )
            .done(function(result) {
                console.log(result)
                $( "#Company_name" ).val(result.company.name);
                $( "#Company_street" ).val(result.company.street);
                $( "#Company_pc" ).val(result.company.pc);
                $( "#Company_city" ).val(result.company.town);
                $( '#Company_country' ).val(result.country.isoCode.short);
                $( '#Company_vat_number' ).val(result.vat);
                $( "#Company_country-ts-control" ).trigger('click');
                function show_popup(){
                    var id = $( 'div[data-value=\"'+result.country.isoCode.short+'\"]' ).attr('id');
                    $( "#"+id ).trigger('click');
                };
                window.setTimeout( show_popup, 100 );
                company.parent().parent().removeClass( "has-error" );
                $("#loading").remove();
            })
            .fail(function() {
                company.parent().parent().addClass( "has-error" );
                $("#loading").remove();
            });
    });
});
