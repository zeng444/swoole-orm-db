<?php

namespace Janfish\Swoole\Db\Criteria\Finder;

use Janfish\Swoole\Db\Criteria\Finder;
use Phalcon\Db;
use Phalcon\Di;

/**
 * 查询器
 * Author:Robert
 *
 * Class Finder
 * @package Janfish\Swoole\Criteria
 */
class Condition
{
    /**
     *
     */
    const OR_OPERATOR_CHAR = 'OR';

    /**
     *
     */
    const AND_OPERATOR_CHAR = 'AND';

    /**
     * Author:Robert
     *
     * @var array
     */
    private $_conditions = [];

    /**
     * Author:Robert
     *
     * @var array
     */
    private $_bind = [];

    /**
     * Author:Robert
     *
     * @var array
     */
    private $_where = [];

    /**
     * @var
     */
    private $_dateColumns = [];
    /**
     * @var
     */
    public $_fullTextColumns = [];

    /**
     * Author:Robert
     *
     * @var array
     */
    private $bindFiled = [];

    /**
     * Condition constructor.
     * @param array $conditions
     * @param array $definer
     */
    public function __construct(array $conditions = [], array $definer = [])
    {
        $this->_conditions = $conditions;
        if (isset($definer['date'])) {
            $this->_dateColumns = $definer['date'];
        }
        if (isset($definer['fullText'])) {
            $this->_fullTextColumns = $definer['fullText'];
        }
    }

    /**
     * Author:Robert
     *
     * @param array $columns
     */
    public function defineDateColumns(array $columns)
    {

        $this->_dateColumns = $columns;
    }

    /**
     * Author:Robert
     *
     * @param array $columns
     */
    public function defineFullTextColumns(array $columns)
    {
        $this->_fullTextColumns = $columns;
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return string
     */
    public function where($field, $value)
    {
        $whereSql = [];
        if (in_array($field, $this->_dateColumns) && is_array($value)) {
            $startBind = $this->makeBindField($field);
            $endBind = $this->makeBindField($field);
            $whereSql[] = "`$field`>=:{$startBind}";
            $whereSql[] = "`$field`<=:{$endBind}";
            $this->_bind[$startBind] = $value[0] ?? '';
            $this->_bind[$endBind] = $value[1] ?? '';
        } elseif (in_array($field, $this->_fullTextColumns)) {
            $bindField = $this->makeBindField($field);
            $whereSql[] = "`$field` LIKE :$bindField";
            $this->_bind[$bindField] = "%$value%";
        } elseif (is_array($value)) {
            if (is_int(key($value))) {
                $value['in'] = $value;
            }
            $sign = isset($value['in']) ? 'IN' : 'NOT IN';
            $values = $sign === 'IN' ? $value['in'] : $value['notIn'];
            $holder = [];
            foreach ($values as $index => $it) {
                $bindField = $this->makeBindField($field);
                $holder[] = ':'.$bindField;
                $this->_bind[$bindField] = $it;
            }
            $whereSql[] = "`$field` $sign (".implode(',', $holder).")";
        } else {
            $bindField = $this->makeBindField($field);
            $whereSql[] = "`$field` = :$bindField";
            $this->_bind[$bindField] = $value;
        }
        return $whereSql ? implode(' AND ', $whereSql) : '';
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    public function generate()
    {
        foreach ($this->_conditions as $column => $value) {
            $this->_where[] = $this->where($column, $value);
        }
        return [
            implode($this->_where, ' AND '),
            $this->_bind,
        ];
    }

    /**
     * Author:Robert
     *
     * @param string $filed
     * @return string
     */
    private function makeBindField(string $filed): string
    {
        $this->bindFiled[$filed] = !isset($this->bindFiled[$filed]) ? 1 : $this->bindFiled[$filed] + 1;
        return $filed.$this->bindFiled[$filed];
    }


}