<?php

namespace Janfish\Swoole\Coroutine\Db;

use Swoole\Coroutine\MySQL as CoroutineMySQL;
use Janfish\Swoole\Coroutine\Db\Result\Pdo as ResultPdo;

/**
 * Author:Robert
 *
 * Class Adapter
 * @package Janfish\Swoole\Coroutine\Db
 */
class Adapter implements AdapterInterface
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
     * @var
     */
    protected $_pdo;

    /**
     * Author:Robert
     *
     * @var
     */
    protected $_statement;


    /**
     * Author:Robert
     *
     * @var bool
     */
    protected $_isDefer = false;

    /**
     * @var int
     */
    protected $max_reconnection = 2;

    /**
     * @var int
     */
    private $_reconnection = 0;


    /**
     * MySQL server has gone away
     * https://dev.mysql.com/doc/refman/5.7/en/client-error-reference.html#error_cr_server_gone_error
     */
    const  CR_SERVER_GONE_ERROR = 2006;

    /**
     *  Lost connection to MySQL server during query
     * https://dev.mysql.com/doc/refman/5.7/en/client-error-reference.html#error_cr_server_lost
     */
    const  CR_SERVER_LOST = 2013;


    /**
     * Author:Robert
     *
     * @param $errorNo
     * @return bool
     */
    public function isConnectionError($errorNo): bool
    {
        return in_array($errorNo, [self::CR_SERVER_GONE_ERROR, self::CR_SERVER_LOST]);
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
        if (isset($descriptor['max_reconnection '])) {
            $this->max_reconnection = $descriptor['fetch_mode'];
        }
        $this->_pdo = new CoroutineMySQL();
        return $this->_pdo->connect($descriptor);
    }


    /**
     * Author:Robert
     *
     * @return bool
     */
    public function reconnect(): bool
    {
        if ($this->_reconnection >= $this->max_reconnection) {
            throw new \PDOException('Retry connection failed');
        }
        if (!$this->connect($this->_descriptor)) {
            $this->_reconnection++;
        } else {
            $this->_reconnection = 0;
        }
        return true;
    }

    /**
     * Author:Robert
     *
     * @param $sqlStatement
     * @param null $bindParams
     * @param null $bindTypes
     * @return mixed
     * @throws \Exception
     */
    public function query($sqlStatement, $bindParams = null, $bindTypes = null)
    {
        list($sqlStatement, $bindParams) = $this->parseBind($sqlStatement, $bindParams, $bindTypes);
        $this->_statement = $this->_pdo->prepare($sqlStatement);
        if (!$this->_statement) {
            if ($this->isConnectionError($this->_pdo->errno)) {
                $this->reconnect();
                return $this->execute($sqlStatement, $bindParams, $bindTypes);
            } else {
                throw new \Exception($this->_pdo->error, $this->_pdo->errno);
            }
        }
        if ($this->_isDefer) {
            $this->_pdo->setDefer();
        }
        $result = $this->_statement->execute($bindParams);
        if (!$result) {
            if ($this->isConnectionError($this->_pdo->errno)) {
                $this->reconnect();
                return $this->query($sqlStatement, $bindParams, $bindTypes);
            } else {
                throw new \Exception($this->_pdo->error, $this->_pdo->errno);
            }
        }
        return new ResultPdo($this, $this->_statement, $sqlStatement, $bindParams, $bindTypes);
    }


    /**
     * Author:Robert
     *
     * @param $sqlStatement
     * @param null $bindParams
     * @param null $bindTypes
     * @return bool
     * @throws \Exception
     */
    public function execute($sqlStatement, $bindParams = null, $bindTypes = null): bool
    {
        list($sqlStatement, $bindParams) = $this->parseBind($sqlStatement, $bindParams, $bindTypes);
        $this->_statement = $this->_pdo->prepare($sqlStatement);
        if (!$this->_statement) {
            if ($this->isConnectionError($this->_pdo->errno) && $this->isUnderTransaction() === false) {
                $this->reconnect();
                return $this->execute($sqlStatement, $bindParams, $bindTypes);
            } else {
                throw new \Exception($this->_pdo->error, $this->_pdo->errno);
            }
        }
        if ($this->_isDefer) {
            $this->_pdo->setDefer();
        }
        if (!$this->_statement->execute($bindParams)) {
            return false;
        }
        return true;
    }

    /**
     * Author:Robert
     *
     */
    public function setDefer()
    {
        $this->_isDefer = true;
    }


    /**
     * Author:Robert
     *
     */
    public function recv()
    {
        $this->_isDefer = false;
        $result = $this->_pdo->recv();
        if (!$result) {
            return false;
        }
        return true;

    }

    /**
     * Author:Robert
     *
     * @param array $array
     * @param array $append
     * @return array
     */
    private function mergeAppend(array $array, array $append): array
    {
        foreach ($append as $val) {
            $array[] = $val;
        }
        return $array;
    }


    /**
     * Author:Robert
     *
     * @param string $table
     * @param $values
     * @param null $fields
     * @param null $dataTypes
     * @return bool
     * @throws \Exception
     */
    public function insert(string $table, $values, $fields = null, $dataTypes = null): bool
    {
        /**
         * A valid array with more than one element is required
         */
        if (!sizeof($values)) {
            throw new \Exception("Unable to insert into ".$table." without data");
        }

        $placeholders = [];
        $insertValues = [];
        $bindDataTypes = [];

        /**
         * Objects are casted using __toString, null values are converted to string "null", everything else is passed as "?"
         */
        foreach ($values as $position => $value) {
            if (is_object($value)) {
                $placeholders[] = (string)$value;
            } else {
                if ($value === null) {
                    $placeholders[] = "null";
                } else {
                    $placeholders[] = "?";
                    $insertValues[] = $value;
                    if (is_array($dataTypes)) {
                        if (!isset($dataTypes[$position])) {
                            throw new \Exception("Incomplete number of bind types");
                        }
                        $bindDataTypes[] = $dataTypes[$position];
                    }
                }
            }
        }

        $escapedTable = $this->escapeIdentifier($table);

        /**
         * Build the final SQL INSERT statement
         */
        $joinedValues = join(", ", $placeholders);
        if (is_array($fields)) {
            $escapedFields = [];
            foreach ($fields as $field) {
                $escapedFields[] = $this->escapeIdentifier($field);
            }
            $insertSql = "INSERT INTO ".$escapedTable." (".join(", ", $escapedFields).") VALUES (".$joinedValues.")";
        } else {
            $insertSql = "INSERT INTO ".$escapedTable." VALUES (".$joinedValues.")";
        }

        /**
         * Perform the execution via PDO::execute
         */
        if (!sizeof($bindDataTypes)) {
            return $this->{"execute"}($insertSql, $insertValues);
        }
        return $this->{"execute"}($insertSql, $insertValues, $bindDataTypes);
    }


    /**
     * Author:Robert
     *
     * @param string $sqlQuery
     * @param int $fetchMode
     * @param null $bindParams
     * @param null $bindTypes
     * @return array
     * @throws \Exception
     */
    public function fetchAll(string $sqlQuery, $fetchMode = 2, $bindParams = null, $bindTypes = null): array
    {
        $results = [];
        $result = $this->query($sqlQuery, $bindParams, $bindTypes);
        if (is_object($result)) {
            if ($fetchMode !== null) {
                $result->setFetchMode($fetchMode);
            }
            while ($row = $result->fetch()) {
                $results[] = $row;
            }
        }
        return $results;
    }

    /**
     * Author:Robert
     *
     * @param string $sqlQuery
     * @param int $fetchMode
     * @param null $placeholders
     * @return array
     * @throws \Exception
     */
    public function fetchOne(string $sqlQuery, $fetchMode = 2, $placeholders = null)
    {
        $this->_statement = $this->query($sqlQuery, $placeholders);
        return $this->_statement->fetch();
    }


    /**
     * Author:Robert
     *
     * @param string $table
     * @param $data
     * @param null $dataTypes
     * @return bool
     * @throws \Exception
     */
    public function insertAsDict(string $table, $data, $dataTypes = null): bool
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $values = [];
        $fields = [];
        foreach ($data as $field => $value) {
            $fields[] = $field;
            $values[] = $value;
        }
        return $this->insert($table, $values, $fields, $dataTypes);
    }

    /**
     * Author:Robert
     *
     * @param $table
     * @param $fields
     * @param $values
     * @param null $whereCondition
     * @param null $dataTypes
     * @return bool
     * @throws \Exception
     */
    public function update($table, $fields, $values, $whereCondition = null, $dataTypes = null): bool
    {
        $placeholders = [];
        $updateValues = [];
        $bindDataTypes = [];
        /**
         * Objects are casted using __toString, null values are converted to string 'null', everything else is passed as '?'
         */
        foreach ($values as $position => $value) {
            if (!isset($fields[$position])) {
                throw new \Exception("The number of values in the update is not the same as fields");
            }
            $field = $fields[$position];
            $escapedField = $this->escapeIdentifier($field);
            if ($value === null) {
                $placeholders[] = $escapedField." = null";
            } else {
                $updateValues[] = $value;
                if (is_array($dataTypes)) {
                    if (!isset($dataTypes[$position])) {
                        throw new \Exception("Incomplete number of bind types");
                    }
                    $bindType = $dataTypes[$position];
                    $bindDataTypes[] = $bindType;
                }
                $placeholders[] = $escapedField." = ?";
            }
        }
        $escapedTable = $this->escapeIdentifier($table);
        $setClause = join(", ", $placeholders);
        if ($whereCondition !== null) {
            $updateSql = "UPDATE ".$escapedTable." SET ".$setClause." WHERE ";

            /**
             * String conditions are simply appended to the SQL
             */
            if (is_string($whereCondition)) {
                $updateSql .= $whereCondition;
            } else {

                /**
                 * Array conditions may have bound params and bound types
                 */
                if (!is_array($whereCondition)) {
                    throw new \Exception("Invalid WHERE clause conditions");
                }

                /**
                 * If an index 'conditions' is present it contains string where conditions that are appended to the UPDATE sql
                 */
                if (isset($whereCondition["conditions"])) {
                    $updateSql .= $whereCondition["conditions"];
                }

                /**
                 * Bound parameters are arbitrary values that are passed by separate
                 */

                if (isset($whereCondition["bind"])) {
                    $updateValues = $this->mergeAppend($updateValues, $whereCondition["bind"]);
                }

                /**
                 * Bind types is how the bound parameters must be casted before be sent to the database system
                 */
                if (isset($whereCondition["bindTypes"])) {
                    $bindDataTypes = $this->mergeAppend($bindDataTypes, $whereCondition["bindTypes"]);
                }
            }
        } else {
            $updateSql = "UPDATE ".$escapedTable." SET ".$setClause;
        }
        /**
         * Perform the update via PDO::execute
         */
        if (!sizeof($bindDataTypes)) {
            return $this->{"execute"}($updateSql, $updateValues);
        }
        return $this->{"execute"}($updateSql, $updateValues, $bindDataTypes);
    }


    /**
     * Author:Robert
     *
     * @param $table
     * @param $data
     * @param null $whereCondition
     * @param null $dataTypes
     * @return bool
     * @throws \Exception
     */
    public function updateAsDict($table, $data, $whereCondition = null, $dataTypes = null): bool
    {
        $values = [];
        $fields = [];
        if (!is_array($data) || empty($data)) {
            return false;
        }
        foreach ($data as $field => $value) {
            $fields[] = $field;
            $values[] = $value;
        }
        return $this->update($table, $fields, $values, $whereCondition, $dataTypes);

    }

    /**
     * Author:Robert
     *
     * @param $table
     * @param null $whereCondition
     * @param null $placeholders
     * @param null $dataTypes
     * @return bool
     */
    public function delete($table, $whereCondition = null, $placeholders = null, $dataTypes = null): bool
    {
        $escapedTable = $this->escapeIdentifier($table);
        if (!empty($whereCondition)) {
            $sql = "DELETE FROM ".$escapedTable." WHERE ".$whereCondition;
        } else {
            $sql = "DELETE FROM ".$escapedTable;
        }
        /**
         * Perform the update via PDO::execute
         */
        return $this->{"execute"}($sql, $placeholders, $dataTypes);
    }


}
