var table;
$(document).ready(function() {
    if ($("#esAdmnistrador")){
        table = $('#grid').DataTable({
            language: {
                url: '/theme/resources/datatable.es-es.json'
            },
            "order": [[ 5, "desc" ]]
        });
    } else {
        table = $('#grid').DataTable({
            language: {
                url: '/theme/resources/datatable.es-es.json'
            },
            "order": [[6, "desc" ]]
        });
    }

    $.each($('.th-fecha'), function(object,value){
        $(value).css("min-width","120px");
    });
    $.each($('.th-data'), function(object,value){
        $(value).css("min-width","150px");
    });

});

$('#search').on( 'keyup', function () {
    table.search( this.value ).draw();
});

$('#table-filter').change(function() {
 var value;
  switch ($(this).val()) {
      case 'TODOS':
          value = '.';
          break;
      case 'BORRADOR':
          value = 'Borrador';
          break;
      case 'EN_ESPERA_VALIDACION':
            value = 'En espera de validación';
            break;
      case 'EN_ESPERA_MODIFICACION':
          value = 'Solicitud de modificación';
          break;
      case 'VALIDADO':
          value = 'Validado';
          break;
      case 'EN_CORRECCION':
          value = 'En corrección';
          break;
      case 'DESECHADO':
          value = 'Desechado';
          break;        
      default:
          value = '*';
          break;
  }
  var column_index = 0;
  table.columns(column_index).search(value , true, false).draw();             
   // $(this).val() will work here
});

