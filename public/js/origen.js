$("#tipoOrigen").change(function () {
    var endpoint = this.value;
    var url = window.location.href;
    var buscarurl = url.includes("/url/");
    var buscarfile = url.includes("/file/");
    var buscardatabase = url.includes("/database/");
    if (buscarurl) {
        url = url.replace('url',endpoint)
    } else if (buscarfile) {
        url = url.replace('file',endpoint)
    } else if (buscardatabase) { 
        url = url.replace('database',endpoint)
    }
    window.location.href = url;
});
$("#tipoBaseDatos").change(function () {
    var tipo = this.value;
    if (tipo=="ORACLE") {
        $('label[for ="esquema"]').text("Esquema"); 
    } 
});

if ($("#muestaModal").val()==="") {
    $("#camposErrormodal").modal("show");
}

if ($('#existe').val() == "1") {
   $("#camposErrormodal").modal("show");
}

$("#siguiente").on("click", function(e){
   $('input[name ="modoFormulario"]').val("insert");    
   $(this).parents(".form").submit();
});

$("#borrarArchivoActual").on("click", function(e){
   $('input[name ="modoFormulario"]').val("test");  
   $("#siguiente").attr("style","display:none");
   $("#archivoActual").attr("style","display:none");
   $("input[class=rounded]").attr("style","display:revert");  
   $("div[class=ok]").attr("style","display:none"); 
   $("div[class=ko]").attr("style","display:none"); 
});

$(function() {
    $("input:file").change(function (){
        $("#archivoActual").attr("style","display:none");
        $("#archivoCargado").attr("style","display:block");
        $('#archivoCargado > table > tbody > tr > td').text($("#selectorarchivo > input").val().replace(/C:\\fakepath\\/i, ''))
        $("#lineaArchivoActual").attr("style","display:none");
        $("input[class=rounded]").attr("style","display:revert"); 
        $("input[id=siguiente]").attr("style","display:none");
        $("div[class=ok]").attr("style","display:none"); 
        $("div[class=ko]").attr("style","display:none");
    });
});

$(document).ready(function() {
    $("#archivoCargado").attr("style","display:none");
    if ($(".container").attr("style") == "display:none") {
        $("#accesoDenegado").modal("show");
        $("#botonCerrraAccesoDenegado").bind("click", function() { 
            window.location.href = "/asistentecamposdatos";
        });
    } else {
        if ($("#lineaArchivoActual").text() !== "") {
            $("input[class=rounded]").attr("style","display:none");        
        }
        $('html,body').animate({scrollTop: document.body.scrollHeight},"fast");
    }
    if ($("div[class=ko]").text()!=""){
        $('input[name ="modoFormulario"]').val("test"); 
    }
    var tipo = $("#tipoBaseDatos").value;
    if (tipo=="ORACLE") {
        $('label[for ="esquema"]').text("Esquema"); 
    } 
});