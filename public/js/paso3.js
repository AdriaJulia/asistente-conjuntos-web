$(document).ready(function() {
    if ($(".container").attr("style") == "display:none") {
        $("#accesoDenegado").modal("show");
    }
    $("#botonCerrraAccesoDenegado").bind("click", function() { 
        window.location.href = "/asistentecamposdatos";
    });
});