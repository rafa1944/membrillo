<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 *
 */

namespace angelrove\membrillo2\WPage;

use angelrove\utils\CssJsLoad;


class Frame
{
  //------------------------------------------------------------------
  public static function get($title='', $showClose=false, $linkClose='')
  {
   if(!$linkClose) {
      $linkClose = './';
   }

      CssJsLoad::set_script('
   var WFrame_showClose = '.($showClose? "true" : "false").';

   $(document).ready(function() {
     //-----------------
     // Ocultar "Cerrar" solo si no existe un botón "Cerrar" del form no muestra el X de cerrar del frame
     if(WFrame_showClose == false) {
        if($("#WForm_btClose").length == 0) {
           $(".WFrame .close").hide();
        }
     }
     //-----------------
     $(".WFrame .close").click(function() {
       Frame_close();
     });
     //-----------------
   });

   // Esc --------------
   $(document).keydown(function(e) {
     if(WFrame_showClose == true && e.keyCode == 27) {
        Frame_close();
     }
   });

   //-------------------
   function Frame_close() {
      window.location = "'.$linkClose.'";
   }
   //-------------------

  ', 'WFrame');
     ?>

    <!-- WFrame -->
    <div class="WFrame panel panel-default">
        <div class="panel-heading">
          <? if($showClose) { ?>
            <button class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <? } ?>
          <? if($title) { ?>
            <div class="panel-title"><?=$title?> &nbsp;</div>
          <? } ?>
        </div>

      <!-- body -->
      <div class="panel-body">
      <?

  }
  //----------------------
  public static function get_end()
  {
      ?>
      </div>
      <!-- /body -->

    </div>
    <!-- /WFrame -->
    <?
  }
  //------------------------------------------------------------------
}
