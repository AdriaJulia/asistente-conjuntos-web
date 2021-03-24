$(document).ready(function() {
    if ($(".container").attr("style") == "display:none") {
        $("#accesoDenegado").modal("show");
    }
    $("#botonCerrraAccesoDenegado").bind("click", function() { 
           window.location.href = "/asistentecamposdatos";
    });
});
$('#vocabularios').on('itemAdded', function(event) {
var tag = event.item;
if (!is_valid_url(tag)) {
      alert('No es una url');
      $('#vocabularios').tagsinput('remove', tag, {preventPost: true});
}
});

$('#servicios').on('itemAdded', function(event) {
var tag = event.item;
    if (!is_valid_url(tag)) {
        alert('No es una url'); 
         $('#servicios').tagsinput('remove', tag, {preventPost: true});         
    }
});

function is_valid_url(url) {
   return /^(http(s)?:\/\/)?(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test(url);
}