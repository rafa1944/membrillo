<?
/**
* WEvent: current event
*/

namespace angelrove\membrillo\WObjectsStatus;

use angelrove\membrillo\WApp\Session;


class Event
{
  public  static $CONTROL;
  public  static $EVENT;
  private static $EVENT_PREV;
  public  static $OPER;
  public  static $ROW_ID;

  //----------------------------------------------------------------------------
  public static function updateEvent()
  {
    // No hay evento ---
    if(!isset($_REQUEST['CONTROL']) || !isset($_REQUEST['EVENT'])) {
       return;
    }

    // Current event ----
    self::setEvent($_REQUEST['EVENT']);

    self::$CONTROL = $_REQUEST['CONTROL'];
    self::$OPER    = (isset($_REQUEST['OPER']))?   $_REQUEST['OPER']   : '';
    self::$ROW_ID  = (isset($_REQUEST['ROW_ID']))? $_REQUEST['ROW_ID'] : '';

    // wObjectStatus ----
    global $objectsStatus;
    $wObjectStatus = $objectsStatus->setNewObject(self::$CONTROL); // por si no existe
    $wObjectStatus->updateDatos();
  }
  //----------------------------------------------------------------------------
  // EVENT
  //----------------------------------------------------------------------------
  // in session
  private static function setEvent($event) {
    Session::set('WEvent_EVENT_PREV', Session::get('WEvent_EVENT'));
    Session::set('WEvent_EVENT', $event);

    self::$EVENT_PREV = Session::get('WEvent_EVENT_PREV');
    self::$EVENT      = $event;
  }
  //----------------------------------------------------------------------------
  public static function revertEvent() {
    Session::set('WEvent_EVENT', Session::get('WEvent_EVENT_PREV'));

    self::$EVENT = Session::get('WEvent_EVENT_PREV');
  }
  //----------------------------------------------------------------------------
  // ROW_ID
  //----------------------------------------------------------------------------
  public static function delRowId()
  {
    global $objectsStatus;

    self::$ROW_ID = '';
    if($wo = $objectsStatus->getObject(self::$CONTROL)) {
       $wo->delRowId();
    }
  }
  //----------------------------------------------------------------------------
  public static function setRowId($value)
  {
    global $objectsStatus;

    self::$ROW_ID = $value;
    if($wo = $objectsStatus->getObject(self::$CONTROL)) {
       $wo->setRowId($value);
    }
  }
  //----------------------------------------------------------------------------
}
