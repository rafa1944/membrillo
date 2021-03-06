<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 */

namespace angelrove\membrillo\WObjects\WList;

use angelrove\front_components\Pagination;
use angelrove\membrillo\CrudUrl;
use angelrove\membrillo\WObjectsStatus\Event;
use angelrove\membrillo\WObjectsStatus\EventComponent;
use angelrove\membrillo\WApp\Local;
use angelrove\utils\CssJsLoad;
use angelrove\utils\Db_mysql;

class WList extends EventComponent
{
    private $sqlQuery;
    private $dbFields;
    private $title;
    private $showScroll;

    private $defaultSelected = false;
    private $msgConfirmDel   = '';
    private $rowsAdd = false;

    private $htmPaginacion;
    private $listRows;

    // Pagination
    private $paging_showOn  = 'top'; // top, bottom, false
    private $paging_numRows = 100;
    private $paging_config  = '';

    // Events
    private $event_new    = '';
    private $event_update = '';

    private $event_fOrder   = 'fieldOrder';
    private $event_fOnClick = 'fieldOnClick';
    private $event_numPage  = 'pagination';

    private $noId = false;

    // Buttons
    private $onClickRow = '';
    private $list_Op    = array();

    private $bt_new            = false;
    private $bt_update         = false;
    private $bt_detalle        = false;
    private $bt_delete         = false;
    private $bt_delete_confirm = true;

    // Interfaces
    private $rowEditor;
    private $cellEditor;
    private $optionsEditor;

    //-------------------------------------------------------
    // PUBLIC
    //-------------------------------------------------------
    /*
     * Params: basic, order
     */
    public function __construct($id_control, $sqlQ, array $dbFields, array $params = array())
    {
        //------
        parent::__construct($id_control);

        $this->sqlQuery = $sqlQ;
        $this->dbFields = $dbFields;

        //------
        // if (se ejecuta por 1º vez) { // inicializar datos
        //   $this->wObjectStatus->setDato('param_fieldPrev', '');
        // }

        //------
        if (isset($params['basic']) && $params['basic']) {
            return;
        }

        //------
        CssJsLoad::set(__DIR__ . '/style.css');
        CssJsLoad::set(__DIR__ . '/lib.js');

        //------
        $this->parse_event($this->WEvent);
    }
    //--------------------------------------------------------------
    public function setDefaultOrder($column, $order = 'ASC')
    {
        global $objectsStatus;

        $datos = $this->wObjectStatus->getDatos();
        if (isset($datos['param_field'])) {
            return;
        }

        //----
        $objectsStatus->setDato($this->id_object, 'param_field', $column);
        $objectsStatus->setDato($this->id_object, 'param_fieldPrev', $column);
        $objectsStatus->setDato($this->id_object, 'order_asc', $order);
    }
    //--------------------------------------------------------------
    public function parse_event($WEvent)
    {
        $datos = $this->wObjectStatus->getDatos();

        switch ($WEvent->EVENT) {
            //----------
            case $this->event_fOrder:
                // invertir la ordenación
                if (isset($datos['param_fieldPrev']) && $datos['param_fieldPrev'] == $datos['param_field']) {
                    $order_asc = (isset($datos['order_asc']) && $datos['order_asc'] == 'DESC') ? 'ASC' : 'DESC';

                    $this->wObjectStatus->setDato('order_asc', $order_asc);
                }

                $this->wObjectStatus->setDato('param_fieldPrev', $datos['param_field']);
                $this->wObjectStatus->delDato('id_page'); // reiniciar la paginación
                break;
            //----------
            case CRUD_LIST_SEARCH:
                // quitar la tupla seleccionada
                //$this->wObjectStatus->delRowId($this->id_object);

                // reiniciar la paginación
                $this->wObjectStatus->delDato('id_page');
                break;
            //----------
        }
    }
    //-------------------------------------------------------
    /* Eventos de otros controles List */
    private function parseEvents()
    {
        switch (Event::$EVENT) {
            case CRUD_LIST_DETAIL: // reiniciar la paginación
                $this->wObjectStatus->delDato('id_page');
                break;
        }
    }
    //--------------------------------------------------------------
    // Setter
    //-------------------------------------------------------
    public function set_title($title)
    {
        $this->title = $title;
    }
    //-------------------------------------------------------
    public function setListFields(array $dbFields)
    {
        $this->dbFields = $dbFields;
    }
    //-------------------------------------------------------
    /* Selected first by default */
    public function setDefaultSelected()
    {
        $this->defaultSelected = true;
    }
    //-------------------------------------------------------
    public function setReadOnly($isReadonly)
    {
        if ($isReadonly) {
            $this->bt_new    = false;
            $this->bt_delete = false;
        }
    }
    //-------------------------------------------------------
    public function setNoId()
    {
        $this->noId = true;
    }
    //-------------------------------------------------------
    public function setScroll($height)
    {
        $this->showScroll = true;
        $this->height     = $height;
    }
    //-------------------------------------------------------
    // Editors
    //-------------------------------------------------------
    public function setRowEditor(iWListRowEditor $rowEditor)
    {
        $this->rowEditor = $rowEditor;
    }
    //-------------------------------------------------------
    public function setCellEditor(iWListCellEditor $cellEditor)
    {
        $this->cellEditor = $cellEditor;
    }
    //-------------------------------------------------------
    public function setColumnEditor(iWListCellEditor $cellEditor)
    {
        $this->cellEditor = $cellEditor;
    }
    //-------------------------------------------------------
    public function setOptionsEditor(iWListCellOptionsEditor $optionsEditor)
    {
        $this->optionsEditor = $optionsEditor;
    }
    //-------------------------------------------------------
    // Events: CRUD
    //-------------------------------------------------------
    public function showNew($showButton = true)
    {
        $this->event_new = CRUD_EDIT_NEW;
        $this->bt_new    = $showButton;
    }
    //-------------------------------------------------------
    public function showUpdate($showButton = false)
    {
        $this->event_update = CRUD_EDIT_UPDATE;

        if ($showButton === true) {
            $this->bt_update = true;
            if (!$this->onClickRow) {
                // si no se ha asignado previamente
                $this->onClickRow = $this->event_update;
            }
        } else {
            $this->onClickRow = $this->event_update;
        }
    }
    //-------------------------------------------------------
    public function showDelete($isConfirm = true)
    {
        $this->bt_delete         = true;
        $this->bt_delete_confirm = $isConfirm;

        if ($this->bt_delete_confirm) {
            $this->msgConfirmDel = '¿Estás seguro?';
        }
    }
    //-------------------------------------------------------
    public function showDetail($showButton = true)
    {
        $this->bt_detalle = $showButton;

        if ($showButton !== true) {
            $this->onClickRow = CRUD_LIST_DETAIL;
        }
    }
    //-------------------------------------------------------
    // Events Other
    //-------------------------------------------------------
    public function setBtOpc($event, $label = '', $onClick = false, $title = '')
    {
        $this->list_Op[$event] = array(
            'event'    => $event,
            'oper'     => $event,
            'label'    => $label,
            'onClick'  => $onClick,
            'title'    => $title,
            'href'     => '',
            'target'   => '',
            'disabled' => '',
        );
    }
    //-------------------------------------------------------
    public function onClickRow($event)
    {
        $this->onClickRow = $event;
    }
    //-------------------------------------------------------
    // Paginación
    //-------------------------------------------------------
    // $position: top, bottom, false,
    // $config:   basic
    public function showPagination($position, $config = '', $numRows = 100)
    {
        $this->paging_showOn  = $position;
        $this->paging_config  = $config;
        $this->paging_numRows = $numRows;
    }
    //-------------------------------------------------------
    // deprecated !!
    public function setNumRowsPage($paging_numRows)
    {
        $this->paging_numRows = $paging_numRows;
    }
    //-------------------------------------------------------
    public function addRows(array $rows)
    {
        $this->rowsAdd = $rows;
    }
    //-------------------------------------------------------
    // OUT
    //-------------------------------------------------------
    public function get()
    {
        $controlID = $this->id_object;

        /** >> htmPaginacion, listRows **/
        list($this->htmPaginacion, $this->listRows) = $this->getData($this->sqlQuery);

        /** Add row **/
        if ($this->rowsAdd) {
            $this->listRows = $this->rowsAdd + $this->listRows;
        }

        /** Default selected **/
        if ($this->defaultSelected) {
            if (!$this->wObjectStatus->getRowId()) {
                $keyFirst = array_key_first($this->listRows);
                if ($keyFirst) {
                    $firstId = $this->listRows[$keyFirst]->id;
                    $this->wObjectStatus->setRowId($firstId);
                }
            }
        }

        /** Events **/
        $this->parseEvents();

        /** >> htmPaginacion **/
        if ($this->paging_showOn === 'false') {
            $this->htmPaginacion = '';
        }
        $htmPaginacion = &$this->htmPaginacion;

        /** >> $htmListDatos **/
        $htmListDatos = $this->getHtmRowsValues($this->listRows);

        /** >> $htmColumnas **/
        $htmColumnas = $this->getHead();

        /** OUT **/
        ob_start(); /* ¡¡Para retornar el contenido del include!! */
        include 'tmpl_list.inc';
        return ob_get_clean();
    }
    //--------------------------------------------------------------
    public function getData($sqlQuery)
    {
        $listDatos = array();
        $htmPaginacion = '';

        // Array ---
        if ($sqlQuery && is_array($sqlQuery)) {
            $listDatos = $sqlQuery;
        }
        // SQL -----
        elseif ($sqlQuery) {
            $sqlQ = $this->getQuery($sqlQuery);
            list($htmPaginacion, $listDatos) = $this->getPagination($sqlQ);
            // print_r2($sqlQ);
        }

        return [$htmPaginacion, $listDatos];
    }
    //--------------------------------------------------------------
    public function getDebug()
    {
        $sqlQ = $this->getQuery($this->sqlQuery);
        print_r2($sqlQ);

        $listDatos = Db_mysql::getList($sqlQ);
        print_r2($listDatos);
    }
    //--------------------------------------------------------------
    // Form search
    //--------------------------------------------------------------
    public static function searcher($id_object)
    {
        $action = CrudUrl::get(CRUD_LIST_SEARCH, $id_object);

        return <<<EOD
<form class="FormSearch form-inline well well-sm"
      role="search"
      name="search_form"
      method="get"
      action="$action">
EOD;
    }
    //--------------------------------------------------------------
    public static function searcher_END()
    {
        return '</form>';
    }
    //--------------------------------------------------------------
    public static function searcher_complet($id_object)
    {
        return
           self::searcher($id_object).
           self::inputSearch($id_object).
           self::searcher_END();
    }
    //-------------------------------------------------------
    public function getInputSearch($placeholder = '')
    {
        return self::inputSearch($this->id_object, $placeholder);
    }
    //-------------------------------------------------------
    public static function inputSearch($id_object, $placeholder = '')
    {
        $placeholder = ($placeholder)? $placeholder : Local::$t['Search'];

        //---
        global $objectsStatus;
        $value = $objectsStatus->getDato($id_object, 'f_text');

        //---
        $deltext = ($value)? '<a href="#" class="clear_search"><i class="fas fa-times-circle fa-lg" style="color:dimgray"></i></a>' : '';

        //---
        return <<<EOD
        <input type="text"
               class="form-control input-sm"
               name="f_text"
               placeholder="$placeholder"
               value="$value"> $deltext
        &nbsp;
        EOD;
    }
    //--------------------------------------------------------------
    //--------------------------------------------------------------
    public function formSearch()
    {
        echo self::searcher($this->id_object);
    }
    //--------------------------------------------------------------
    public function formSearch_END()
    {
        echo self::searcher_END();
    }
    //--------------------------------------------------------------
    public function formSearch_complet()
    {
        $f_text = $this->wObjectStatus->getDato('f_text');
        echo self::searcher_complet($this->id_object, $f_text);
    }
    //--------------------------------------------------------------
    public function formSearch_btBuscar()
    {
        echo '&nbsp;<button type="submit" class="btn btn-primary btn-sm">Buscar</button>';
    }
    //--------------------------------------------------------------
    // PRIVATE
    //-------------------------------------------------------
    private function getQuery($sqlQuery)
    {
        /** 'ORDER' **/
        $sqlOrder = '';

        // Ordenamiento de usuario
        $param_field = $this->wObjectStatus->getDato('param_field');
        if ($param_field) {
            $order_asc = $this->wObjectStatus->getDato('order_asc');

            $sqlOrder = ' ORDER BY '.$param_field . ' ' . $order_asc;
        }

        /** OUT **/
        $sqlQuery .= $sqlOrder;

        return $sqlQuery;
    }
    //--------------------------------------------------------------
    private function getPagination($sqlQuery)
    {
        $htmPaginacion = '';
        $rows          = '';

        // Páginas
        $urlFormat = CrudUrl::get(
            $this->event_numPage,
            $this->id_object,
            '',
            '',
            'id_page=[id_page]'
        );

        $id_page = $this->wObjectStatus->getDato('id_page');
        if (!$id_page) {
            $id_page = 1;
        }

        $htmPaginacion = new Pagination($sqlQuery, $this->paging_numRows, $id_page, Local::getLang());
        if ($this->paging_config == 'basic') {
            $htmPaginacion->setNumPages(3);
        } else {
            $htmPaginacion->setNumPages(10);
        }
        $htmPaginacion->setUrlFormat($urlFormat);

        $rows = $htmPaginacion->getListRows();
        // if(!$rows) {
        //    require('404.php');
        // }

        // HTML
        $listPaginas = $htmPaginacion->get();

        $numTotal  = $htmPaginacion->getNumRows();
        $str_desde = $htmPaginacion->getItemDesde();
        $str_hasta = $htmPaginacion->getItemHasta();

        $htmPaginacion = '';
        if ($this->paging_config == 'basic') {
            $htmPaginacion = $listPaginas;
        } else {
            $strPages = "($str_desde ".Local::$t['to']." $str_hasta) ".Local::$t['of']." <b>$numTotal</b>";

            $htmPaginacion = <<<EOD
                <div class="center-block2 clearfix">
                   <div class="pull-left">$listPaginas</div>
                   <div class="pull-right resumen">&nbsp; $strPages</div>
                </div>
                EOD;
        }

        return array($htmPaginacion, $rows);
    }
    //--------------------------------------------------------------
    // HEAD
    //--------------------------------------------------------------
    private function getHead()
    {
        $orderSimbols = [
            'none' => '<i class="material-icons md-18">drag_handle</i>',
            'down' => '<i class="material-icons">keyboard_arrow_down</i>',
            'up'   => '<i class="material-icons">keyboard_arrow_up</i>'
        ];

        /** Títulos de los campos **/
        $orderSimbol = ($this->wObjectStatus->getDato('order_asc') == 'DESC')? $orderSimbols['up'] :
                                                                               $orderSimbols['down'];
        $param_field = $this->wObjectStatus->getDato('param_field');

        $htmTitles = '';
        foreach ($this->dbFields as $dbField) {
            if (!$dbField) {
                continue;
            }

            // Campo de ordenación
            if ($dbField->order) {
                $simbol = ($param_field == $dbField->order) ? $orderSimbol : $orderSimbols['none'];
                $link = CrudUrl::get($this->event_fOrder, $this->id_object, '', '', 'param_field='.$dbField->order);

                $dbField->title = '<a class="btFieldOrder" href="'.$link.'">'.$simbol.$dbField->title.'</a>';
            }
            // OnClick
            if ($dbField->onClick) {
                $link = CrudUrl::get(
                    $this->event_fOnClick,
                    $this->id_object,
                    '',
                    '',
                    "onClick_field=$dbField->onClick&id_page=0"
                );

                $dbField->title = '<a class="btFieldOrder" href="' . $link . '">' . $dbField->title . '</a>';
            }

            // width
            $width = ($dbField->size) ? ' style="min-width:' . $dbField->size . 'px"' : '';
            // $width = '';

            // Out
            $htmTitles .= '<th id="fieldTitle_' . $dbField->name . '"' . $width . '>' . $dbField->title . '</th>';
        }

        /** Botón 'Nuevo...' **/
        $strBt_new = '';
        if ($this->bt_new) {
            $strBt_new = '<button type="button" class="on_new btn btn-primary btn-xs">'.
                            '<i class="fas fa-plus" aria-hidden="true"></i> New...'.
                         '</button>';
        }

        return $htmTitles . '<th class="optionsBar" id="List_cabButtons_' . $this->id_object . '">' . $strBt_new . '</th>';
    }
    //-------------------------------------------------------
    // Datos
    //-------------------------------------------------------
    private function getHtmRowsValues(array $rows)
    {
        if (!$rows) {
            return '';
        }

        // $id_selected ---
        $id_selected = $this->wObjectStatus->getRowId();

        /** TRs **/
        $htmList = '';
        $count   = 0;
        foreach ($rows as $id => $row) {
            /** RowEditor **/
            $row_bgColor = '';
            $row_class   = '';
            if (isset($this->rowEditor)) {
                $row_bgColor = $this->rowEditor->getBgColorAt($id, $id_selected, $row);
                $row_class   = $this->rowEditor->getClass($id, $id_selected, $row);
            }

            /** Datos **/
            $strCols = '';
            foreach ($this->dbFields as $dbField) {
                if (!$dbField) {
                    continue;
                }

                $strField = $this->getHtmField($id, $row, $dbField, $id_selected);
                if ($strField == "EXIT_ROW") {
                    $strCols = '';
                    break;
                }
                $strCols .= $strField;
            }
            if ($strCols == '') {
                continue;
            }

            /** Botones **/
            $strButtons = $this->getHtmButtons($id, $row, ++$count);

            /** Color de la tupla **/
            $styleSelected = ($id == $id_selected) ? ' class="success"' : '';
            $styleColor    = ($row_bgColor) ? " style=\"background:$row_bgColor\"" : '';
            $styleSelected = ($row_class) ? " class=\"$row_class\"" : $styleSelected;

            /** Tupla **/
            $htmList .= "\n<tr id='$id'" . $styleSelected . $styleColor . ">\n$strCols $strButtons\n</tr>";
        }

        return $htmList;
    }
    //-------------------------------------------------------
    private function getHtmField($id, $row, $dbField)
    {
        $f_value = @$row->{$dbField->name};

        $f_valueCampo = $f_value;
        $style_align  = ($dbField->align) ? 'text-align:' . $dbField->align . ';' : '';

        /** CellEditor **/
        $f_bgColor = '';
        $f_onClick = '';
        if (isset($this->cellEditor)) {
            $f_valueCampo = $this->cellEditor->getValueAt($id, $dbField->name, $f_value, $row);

            // getBgColorAt
            if ($f_bgColor = $this->cellEditor->getBgColorAt($id, $dbField->name, $f_value, $row)) {
                $f_bgColor = 'background:' . $f_bgColor . ';';
            }

            // getOnClick (deprecated??)
            $f_onClick = $this->cellEditor->getOnClick($id, $dbField->name, $f_value, $row);
            if ($f_onClick === true) {
                $f_onClick = ' onClickUser on_'.$dbField->name.' ';
            } elseif ($f_onClick) {
                $f_onClick = ' onClickUser ' . $f_onClick.' ';
            }
        }

        /** Parse data type **/
        switch ($dbField->type) {
            case 'boolean':
                $f_valueCampo = ($f_valueCampo)?'<i class="fas fa-check fa-lg"></i>' : '';
                break;

            case 'datetime':
                $f_valueCampo = date('d/m/Y H:i', strtotime($f_valueCampo));
                break;

            case 'date':
                $f_valueCampo = date('d/m/Y', strtotime($f_valueCampo));
                break;
        }

        /** prevent default **/
        $class_prevDef = ($dbField->preventDefault)? ' preventDefault ' : '';

        /** OUT **/
        $style = '';
        if ($style_align || $f_bgColor) {
            $style = ' style="' . $style_align . $f_bgColor . '"';
        }

        return '<td class="'.$f_onClick.$class_prevDef.' '.$dbField->class.'"' . $style . '>' . nl2br($f_valueCampo) . '</td>';
    }
    //-------------------------------------------------------
    // Buttons
    //-------------------------------------------------------
    private function getHtmButtons($id, $row, $numRow)
    {
        $htmButtons = array();

        /** Buttons **/
        if ($this->bt_delete) {
            $label                   = ''; // $label = <span>Delete</span>
            $htmButtons['bt_delete'] = '<button type="button" class="on_delete btn btn-xs btn-danger">'.
                                          '<i class="far fa-trash-alt fa-lg"></i>' . $label .
                                       '</button>';
            if ($this->optionsEditor && $this->optionsEditor->showBtDelete($id, $row) === false) {
                $htmButtons['bt_delete'] = '';
            }
        }

        if ($this->bt_update) {
            $label                   = ''; // $label = <span>Update</span>
            $htmButtons['bt_update'] = '<button type="button" class="on_update btn btn-xs btn-default">'.
                                          '<i class="far fa-edit fa-lg"></i>' . $label .
                                       '</button>';
            if ($this->optionsEditor && $this->optionsEditor->showBtUpdate($id, $row) === false) {
                $htmButtons['bt_update'] = '';
            }
        }

        if ($this->bt_detalle) {
            $label                    = ''; // $label = <span>Detalle</span>
            $htmButtons['bt_detalle'] = '<button type="button" class="on_detalle btn btn-xs btn-default">'.
                                          '<i class="fas fa-caret-right fa-lg"></i>' . $label .
                                        '</button>';
        }

        // Opcional ----
        foreach ($this->list_Op as $key => $bt_opc) {
            // optionsEditor ----
            $ret_optionsEditor = '';

            if ($this->optionsEditor) {
                $ret_optionsEditor = $this->optionsEditor->showBtOp($key, $id, $row);

                if ($ret_optionsEditor === false) {
                    // ocultar
                    continue;
                } elseif ($ret_optionsEditor === true) { // mostrar
                    // show
                } elseif (is_array($ret_optionsEditor)) {
                    // parámetros
                    if (isset($ret_optionsEditor['label'])) {
                        $bt_opc['label'] = $ret_optionsEditor['label'];
                    }
                    if (isset($ret_optionsEditor['disabled'])) {
                        $bt_opc['disabled'] = $ret_optionsEditor['disabled'];
                    }
                    $bt_opc['href']   = $ret_optionsEditor['href'];
                    $bt_opc['target'] = $ret_optionsEditor['target'];
                }
            }

            //--------
            $bt_href = CrudUrl::get($bt_opc['event'], $this->id_object, $id, $bt_opc['oper']);

            if ($bt_opc['href']) {
                $bt_href = $bt_opc['href'];
            }

            $bt_target = '';
            if ($bt_opc['target']) {
                $bt_target = $bt_opc['target'];
            }
            if ($bt_opc['disabled'] === true) {
                $htmButtons['op_' . $key] = '<span class="disabled_' . $key . '">' .
                                                $bt_opc['label'] .
                                            '</span>';
            } else {
                $htmButtons['op_' . $key] = "<a class='$key btn btn-primary btn-xs' ".
                                               "role='button' ".
                                               "href='$bt_href' ".
                                               "target='$bt_target' ".
                                               "title='$bt_opc[title]'>$bt_opc[label]</a>";
            }
        }
        // END Opcional ---

        /** Out **/
        return '<td class="optionsBar">' . implode('', $htmButtons) . '</td>';
    }
    //--------------------------------------------------------------
}
