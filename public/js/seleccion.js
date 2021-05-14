$(function() {
    $('select[name ="alineacionEntidad"]').change(function(e) {
       $('input[name ="modoFormulario"]').val("seleccion");
       $("button[class='btnEliminar']").each( function(){$(this).click()});
       $("#alineacionRelaciones").val("");
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
        var recargadoExito = true;
        jsoninical = jsoninical.replace(",}","}");
        if ((jsoninical.trim()!=="") && $('div[class ="atributosAsignados"]').find("input").length==0){
            var alineacionInicial = JSON.parse(jsoninical);
            var $alineacionEntidades = $("#alineacionEntidades").children().length;
            if ($alineacionEntidades>0) {
                $.each(alineacionInicial, function(object,value) {
                    if ($('div[name="' + object + '"]').length >=0) {
                        var boton = $('div[name="' + object + '"]').find("button");
                        var select = $('div[name="' + object + '"]').find("select");
                        if (select.length>0 && boton.length>0) {
                            select.val(value);
                            boton.click();
                        } else {
                            recargadoExito = false;  
                            $('input[name ="alineacionRelaciones"]').val("");
                        }
                    } 
                });
           } else if ((jsoninical.trim()!=="")){
                $('input[name ="modoFormulario"]').val("seleccion");
                $("form").submit();
                return false;
           } else {
             recargadoExito = false;
             $('input[name ="alineacionRelaciones"]').val("");
           }
        }

        if (recargadoExito) {
            $alineacionEntidades = $('input[name ="alineacionRelaciones"]').val().length;
            if (($alineacionEntidades >= 3) &&  ($('div[class ="atributosAsignados"]').find("input").length==0)) {
                 $('select[name ="alineacionEntidad"]').change();
            }
        }

        if ($("div[class='fieldsetContent'] > div").length >=0 ){
            $("div[class='fieldsetContent'] > div > ul").hide();
        }

        $("#descripcionEntidad").hide();
        $('label[for ="descripcionEntidad"]').hide();
        $("#descripcionEntidad_help").removeClass("help-text");
        $("div[class='form-group form-atributo']>div>div>ul").each( function(){$(this).hide()});
    }
});