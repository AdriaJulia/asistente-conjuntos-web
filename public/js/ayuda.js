$(document).ready(function() {
    var uri = window.location.href.split("?");
    var url = uri[0].split("/");
    var id = url[url.length-1];
    var ayudas = [11,12,13,21,22,23,31,41,42,51,52];
    if (ayudas.find(x=>x==id)!=undefined) {
       visible(id);
    } else {
       visible(11);
    }
    if (!getUrlParameter('locationAnterior')){
         $.each($("a[id^='volver_']"), function (){
           $(this).attr("style","display:none");
         });
    }


});

function visible(id) {            
    $.each($("div[id^='ayuda_']"), function () {
        id_padre =  id - (id % 10);
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

function solicitarSoporte() {
    location.href = "/asistentecamposdatos/ayuda/soporte";
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