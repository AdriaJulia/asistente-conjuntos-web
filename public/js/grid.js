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
      case 'EN_ESPERA':
          value = 'En espera de validación';
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