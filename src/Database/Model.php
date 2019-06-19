<?php
namespace angelrove\membrillo\Database;

use angelrove\membrillo\Database\ModelInterface;
use angelrove\membrillo\Database\GenQuery;
use angelrove\utils\Db_mysql;

class Model implements ModelInterface
{
    /*
     * Sample $conditions param:
        $conditions[] = "id <> 1";

        // Search ---
        $conditions['f_text'] = "name LIKE '%[VALUE]%'";
        $conditions['f_status'] = [
            'default' => "deleted_at IS NULL",
            'deleted' => "deleted_at IS NOT NULL",
        ];
     */
    public static function read(array $filter_conditions=array(), array $filter_data=array())
    {
        if (static::CONF['soft_delete'] && !$filter_conditions) {
            $filter_conditions[] = 'deleted_at IS NULL';
        }
        $sqlFilters = GenQuery::getSqlFilters($filter_conditions, $filter_data);
        // print_r2($sqlFilters);

        return GenQuery::select(static::CONF['table']).$sqlFilters;
    }

    public static function findById($id, $asArray=true, $setHtmlSpecialChars = true)
    {
        $sql = GenQuery::selectRow(static::CONF['table'], $id);

        if ($asArray) {
            return Db_mysql::getRow($sql, $setHtmlSpecialChars);
        } else{
            return Db_mysql::getRowObject($sql, $setHtmlSpecialChars);
        }
    }

    public static function getValueById($id, $field)
    {
        $sql = GenQuery::selectRow(static::CONF['table'], $id);
        $data = Db_mysql::getRow($sql);

        return $data[$field];
    }

    public static function find(array $filter_conditions)
    {
        $sqlFilters = GenQuery::getSqlFilters($filter_conditions);

        $sql = "SELECT * FROM " . static::CONF['table'] . $sqlFilters." LIMIT 1";

        return Db_mysql::getRow($sql);
    }

    public static function findEmpty()
    {
        $columns = Db_mysql::getListOneField("SHOW COLUMNS FROM " . static::CONF['table']);
        foreach ($columns as $key => $value) {
            $datos[$key] = '';
        }

        return $datos;
    }

    public static function create(array $listValues=array())
    {
        return GenQuery::helper_insert(static::CONF['table'], $listValues);
    }

    public static function update(array $listValues=array(), $id='')
    {
        return GenQuery::helper_update(static::CONF['table'], $listValues, $id);
    }

    public static function delete()
    {
        if (static::CONF['soft_delete']) {
            return GenQuery::softDelete(static::CONF['table']);
        } else {
            return GenQuery::delete(static::CONF['table']);
        }
    }
}
