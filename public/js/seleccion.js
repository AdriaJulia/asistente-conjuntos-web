$(function() {
    $("input:file").change(function (){
        $("#archivoCargado").attr("style","display:block");
        $('#archivoCargado > table > tbody > tr > td').text($("#selectorarchivo > input").val().replace(/C:\\fakepath\\/i, ''))
        $("input[class=rounded]").attr("style","display:revert"); 
    });
});

$(function() {
    $('select[name ="alineacionEntidad"]').change(function(e) {
       $('input[name ="modoFormulario"]').val("seleccion");
       $("button[class='btnEliminar']").each( function(){$(this).click()});
       $("#alineacionRelaciones").val("");  
       this.form.submit();
    });
});

$(function() {
    $('select[name ="tipoAlineacion"]').change(function() {
        var endpoint = "/" + this.value + "/";
        var url = window.location.href;
        var buscarxml = url.includes("/xml/");
        var buscarcampos = url.includes("/campos/");
        if (buscarxml) {
            url = url.replace('/xml/',endpoint)
        } else if (buscarcampos) {
            url = url.replace('/campos/',endpoint)
        }
        if (endpoint!=="//") {
           window.location.href = url;
        }
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
        if ($('select[name ="tipoAlineacion"]').val()=="campos"){
            var jsoninical = $('input[name ="alineacionRelaciones"]').val();
            var recargadoExito = true;
            jsoninical = jsoninical.replace(",}","}");
            jsoninical = jsoninical.replace("{}","");
            if ((jsoninical.trim()!=="") && $('div[class ="atributosAsignados"]').find("input").length==0){
                var alineacionInicial = JSON.parse(jsoninical);
                var $alineacionEntidades = $("#alineacionEntidades").children().length;
                if ($alineacionEntidades>0) {
                    $.each(alineacionInicial, function(object,value) {
                        value = value.split("&&&")[0];
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
                $("#descripcionEntidadId").show();
            }
    
            if ($("div[class='fieldsetContent'] > div").length >=0 ){
                $("div[class='fieldsetContent'] > div > ul").hide();
            }
            
            if ($("#descripcionEntidad").val()!==""){
                $("#descripcionEntidadId").show();
            } else {
                $("#descripcionEntidadId").hide();
            }
            $('textarea[name ="descripcionEntidad"]').hide();
            $("#descripcionEntidad_help").removeClass("help-text");
            $("div[class='form-group form-atributo']>div>div>ul").each( function(){$(this).hide()});
            if ($("#subtipo").val()!="") {
                $("#subtipoEntidad").val($("#subtipo").val());
            }
        }  
    }
    $("#actionButtons").show(); 
});