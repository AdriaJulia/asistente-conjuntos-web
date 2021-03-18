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
         var territorio =   $('input[type=hidden][id=territorio').val();
        if (territorio!=null) {
            var pastesTerritorio = territorio.split(":");
            var descricion = "";
            var tipo = "";
            if (pastesTerritorio.length>0) {
                tipo = pastesTerritorio[0];
            }
            if (pastesTerritorio.length>1) {
                descricion = pastesTerritorio[1];
            }
            
            if (tipo == "CO") {
                $('input[type=radio][id=territorios_aragon]').prop('checked', true);
                CONTROL.cambio(true,false,false,false,false,true);
            } else if (tipo == "PR") {
                $('input[type=radio][id=territorios_provincia]').prop('checked', true);
                $('input[type=text][id=territorios_provincias]').val(descricion);
                CONTROL.cambio(false,true,false,false,false,true);
            } else if (tipo == "CM") {
                $('input[type=radio][id=territorios_comarca]').prop('checked', true);
                $('input[type=text][id=territorios_comarcas').val(descricion);
                CONTROL.cambio(false,false,true,false,false,true);
            } else if (tipo == "LO") {
                $('input[type=radio][id=territorios_localidad]').prop('checked', true);
                $('input[type=text][id=territorios_localidades').val(descricion);
                CONTROL.cambio(false,false,false,true,false,true);
            } else if (tipo == "OT") {
                $('input[type=radio][id=territorios_otro]').prop('checked', true); 
                $('input[type=text][id=territorios_otros').val(descricion);  
                CONTROL.cambio(false,false,false,false,true,true);
            }
        }   
    }                     
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
    $.getJSON( "/resources/localidades.json", function( availableLocalidades ) {
      $(".localidadesAutoComplete").autocomplete({
         source: availableLocalidades
       });
    });
});


CONTROL = {
   cambio: function(aragon, provincias, comarcas, localidades, otros, territorio) {
        if (aragon) {
            $('input[type=radio][id=territorios_provincia]').prop('checked', false);
            $('input[type=radio][id=territorios_comarca]').prop('checked', false);
            $('input[type=radio][id=territorios_localidad]').prop('checked', false);
            $('input[type=radio][id=territorios_otro]').prop('checked', false);

            $('input[type=text][id=territorios_provincias]').val('');
            $('input[type=text][id=territorios_comarcas').val('');
            $('input[type=text][id=territorios_localidades').val('');
            $('input[type=text][id=territorios_otros').val('');

        } else if (provincias) {
            $('input[type=radio][id=territorios_aragon]').prop('checked', false);
            $('input[type=radio][id=territorios_comarca]').prop('checked', false);
            $('input[type=radio][id=territorios_localidad]').prop('checked', false);
            $('input[type=radio][id=territorios_otro]').prop('checked', false);

            $('input[type=text][id=territorios_comarcas').val('');
            $('input[type=text][id=territorios_localidades').val('');
            $('input[type=text][id=territorios_otros').val('');

        } else if (comarcas) {

            $('input[type=radio][id=territorios_aragon]').prop('checked', false);
            $('input[type=radio][id=territorios_provincia]').prop('checked', false);
            $('input[type=radio][id=territorios_localidad]').prop('checked', false);
            $('input[type=radio][id=territorios_otro]').prop('checked', false);

            $('input[type=text][id=territorios_provincias]').val('');
            $('input[type=text][id=territorios_localidades').val('');
            $('input[type=text][id=territorios_otros').val('');

        } else if (localidades) {
            $('input[type=radio][id=territorios_aragon]').prop('checked', false);
            $('input[type=radio][id=territorios_provincia]').prop('checked', false);
            $('input[type=radio][id=territorios_comarca]').prop('checked', false);
            $('input[type=radio][id=territorios_otro]').prop('checked', false);

            $('input[type=text][id=territorios_provincias]').val('');
            $('input[type=text][id=territorios_comarcas').val('');
            $('input[type=text][id=territorios_otros').val('');
        } else {

            $('input[type=radio][id=territorios_aragon]').prop('checked', false);
            $('input[type=radio][id=territorios_provincia]').prop('checked', false);
            $('input[type=radio][id=territorios_comarca]').prop('checked', false);
            $('input[type=radio][id=territorios_localidad]').prop('checked', false); 

            $('input[type=text][id=territorios_provincias]').val('');
            $('input[type=text][id=territorios_comarcas').val('');
            $('input[type=text][id=territorios_localidades').val('');
        }
        if (!territorio) { 
           $('input[type=hidden][id=territorio').val('');
        }
    }
} 

$('input[type=radio][id=territorios_aragon]').change(function() {
     CONTROL.cambio(true,false,false,false,false,false);
     $('input[type=hidden][id=territorio').val('CO:Aragón');
}); 

$('input[type=radio][id=territorios_provincia]').change(function() {
    CONTROL.cambio(false,true,false,false,false,false);
}); 

$('input[type=radio][id=territorios_comarca]').change(function() {
    CONTROL.cambio(false,false,true,false,false,false);
}); 

$('input[type=radio][id=territorios_localidad]').change(function() {
    CONTROL.cambio(false,false,false,true,false,false);
}); 

$('input[type=radio][id=territorios_otro]').change(function() {
     CONTROL.cambio(false,false,false,false,true,false);
});

$('input[type=text][id=territorios_provincias]').focus(function() {
    CONTROL.cambio(false,true,false,false,false,false);
    $('input[type=radio][id=territorios_provincia]').prop('checked', true);
}); 

$('input[type=text][id=territorios_comarcas]').focus(function() {
    CONTROL.cambio(false,false,true,false,false,false);
    $('input[type=radio][id=territorios_comarca]').prop('checked', true);
}); 

$('input[type=text][id=territorios_localidades]').focus(function() {
    CONTROL.cambio(false,false,false,true,false,false);
    $('input[type=radio][id=territorios_localidad]').prop('checked', true);
}); 

$('input[type=text][id=territorios_otros]').focus(function() {
     CONTROL.cambio(false,false,false,false,true,false);
     $('input[type=radio][id=territorios_otro]').prop('checked', true);
});


$('input[type=text][id=territorios_provincias]').change(function() {
    var territorio = "PR:" + $('input[type=text][id=territorios_provincias]').val();
    $('input[type=hidden][id=territorio').val(territorio );
}); 

$('input[type=text][id=territorios_comarcas]').change(function() {
    var territorio = "CM:" + $('input[type=text][id=territorios_comarcas]').val();
    $('input[type=hidden][id=territorio').val(territorio) ;
}); 

$('input[type=text][id=territorios_localidades]').change(function() {
    var territorio = "LO:" + $('input[type=text][id=territorios_localidades]').val();
    $('input[type=hidden][id=territorio').val(territorio);
}); 

$('input[type=text][id=territorios_otros]').change(function() {
     var territorio = "OT:" + $('input[type=text][id=territorios_otros]').val();
    $('input[type=hidden][id=territorio').val(territorio);
});