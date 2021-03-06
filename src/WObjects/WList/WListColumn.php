<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 *
 */

namespace angelrove\membrillo\WObjects\WList;

class WListColumn
{
    public $name;
    public $title;
    public $size;
    public $align;
    public $type;
    public $order;
    public $class;
    public $onClick;
    public $preventDefault;

    //-------------------------------------------------------
    public function __construct($name, $title, $size = '', $align = '', $type = '')
    {
        $this->name  = $name;
        $this->title = $title;
        $this->size  = $size;
        $this->align = $align;
        $this->type = $type;
    }
    //-------------------------------------------------------
    public function setType($type)
    {
        $this->type = $type;
    }
    //-------------------------------------------------------
    public function setWidth($size)
    {
        $this->size = $size;
    }
    //-------------------------------------------------------
    public function setOrder($field = '')
    {
        $this->order = (!$field) ? $this->name : $field;
    }
    //-------------------------------------------------------
    public function setClass()
    {
        $this->class = $this->name;
    }
    //-------------------------------------------------------
    public function setOnClick()
    {
        $this->onClick = $this->name;
    }
    //-------------------------------------------------------
    public function preventDefault()
    {
        $this->preventDefault = true;
    }
    //-------------------------------------------------------
}
