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
   if ((estado == 'VALIDADO')) {
        if (conAlineacion){
            $("#divapiaod").show();
            $("#divconfirma").hide();
        } else {
            $("#divapiaod").hide();
            $("#divconfirma").show();
        }
        $("#divmail").hide();
   } else {
        $("#divapiaod").hide();
        $("#divmail").show();
        $("#divconfirma").hide();
   }

   $("#formularioPublicacion").modal("show");
}

function MuestraAsistenteEstado(){
    var urlworkflow = $("#urlworkflow").val();
    urlworkflow = urlworkflow.replace('workflow/','workflow/noredirect/');
    $.ajax({
        type: "POST",
        url:  urlworkflow,
        cache:false,
        data: {"descripcion":"***SIN_CORREO***", estado:"EN_CORRECCION"},
        success: function( data ){
            window.location.href = data.data;
        },
        error: function( e ){
            alert("Error en el sistema. Póngase en contacto con el administrador");
        }
    });
 }