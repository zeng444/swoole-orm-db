<?php

namespace Janfish\Swoole\Db\Criteria;

use Phalcon\Db;
use Phalcon\Di;

/**
 * 查询器
 * Author:Robert
 *
 * Class Finder
 * @package Janfish\Swoole\Criteria
 */
class Finder
{
    /**
     * @var
     */
    protected $dateColumns = [];

    /**
     * @var
     */
    protected $fullTextColumns = [];

    /**
     * @var
     */
    protected $columns;

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var
     */
    protected $schema;

    /**
     * @var
     */
    protected $table;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var int
     */
    protected $limit = 1;

    /**
     * @var array
     */
    protected $sql = [];

    /**
     * @var array
     */
    protected $bind = [];

    /**
     * @var
     */
    protected $db;

    /**
     * @var
     */
    protected $sort;

    /**
     * @var
     */
    private $hideColumns = [];

    /**
     * @var string
     */
    protected $mode = self::MYSQL_MODE;

    /**
     * @var
     */
    protected $returnData = Db::FETCH_ASSOC;

    /**
     *
     */
    const MYSQL_MODE = 'MYSQL';

    /**
     * TODO 暂不支持
     */
    const ES_MODE = 'ES';

    /**
     * Finder constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (isset($options['returnData'])) {
            $this->returnData = $options['returnData'];
        }
    }

    /**
     * 设置模式以后
     * Author:Robert
     *
     */
    public function setMode()
    {
        $this->mode = self::MYSQL_MODE;
        return $this;
    }

    /**
     * 设置schema
     * Author:Robert
     *
     * @param $schema
     * @return $this
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
        return $this;
    }


    /**
     * 设置排序
     * Author:Robert
     *
     * @param  $rule
     * @return $this
     */
    public function setSort($rule)
    {
        $this->sort = $rule;
        return $this;
    }

    /**
     * 查询数据表
     * Author:Robert
     *
     * @param $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 设置日期字段
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineDateColumns(array $columns)
    {
        $this->dateColumns = $columns;
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param $db
     * @return $this
     */
    public function setDbService($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * 设置返回的列
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * 设置全文字段
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineFullTextColumns(array $columns)
    {
        $this->fullTextColumns = $columns;
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineTypeColumns(array $columns)
    {
        if (isset($columns['date'])) {
            $this->defineDateColumns($columns['date']);
        }
        if (isset($columns['fullText'])) {
            $this->defineFullTextColumns($columns['fullText']);
        }
        return $this;
    }


    /**
     * 定义隐藏的列，SELECT *时不返回
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineHideColumns(array $columns)
    {
        $this->hideColumns = $columns;
        return $this;
    }

    /**
     * 设置分页
     * Author:Robert
     *
     * @param $offset
     * @param $limit
     * @return $this
     */
    public function setPagination($offset, $limit = null)
    {
        if ($limit === null) {
            $this->offset = 0;
            $this->limit = $offset;
        } else {
            $this->offset = $offset;
            $this->limit = $limit;
        }
        return $this;
    }

    /**
     * 设置查询条件
     * Author:Robert
     *
     * @param array $conditions
     * @return $this
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * 生成绑定参数
     * Author:Robert
     *
     * @return array
     */
    public function getBind(): array
    {
        return $this->bind;
    }

    /**
     * 生成sql
     * Author:Robert
     *
     * @return array
     */
    public function getSQL(): array
    {
        return $this->sql;
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    private function makeSortSQL(): string
    {
        if (!$this->sort) {
            return '';
        }
        if (is_array($this->sort)) {
            $sql = [];
            foreach ($this->sort as $column => $command) {
                $sql[] = "`$column` $command";
            }
            return implode(',', $sql);
        } else {
            return $this->sort;
        }
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    private function makeColumnSQL(): string
    {
        if (!$this->columns) {
            return '*';
        }
        $columns = [];
        foreach ($this->columns as $field => $alias) {
            if (is_int($field)) {
                $columns[] = "`{$alias}`";
            } else {
                $columns[] = "`{$field}` AS `{$alias}`";
            }
        }
        return implode(',', $columns);
    }

    /**
     * 生成查询参数
     * Author:Robert
     *
     * @return array
     */
    public function generateParams(): array
    {
        $whereSql = [];
        $bind = [
            'offset' => $this->offset,
            'limit' => $this->limit,
        ];
        foreach ($this->conditions as $column => $value) {
            if (in_array($column, $this->dateColumns)) {
                if (is_array($value)) {
                    $startValue = $value[0] ?? '';
                    $endValue = $value[1] ?? '';
                    if ($startValue) {
                        $startBind = $column.'0';
                        $whereSql[] = "`$column`>=:{$startBind}";
                        $bind[$startBind] = $startValue;
                    }
                    if ($endValue) {
                        $endBind = $column.'1';
                        $whereSql[] = "`$column`<=:{$endBind}";
                        $bind[$endBind] = $endValue;
                    }
                } else {
                    $whereSql[] = "`$column` = :$column";
                    $bind[$column] = $value;
                }
            } elseif (is_array($value) && $value) {
                $holder = [];
                if ((isset($value['in']) or isset($value['notIn']))) {
                    $eq = isset($value['in']) ? 'IN' : 'NOT IN';
                    $values = $eq === 'IN' ? $value['in'] : $value['notIn'];
                    foreach ($values as $key => $it) {
                        $holder[] = ':'.$column.$key;
                        $bind[$column.$key] = $it;
                    }
                    $whereSql[] = "`$column` $eq (".implode(',', $holder).")";
                } else {
                    foreach ($value as $key => $it) {
                        $holder[] = ':'.$column.$key;
                        $bind[$column.$key] = $it;
                    }
                    if ($holder) {
                        $whereSql[] = "`$column` IN (".implode(',', $holder).")";
                    }
                }
            } elseif (in_array($column, $this->fullTextColumns)) {
                $whereSql[] = "`$column` LIKE :$column";
                $bind[$column] = "%$value%";
            } else {
                $whereSql[] = "`$column` = :$column";
                $bind[$column] = $value;
            }
        }
        $whereSql = $whereSql ? implode(' AND ', $whereSql) : '';
        $columns = $this->makeColumnSQL();
        $schema = $this->schema ? "`{$this->schema}`." : '';
        $sort = $this->makeSortSQL();
        $this->sql['SELECT'] = $columns;
        $this->sql['FROM'] = "{$schema}`{$this->table}`";
        $this->sql['WHERE'] = $whereSql ? 'WHERE '.$whereSql : '';
        $this->sql['ORDER'] = $sort ? 'ORDER BY '.$sort : '';
        $this->sql['LIMIT'] = ":offset,:limit";
        $this->bind = $bind;
        return [$this->sql, $this->bind];
    }


    /**
     * Author:Robert
     *
     * @param array $items
     * @return array
     */
    public function removeHideColumns(array $items)
    {
        $rules = [];
        foreach ($this->hideColumns as $rule) {
            $rules[$rule] = 0;
        }
        return array_map(function ($item) use ($rules) {
            return array_diff_key($item, $rules);
        }, $items);

    }

    /**
     * Author:Robert
     *
     * @return mixed
     * @throws \Exception
     */
    public function count(): int
    {
        $fetchParams = $this->generateParams();
        if (!$this->db) {
            $di = Di::getDefault();
            $this->db = $di->get('db');
        }
        if (!$this->db) {
            throw new \Exception('db service not exist');
        }
        list($sqlData, $bind) = $fetchParams;
        $sql = "SELECT count(`id`) AS `count` FROM {$sqlData['FROM']} {$sqlData['WHERE']} LIMIT {$sqlData['LIMIT']}";
        $bind['limit'] = 1;
        $bind['offset'] = 0;
        $result = $this->db->fetchOne($sql, Db::FETCH_ASSOC, $bind, [
            'offset' => \PDO::PARAM_INT,
            'limit' => \PDO::PARAM_INT,
        ]);
        return $result['count'];
    }

    /**
     * 获取数据结果
     * Author:Robert
     *
     * @param int $returnType
     * @return array
     * @throws \Exception
     */
    public function execute($returnType = null): array
    {
        $fetchParams = $this->generateParams();
        if (!$this->db) {
            $di = Di::getDefault();
            $this->db = $di->get('db');
        }
        if (!$this->db) {
            throw new \Exception('db service not exist');
        }
        $returnType = $returnType ?: $this->returnData;
        list($sqlData, $bind) = $fetchParams;
        $sql = "SELECT {$sqlData['SELECT']} FROM {$sqlData['FROM']} {$sqlData['WHERE']} {$sqlData['ORDER']} LIMIT {$sqlData['LIMIT']}";
        $items = $this->db->fetchAll($sql, $returnType, $bind, [
            'offset' => \PDO::PARAM_INT,
            'limit' => \PDO::PARAM_INT,
        ]);
        if ($this->hideColumns) {
            return $this->removeHideColumns($items);
        }
        return $items;
    }

}
