
$(document).ready(function()
{
   $(".WForm input").eq(0).focus(); // Focus en el 1º elemento
});

// Shortcuts ---------------------
$(document).keydown(function(e)
{
   //------------------------
   // Esc
   if(e.keyCode == 27)
   {
      if(scut_close) {
        WForm_close(scut_id_object);
      };
   }
   //------------------------
   // Ctrl+Enter
   if(e.keyCode == 13 && e.ctrlKey)
   {
      WForm_submit(scut_id_object, 'editUpdate');
   }
   //------------------------
});


//-------------------------------------------
function WForm_submit(id_object, event)
{
  var formEdit = document.getElementById('form_edit_'+id_object);

  if(event != '') {
     formEdit.EVENT.value = event;
  }

  // Submit
  //formEdit.action = './?CONTROL='+id_object+'&EVENT='+formEdit.EVENT.value+'&OPER='+formEdit.OPER.value+'&ROW_ID='+formEdit.ROW_ID.value;
  formEdit.action = './?CONTROL='+id_object+'&EVENT='+formEdit.EVENT.value+'&ROW_ID='+formEdit.ROW_ID.value;
  formEdit.submit();
}
//-------------------------------------------
function WForm_delete(id_object)
{
  var formEdit = document.getElementById('form_edit_'+id_object);

  formEdit.EVENT.value = 'form_delete';
  formEdit.OPER.value  = 'delete';

  // Submit
  formEdit.action = './?CONTROL='+id_object+'&EVENT='+formEdit.EVENT.value+'&OPER='+formEdit.OPER.value+'&ROW_ID='+formEdit.ROW_ID.value;

  var res = confirm("¿Estás seguro?");
  if(res == true) {
     formEdit.submit();
  } else {
     return false;
  }
}
//-------------------------------------------
function WForm_close(id_object)
{
  //var res = confirm("¿Seguro?");
  var res = true;
  if(res == true) {
     window.location = "?CONTROL=id_object&EVENT=form_close";
  }
  else {
     return false;
  }
}
//-------------------------------------------
