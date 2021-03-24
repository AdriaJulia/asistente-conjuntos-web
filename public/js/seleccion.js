$(function() {
    $('select[name ="alineacionEntidad"]').change(function(e) {
       $('input[name ="modoFormulario"]').val("seleccion");
      this.form.submit();
    });
});
function enviar(valor) {
   $('input[name ="modoFormulario"]').val(valor);
   if ($('div[class ="atributosAsignados"]').find("input").length==0){
      $('select[id="alineacionEntidad"]').val("");
   }
   $(this).parents(".form").submit();
}

$(document).ready(function(e) {
    if ($(".container").attr("style") == "display:none") {
        $("#accesoDenegado").modal("show");
        $("#botonCerrraAccesoDenegado").bind("click", function() { 
               window.location.href = "/asistentecamposdatos";
        });
    } else {
        var jsoninical = $('input[name ="alineacionRelaciones"]').val();
        jsoninical = jsoninical.replace(",}","}");
        if ((jsoninical.trim()!=="") && $('div[class ="atributosAsignados"]').find("input").length==0){
            var alineacionInicial = JSON.parse(jsoninical);
            $.each(alineacionInicial, function(object,value) {
                var boton = $('div[name="' + object + '"]').find("button");
                var select = $('div[name="' + object + '"]').find("select");
                select.val(value);
                boton.click();
            });
        }
        $alineacionEntidades = $('input[name ="alineacionRelaciones"]').val().length;

        if (($alineacionEntidades >= 3) &&  ($('div[class ="atributosAsignados"]').find("input").length==0)) {
             $('select[name ="alineacionEntidad"]').change();
        }
    }

});