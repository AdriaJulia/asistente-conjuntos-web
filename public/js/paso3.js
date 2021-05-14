$(document).ready(function() {

    if ($(".container").attr("style") == "display:none") {
        $("#accesoDenegado").modal("show");
    }
    $("#botonCerrraAccesoDenegado").bind("click", function() { 
        window.location.href = "/asistentecamposdatos";
    });
    
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

/*
$('#etiquetas').on('itemAdded', function(event) {
    var tag = event.item;
        if (etiquetasAvalable.indexOf(tag) == -1) {
            alert('No es una etiqueta valida'); 
             $('#etiquetas').tagsinput('remove', tag, {preventPost: true});         
        } 
 });
*/





