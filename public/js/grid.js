var table;
$(document).ready(function() {
   table = $('#grid').DataTable({
        language: {
            url: '/theme/resources/datatable.es-es.json'
        }
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
          value = 'En borrador';
          break;
      case 'EN_ESPERA_PUBLICACION':
            value = 'En espera validación';
            break;
      case 'EN_ESPERA_MODIFICACION':
          value = 'En espera modificación';
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