$(document).ready(function() { 
    if ($(".container").attr("style") == "display:none") {
        $("#accesoDenegado").modal("show"); 
        $("#botonCerrraAccesoDenegado").bind("click", function() { 
               window.location.href = "/asistentecamposdatos";
        });
    } else {
        $('.datepicker').datepicker({
            isRTL: false,
            dateFormat:'yy-mm-dd',
            autoclose:true,
        });
        var coberturaGeografica = $('input[type=hidden][id=coberturaGeografica').val();
        if (coberturaGeografica!=null) {
            var pastesCoberturaGeografica = coberturaGeografica.split(":");
            var descricion = "";
            var tipo = "";
            if (pastesCoberturaGeografica.length>0) {
                tipo = pastesCoberturaGeografica[0];
            }
            if (pastesCoberturaGeografica.length>1) {
                descricion = pastesCoberturaGeografica[1];
            }
            
            if (tipo == "CO") {
                $('input[type=radio][id=coberturasGeograficas_aragon]').prop('checked', true);
                CONTROL.cambio(true,false,false,false,false,true);
            } else if (tipo == "PR") {
                $('input[type=radio][id=coberturasGeograficas_provincia]').prop('checked', true);
                $('input[type=text][id=coberturasGeograficas_provincias]').val(descricion);
                CONTROL.cambio(false,true,false,false,false,true);
            } else if (tipo == "CM") {
                $('input[type=radio][id=coberturasGeograficas_comarca]').prop('checked', true);
                $('input[type=text][id=coberturasGeograficas_comarcas').val(descricion);
                CONTROL.cambio(false,false,true,false,false,true);
            } else if (tipo == "MU") {
                $('input[type=radio][id=coberturasGeograficas_municipio]').prop('checked', true);
                $('input[type=text][id=coberturasGeograficas_municipios').val(descricion);
                CONTROL.cambio(false,false,false,true,false,true);
            } else if (tipo == "OT") {
                $('input[type=radio][id=coberturasGeograficas_otro]').prop('checked', true); 
                $('input[type=text][id=coberturasGeograficas_otros').val(descricion);  
                CONTROL.cambio(false,false,false,false,true,true);
            } else {
                $('input[type=radio][id=coberturasGeograficas_aragon]').prop('checked', true);
                CONTROL.cambio(true,false,false,false,false,false); 
            }
        } 
    } 
    $("#actionButtons").show();                   
});
$(function() {
   $.getJSON( "/resources/provincias.json", function( availableProvincias ) {
      $(".povinciasAutoComplete").autocomplete({
         source: availableProvincias
       });
    });
});
$(function() {
    $.getJSON( "/resources/comarcas.json", function( availableComarcas ) {
      $(".comarcasAutoComplete").autocomplete({
         source: availableComarcas
       });
    });
});
$(function() {
    $.getJSON( "/resources/localidades.json", function( availableMunicipios ) {
      $(".municipiosAutoComplete").autocomplete({
         source: availableMunicipios
       });
    });
});


CONTROL = {
   cambio: function(aragon, provincias, comarcas, municipios, otros, coberturaGeografica) {
        if (aragon) {
            $('input[type=radio][id=coberturasGeograficas_provincia]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_comarca]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_municipio]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_otro]').prop('checked', false);

            $('input[type=text][id=coberturasGeograficas_provincias]').val('');
            $('input[type=text][id=coberturasGeograficas_comarcas').val('');
            $('input[type=text][id=coberturasGeograficas_municipios').val('');
            $('input[type=text][id=coberturasGeograficas_otros').val('');

        } else if (provincias) {
            $('input[type=radio][id=coberturasGeograficas_aragon]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_comarca]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_municipio]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_otro]').prop('checked', false);

            $('input[type=text][id=coberturasGeograficas_comarcas').val('');
            $('input[type=text][id=coberturasGeograficas_municipios').val('');
            $('input[type=text][id=coberturasGeograficas_otros').val('');

        } else if (comarcas) {

            $('input[type=radio][id=coberturasGeograficas_aragon]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_provincia]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_municipio]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_otro]').prop('checked', false);

            $('input[type=text][id=coberturasGeograficas_provincias]').val('');
            $('input[type=text][id=coberturasGeograficas_municipios').val('');
            $('input[type=text][id=coberturasGeograficas_otros').val('');

        } else if (municipios) {
            $('input[type=radio][id=coberturasGeograficas_aragon]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_provincia]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_comarca]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_otro]').prop('checked', false);

            $('input[type=text][id=coberturasGeograficas_provincias]').val('');
            $('input[type=text][id=coberturasGeograficas_comarcas').val('');
            $('input[type=text][id=coberturasGeograficas_otros').val('');
        } else {

            $('input[type=radio][id=coberturasGeograficas_aragon]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_provincia]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_comarca]').prop('checked', false);
            $('input[type=radio][id=coberturasGeograficas_municipio]').prop('checked', false); 

            $('input[type=text][id=coberturasGeograficas_provincias]').val('');
            $('input[type=text][id=coberturasGeograficas_comarcas').val('');
            $('input[type=text][id=coberturasGeograficas_municipios').val('');
        }
        if (!coberturaGeografica) { 
           $('input[type=hidden][id=coberturaGeografica').val('CO:Aragón');
        }
    }
} 

$('input[type=radio][id=coberturasGeograficas_aragon]').change(function() {
     CONTROL.cambio(true,false,false,false,false,false);
     $('input[type=hidden][id=coberturaGeografica').val('CO:Aragón');
}); 

$('input[type=radio][id=coberturasGeograficas_provincia]').change(function() {
    CONTROL.cambio(false,true,false,false,false,false);
}); 

$('input[type=radio][id=coberturasGeograficas_comarca]').change(function() {
    CONTROL.cambio(false,false,true,false,false,false);
}); 

$('input[type=radio][id=coberturasGeograficas_municipio]').change(function() {
    CONTROL.cambio(false,false,false,true,false,false);
}); 

$('input[type=radio][id=coberturasGeograficas_otro]').change(function() {
     CONTROL.cambio(false,false,false,false,true,false);
});

$('input[type=text][id=coberturasGeograficas_provincias]').focus(function() {
    CONTROL.cambio(false,true,false,false,false,false);
    $('input[type=radio][id=coberturasGeograficas_provincia]').prop('checked', true);
}); 

$('input[type=text][id=coberturasGeograficas_comarcas]').focus(function() {
    CONTROL.cambio(false,false,true,false,false,false);
    $('input[type=radio][id=coberturasGeograficas_comarca]').prop('checked', true);
}); 

$('input[type=text][id=coberturasGeograficas_municipios]').focus(function() {
    CONTROL.cambio(false,false,false,true,false,false);
    $('input[type=radio][id=coberturasGeograficas_municipio]').prop('checked', true);
}); 

$('input[type=text][id=coberturasGeograficas_otros]').focus(function() {
     CONTROL.cambio(false,false,false,false,true,false);
     $('input[type=radio][id=coberturasGeograficas_otro]').prop('checked', true);
});


$('input[type=text][id=coberturasGeograficas_aragon]').change(function() {
    var coberturaGeografica = "CO:" + $('input[type=text][id=coberturasGeograficas_aragon]').val();
    $('input[type=hidden][id=coberturaGeografica').val(coberturaGeografica );
}); 


$('input[type=text][id=coberturasGeograficas_provincias]').change(function() {
    var coberturaGeografica = "PR:" + $('input[type=text][id=coberturasGeograficas_provincias]').val();
    $('input[type=hidden][id=coberturaGeografica').val(coberturaGeografica );
}); 

$('input[type=text][id=coberturasGeograficas_comarcas]').change(function() {
    var coberturaGeografica = "CM:" + $('input[type=text][id=coberturasGeograficas_comarcas]').val();
    $('input[type=hidden][id=coberturaGeografica').val(coberturaGeografica) ;
}); 

$('input[type=text][id=coberturasGeograficas_municipios]').change(function() {
    var coberturaGeografica = "MU:" + $('input[type=text][id=coberturasGeograficas_municipios]').val();
    $('input[type=hidden][id=coberturaGeografica').val(coberturaGeografica);
}); 

$('input[type=text][id=coberturasGeograficas_otros]').change(function() {
     var coberturaGeografica = "OT:" + $('input[type=text][id=coberturasGeograficas_otros]').val();
    $('input[type=hidden][id=coberturaGeografica').val(coberturaGeografica);
});


 $('input[type=checkbox][id=coberturaIdioma_lenguajes_4]').change(function() {
    $('input[type=text][id=coberturaIdioma_otroslenguajes]').val('')
 }); 

 $('input[type=text][id=coberturaIdioma_otroslenguajes]').change(function() {
    var otroidioma =  $('input[type=text][id=coberturaIdioma_otroslenguajes]').val();
    if (otroidioma==""){
        $('input[type=checkbox][id=coberturaIdioma_lenguajes_4]').prop('checked', false);
    } else {
        $('input[type=checkbox][id=coberturaIdioma_lenguajes_4]').prop('checked', true);
    }

});

$(function() {
    $('#divetiquetas > div > input[type=text]').attr('style', 'width: 300px !important');
});

var etiquetasAvalable 
$(function() {
    var settings = {
        'cache': false,
        'dataType': "jsonp",
        'timeout' : 3000,
        "async": true,
        "crossDomain": true,
        "url": $('#urltags').val(),
        "headers": {
            "accept": "application/json",
            "Access-Control-Allow-Origin":"*"
        }
    }
 
    $.ajax(settings).done(function(response) {
        etiquetasAvalable = response["result"];
        $("#divetiquetas > div > input[type=text]").autocomplete({source: etiquetasAvalable});
      }).fail(function(data){
         $('#loadingDiv').hide();
      });
});
