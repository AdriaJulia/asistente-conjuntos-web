var $loading = $('#loadingDiv').hide();
$(document)
  .ajaxStart(function () {
    $loading.show();
  })
  .ajaxStop(function () {
    $loading.hide();
});
$("input[name='comprobaro_rigen']").click(function(){
    $loading.show();
});

$("input[id='siguiente']").click(function(){
    $loading.show();
});




