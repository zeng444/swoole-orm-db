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


    const EQUAL_DIRECTIVE = 'eq';
    const NOT_EQUAL_DIRECTIVE = 'neq';
    const GREATER_THAN_DIRECTIVE = 'gt';
    const LESS_THAN_DIRECTIVE = 'lt';
    const GREATER_THAN_EQUAL_DIRECTIVE = 'gte';
    const LESS_THAN_EQUAL_DIRECTIVE = 'lte';
    const IN_DIRECTIVE = 'in';
    const NOT_IN_DIRECTIVE = 'notIn';

    /**
     *
     */
    const DIRECTIVE_MAP = [
        self::EQUAL_DIRECTIVE => '=',
        self::NOT_EQUAL_DIRECTIVE => '<>',
        self::GREATER_THAN_DIRECTIVE => '>',
        self::LESS_THAN_DIRECTIVE => '<',
        self::GREATER_THAN_EQUAL_DIRECTIVE => '>=',
        self::LESS_THAN_EQUAL_DIRECTIVE => '<=',
        self::IN_DIRECTIVE => 'IN',
        self::NOT_IN_DIRECTIVE => 'NOT IN',
    ];

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
            if($value[0]){
                $startBind = $this->makeBindField($field);
                $whereSql[] = "`$field`>=:{$startBind}";
                $this->_bind[$startBind] = $value[0] ?? '';
            }
            if($value[1]){
                $endBind = $this->makeBindField($field);
                $whereSql[] = "`$field`<=:{$endBind}";
                $this->_bind[$endBind] = $value[1] ?? '';
            }
        } elseif (in_array($field, $this->_fullTextColumns)) {
            $bindField = $this->makeBindField($field);
            $whereSql[] = "`$field` LIKE :$bindField";
            $this->_bind[$bindField] = "%$value%";
        } elseif (is_array($value)) {
            $sign = key($value);
            if (is_int($sign)) {
                $sign = self::IN_DIRECTIVE;
                $value[$sign] = $value;
            }
            if (in_array($sign, [self::IN_DIRECTIVE, self::NOT_IN_DIRECTIVE])) {
                $holder = [];
                foreach ($value[$sign] as $index => $it) {
                    $bindField = $this->makeBindField($field);
                    $holder[] = ':'.$bindField;
                    $this->_bind[$bindField] = $it;
                }
                $whereSql[] = "`$field` ".self::DIRECTIVE_MAP[$sign]." (".implode(',', $holder).")";
            } elseif (in_array($sign, [
                self::EQUAL_DIRECTIVE,
                self::NOT_EQUAL_DIRECTIVE,
                self::GREATER_THAN_DIRECTIVE,
                self::LESS_THAN_DIRECTIVE,
                self::GREATER_THAN_EQUAL_DIRECTIVE,
                self::LESS_THAN_EQUAL_DIRECTIVE,
            ])) {
                $bindField = $this->makeBindField($field);
                $whereSql[] = "`$field` ".self::DIRECTIVE_MAP[$sign]." :$bindField";
                $this->_bind[$bindField] = $value[$sign];
            }
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