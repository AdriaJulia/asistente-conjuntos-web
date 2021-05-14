var urlworflow;
$(document).ready(function() {
    if ($(".container").attr("style") == "display:none") {
        $("#accesoDenegado").modal("show");
        $("#botonCerrraAccesoDenegado").bind("click", function() { 
               window.location.href = "/asistentecamposdatos";
        });
   } else {
        $('#grid').DataTable({
            language: {
                url: '/theme/resources/datatable.es-es.json'
            }
        });
        $("#formulariodatos").on('submit',function(e) {
            submitForm();
            return false;
      });
   } 
});

if ($("#muestraError").val()) {
   $("#camposErrormodal").modal("show");
}
function submitForm(){
    var urlworkflow = $("#urlworkflow").val();
    $.ajax({
        type: "POST",
        url: urlworkflow,
        cache:false,
        data: $('form#formulariodatos').serialize(),
        success: function(response){
            location.reload();
        },
        error: function( e ){
            alert("Error en el sistema. Póngase en contacto con el administrador");
        }
    });
}

function MuestraFormularioEstado(titulo, estado ){
   var urlworkflow = $("#urlworkflow").val();
   var conAlineacion = ($('#divTablaAlineacion').length>0);
   $('legend[id="tituloPopUp"]').text(titulo);
   $('input[name="estado"]').val(estado);
   $('#formulariodatos').attr('action', urlworkflow);
   if ((estado == 'VALIDADO') &&  (conAlineacion)){
     $("#divapiaod").show();
   } else{
     $("#divapiaod").hide();
   }
   $("#formularioPublicacion").modal("show");
}