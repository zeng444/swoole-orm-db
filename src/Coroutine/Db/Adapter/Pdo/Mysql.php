<?php

namespace Janfish\Swoole\Coroutine\Db\Adapter\Pdo;

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo as PdoAdapter;

class Mysql extends PdoAdapter
{

    private $_escapeChar = '`';

    /**
     * Author:Robert
     *
     * @param $str
     * @param null $escapeChar
     * @return string
     */
    public function escape($str, $escapeChar = null): string
    {
        if ($escapeChar == '') {
            $escapeChar = (string)$this->_escapeChar;
        }
        if (strpos($str, '.') === false) {
            if ($escapeChar != "" && $str != "*") {
                return $escapeChar.str_replace($escapeChar, $escapeChar.$escapeChar, $str).$escapeChar;
            }
            return $str;
        }
        $parts = (array)explode(".", trim($str, $escapeChar));
        $newParts = $parts;
        foreach ($parts as $key => $part) {
            if ($escapeChar == "" || $part == "" || $part == "*") {
                continue;
            }
            $newParts[$key] = $escapeChar.str_replace($escapeChar, $escapeChar.$escapeChar, $part).$escapeChar;
        }
        return implode(".", $newParts);
    }


    /**
     * Author:Robert
     *
     * @param $sqlQuery
     * @param int $number
     * @return string
     */
    public function limit($sqlQuery, $number): string
    {
        if (is_array($number)) {
            $sqlQuery .= " LIMIT ".$number[0];
            if (isset($number[1]) && strlen($number[1])) {
                $sqlQuery .= " OFFSET ".$number[1];
            }
            return $sqlQuery;
        }
        return $sqlQuery." LIMIT ".$number;
    }


}

