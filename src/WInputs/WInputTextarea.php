<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 *
 */

namespace angelrove\membrillo2\WInputs;

class WInputTextarea
{
    //----------------------------------------------------------------
    public static function get($name, $value, $required=false, $title='')
    {
        $required = ($required) ? 'required' : '';
        $placeholder = ($title)? 'placeholder="'.$title.'"' : '';

        return '<textarea '.$placeholder.' name="'.$name.'" class="form-control" '.$required.'>'.$value.'</textarea>';

    }
    //----------------------------------------------------------------
}