<?php
/**
 *
 */

namespace angelrove\membrillo2\Database;

use angelrove\membrillo2\Database\GenQuery;
use angelrove\utils\Db_mysql;

class ModelHelper
{
    //--------------------------------------------
    // CRUD
    //--------------------------------------------
    public static function rows($TABLE)
    {
        return self::read($TABLE);
    }

    public static function read($TABLE, array $filters=array(), $strict=false)
    {
        $sqlFiltros = self::getSqlFiltros($filters, $strict);

        return GenQuery::select($TABLE).$sqlFiltros;
    }

    //--------------------------------------------
    public static function findById($TABLE, $id, $asArray=true, $setHtmlSpecialChars = true)
    {
        $sql = GenQuery::selectRow($TABLE, $id);

        if ($asArray) {
            return Db_mysql::getRow($sql, $setHtmlSpecialChars);
        } else{
            return Db_mysql::getRowObject($sql, $setHtmlSpecialChars);
        }
    }

    public static function getValueById($TABLE, $id, $field)
    {
        $sql = GenQuery::selectRow($TABLE, $id);
        $data = Db_mysql::getRow($sql);

        return $data[$field];
    }

    public static function find($TABLE, array $filters, $strict=true)
    {
        $sqlFiltros = self::getSqlFiltros($filters, $strict);

        $sql = "SELECT * FROM " . $TABLE . $sqlFiltros." LIMIT 1";

        return Db_mysql::getRow($sql);
    }

    public static function findEmpty($TABLE)
    {
        $columns = Db_mysql::getListOneField("SHOW COLUMNS FROM " . $TABLE);
        foreach ($columns as $key => $value) {
            $datos[$key] = '';
        }

        return $datos;
    }
    //--------------------------------------------
    public static function create($TABLE, array $listValues=array())
    {
        return GenQuery::helper_insert($TABLE, $listValues);
    }
    //--------------------------------------------
    public static function update($TABLE, array $listValues=array(), $id = '')
    {
        return GenQuery::helper_update($TABLE, $listValues, $id);
    }
    //--------------------------------------------
    public static function delete($TABLE)
    {
        return GenQuery::delete($TABLE);
    }
    //--------------------------------------------
    public static function getSqlFiltros(array $filters, $strict=false)
    {
        $listWhere = array();
        foreach ($filters as $column => $filtro) {
            if ($strict == true) {
                $listWhere[] = " $column = '$filtro'";
            } else {
                $listWhere[] = " $column LIKE '%$filtro%'";
            }
        }
        $sqlFiltros = \angelrove\utils\UtilsBasic::implode(' AND ', $listWhere);

        if ($sqlFiltros) {
            $sqlFiltros = ' WHERE '.$sqlFiltros;
        }

        return $sqlFiltros;
    }
    //--------------------------------------------
}
