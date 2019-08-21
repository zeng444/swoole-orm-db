<?php

namespace Janfish\Swoole\Coroutine\Db\Adapter\Pdo;

use Janfish\Swoole\Coroutine\Db\Adapter as DBAdapter;
use Swoole\Coroutine\MySQL as CoroutineMySQL;

/**
 * Author:Robert
 *
 * Class Mysql
 * @package Janfish\Swoole\Coroutine\Db\Adapter\Pdo
 */
class Mysql extends DBAdapter
{


    /**
     * Author:Robert
     *
     * @var array
     */
    protected $_descriptor = [];


    /**
     * Author:Robert
     *
     * @var int
     */
    protected $_transactionLevel = 0;


    /**
     * Author:Robert
     *
     * @var bool
     */
    protected $_transactionsWithSavepoints = false;

    /**
     * @var string
     */
    protected $_escapeChar = '`';


    /**
     * Pdo constructor.
     * @param array $descriptor
     */
    public function __construct(array $descriptor)
    {
        $this->connect($descriptor);
    }


    /**
     * Author:Robert
     *
     * @param string $statement
     * @param array|null $placeholders
     * @param array|null $bindTypes
     * @return array
     * @throws \Exception
     */
    protected function parseBind(string $statement, array $placeholders = null, array $bindTypes = null): array
    {
        $pattern = '/\:(\w+)/';
        if (!preg_match_all($pattern, $statement, $matched)) {
            return [
                $statement,
                $placeholders,
            ];
        }
        if (sizeof($matched[1]) != sizeof($placeholders)) {
            throw new \Exception('绑定关系错误');
        }
        $bind = [];
        foreach ($matched[1] as $key) {
            $bind[] = $placeholders[$key];
        }
        return [
            preg_replace($pattern, '?', $statement),
            $bind,
        ];
    }

    /**
     * Author:Robert
     *
     * @param array|null $descriptor
     * @return bool
     */
    public function connect(array $descriptor = null): bool
    {
        if ($descriptor) {
            $this->_descriptor = $descriptor;
        }

        if (!isset($descriptor['host'])) {
            $descriptor['host'] = '127.0.0.1';
        }
        if (!isset($descriptor['port'])) {
            $descriptor['host'] = 3306;
        }
        /**
         * /兼容phalcon的判定
         */
        if (isset($descriptor['username'])) {
            $descriptor['user'] = $descriptor['username'];
            unset($descriptor['username']);
        }
        if (isset($descriptor['dbname'])) {
            $descriptor['database'] = $descriptor['dbname'];
            unset($descriptor['dbname']);
        }
        //        if (isset($descriptor['options']) && isset($descriptor['options'][\PDO::MYSQL_ATTR_INIT_COMMAND])) {
        //            $descriptor['charset'] = $descriptor['options'][\PDO::MYSQL_ATTR_INIT_COMMAND];
        //        }
        if (!isset($descriptor['strict_type'])) {
            $descriptor['strict_type'] = false;
        }
        if (!isset($descriptor['fetch_mode'])) {
            $descriptor['fetch_mode'] = true;
        }
        $this->_pdo = new CoroutineMySQL();
        return $this->_pdo->connect($descriptor);
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
     * @return bool
     */
    public function close(): bool
    {
        $this->_pdo->close();
        return true;
    }


    /**
     * Author:Robert
     *
     * @param $sqlQuery
     * @return string
     */
    public function forUpdate($sqlQuery): string
    {
        return $sqlQuery." FOR UPDATE";
    }

    /**
     * Author:Robert
     *
     * @param $sqlQuery
     * @return string
     */
    public function sharedLock($sqlQuery): string
    {
        return $sqlQuery." LOCK IN SHARE MODE";
    }




    /**
     * Author:Robert
     *
     * @param null $sequenceName
     * @return int
     */
    public function lastInsertId(): int
    {
        $pdo = $this->_pdo;
        if (!is_object($pdo)) {
            return 0;
        }
        return $this->_pdo->insert_id;
    }


    /**
     * Author:Robert
     *
     * @return int
     */
    public function affectedRows(): int
    {
        if (!is_object($this->_pdo)) {
            return 0;
        }
        return $this->_pdo->affected_rows;
    }



    /**
     * Author:Robert
     *
     * @param bool $nesting
     * @return bool
     * @throws \Exception
     */
    public function begin(bool $nesting = true): bool
    {

        $pdo = $this->_pdo;
        if (!is_object($pdo)) {
            return false;
        }
        /**
         * Increase the transaction nesting level
         */
        $this->_transactionLevel++;

        /**
         * Check the transaction nesting level
         */
        $transactionLevel = (int)$this->_transactionLevel;

        if ($transactionLevel == 1) {
            return $pdo->begin();
        } else {
            /**
             * Check if the current database system supports nested transactions
             */
            if ($transactionLevel && $nesting && $this->isNestedTransactionsWithSavepoints()) {
                $savepointName = $this->getNestedTransactionSavepointName();
                return $this->createSavepoint($savepointName);
            }
        }

        return false;
    }

    /**
     * Author:Robert
     *
     * @param bool $nesting
     * @return bool
     * @throws \Exception
     */
    public function commit(bool $nesting = true): bool
    {

        $pdo = $this->_pdo;
        if (!is_object($pdo)) {
            return false;
        }
        /**
         * Check the transaction nesting level
         */
        $transactionLevel = (int)$this->_transactionLevel;
        if (!$transactionLevel) {
            throw new \Exception("There is no active transaction");
        }
        if ($transactionLevel == 1) {

            /**
             * Reduce the transaction nesting level
             */
            $this->_transactionLevel--;

            return $pdo->commit();
        } else {

            /**
             * Check if the current database system supports nested transactions
             */
            if ($transactionLevel && $nesting && $this->isNestedTransactionsWithSavepoints()) {
                $savepointName = $this->getNestedTransactionSavepointName();
                /**
                 * Reduce the transaction nesting level
                 */
                $this->_transactionLevel--;
                return $this->releaseSavepoint($savepointName);
            }

        }

        /**
         * Reduce the transaction nesting level
         */
        if ($transactionLevel > 0) {
            $this->_transactionLevel--;
        }

        return false;
    }

    /**
     * Author:Robert
     *
     * @param bool $nesting
     * @return bool
     * @throws \Exception
     */
    public function rollback(bool $nesting = true): bool
    {
        $pdo = $this->_pdo;
        if (!is_object($pdo)) {
            return false;
        }
        /**
         * Check the transaction nesting level
         */
        $transactionLevel = (int)$this->_transactionLevel;
        if (!$transactionLevel) {
            throw new \Exception("There is no active transaction");
        }
        if ($transactionLevel == 1) {
            /**
             * Reduce the transaction nesting level
             */
            $this->_transactionLevel--;
            return $pdo->rollback();

        } else {
            /**
             * Check if the current database system supports nested transactions
             */
            if ($transactionLevel && $transactionLevel && $this->isNestedTransactionsWithSavepoints()) {
                $savepointName = $this->getNestedTransactionSavepointName();
                $this->_transactionLevel--;
                return $this->rollbackSavepoint($savepointName);
            }
        }

        /**
         * Reduce the transaction nesting level
         */
        if ($transactionLevel > 0) {
            $this->_transactionLevel--;
        }
        return false;
    }


    /**
     * Author:Robert
     *
     * @return bool
     */
    public function isNestedTransactionsWithSavepoints(): bool
    {
        return $this->_transactionsWithSavepoints;
    }


    /**
     * Author:Robert
     *
     * @return string
     */
    public function getNestedTransactionSavepointName(): string
    {
        return "PHALCON_SAVEPOINT_".$this->_transactionLevel;
    }


    /**
     * Author:Robert
     *
     * @param $name
     * @return string
     * @throws \Exception
     */
    public function releaseSavepoint($name): string
    {
        $sql = "RELEASE SAVEPOINT ".$name;
        return $this->execute($sql);
    }

    /**
     * Author:Robert
     *
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function rollbackSavepoint($name): bool
    {
        $sql = "ROLLBACK TO SAVEPOINT ".$name;
        return $this->execute($sql);
    }

    /**
     * Author:Robert
     *
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function createSavepoint($name): bool
    {
        $sql = "SAVEPOINT ".$name;
        return $this->execute($sql);
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    public function getDescriptor(): array
    {
        return $this->_descriptor;
    }
}

