$(document).ready(function() {
    var uri = window.location.href.split("?");
    var url = uri[0].split("/");
    var id = url[url.length-1];
    var ayudas = ['paso0_nuevo_conjunto',
                  'paso0_nueva_distribucion',
                  'paso1_contenido',
                  'paso2_contexto',
                  'paso3_origen_archivo',
                  'paso3_origen_url',
                  'paso3_origen_basedatos',
                  'paso4_integracion_seleccion',
                  'paso4_integracion_xml',
                  'listado_info',
                  'ficha_info'];
    if (ayudas.find(x=>x==id)!=undefined) {
       visible(id);
    } else {
       visible('descripcion_distribuciones');
    }
    if (!getUrlParameter('locationAnterior')){
         $.each($("a[id^='volver_']"), function (){
           $(this).attr("style","display:none");
         });
    }


});

function navega(id) {            
    $.each($("div[id^='ayuda_']"), function () {
        uri = window.location.origin + "/asistentecamposdatos/ayuda/" + id;
        if (uri!=location.href) {
            location.href = window.location.origin + "/asistentecamposdatos/ayuda/" + id;
        }
    });
}

function visible(id) {            
    $.each($("div[id^='ayuda_']"), function () {
        
        ids_padre = id.split('_');
        id_padre = ids_padre[0];
        if (($(this).attr('id') == "ayuda_".concat(id)) || ($(this).attr('id') == "ayuda_".concat(id_padre))) {
           $(this).attr("style","display:block");
        } else {
           $(this).attr("style","display:none");
        }
    });
}

function volver() {
    location.href = getUrlParameter('locationAnterior');
}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};