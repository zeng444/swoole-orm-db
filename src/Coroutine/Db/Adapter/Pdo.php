<?php


namespace Janfish\Swoole\Coroutine\Db\Adapter;

use Janfish\Swoole\Coroutine\Db\Adapter;
use Swoole\Coroutine\MySQL as CoroutineMySQL;

/**
 * Author:Robert
 *
 * Class Pdo
 * @package Janfish\Swoole\Coroutine\Db\Adapter
 */
abstract class Pdo extends Adapter
{

    protected $_descriptor = [];

    protected $_pdo;

    protected $_statement;

    protected $_affectedRows;


    /**
     * Author:Robert
     *
     * @param $str
     * @param null $escapeChar
     * @return string
     */
    abstract function escape($str, $escapeChar = null): string;

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
        if ($this->_statement) {
            $this->_statement->execute($bindParams);
        }
        return $this->_statement;
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
        $affectedRows = 0;
        $this->_statement = $this->_pdo->prepare($sqlStatement);
        if ($this->_statement) {
            if (!$this->_statement->execute($bindParams)) {
                return false;
            }
            $affectedRows = $this->_pdo->affected_rows;
        }
        $this->_affectedRows = $affectedRows;
        return true;
    }

    /**
     * Author:Robert
     *
     * @return int
     */
    public function affectedRows(): int
    {
        return $this->_affectedRows;
    }

    /**
     * Author:Robert
     *
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Author:Robert
     *
     * @param null $sequenceName
     * @return int
     */
    public function lastInsertId($sequenceName = null): int
    {
        return $this->_pdo->insert_id;
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
    public function fetchAll(string $sqlQuery, $fetchMode = 2, $placeholders = null)
    {
        list($sqlStatement, $bindParams) = $this->parseBind($sqlQuery, $placeholders);
        $this->_statement = $this->_pdo->prepare($sqlStatement);
        if ($this->_statement) {
            $this->_statement->execute($bindParams);
        }
        return $this->_statement->fetchAll();
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
     * @param $identifier
     * @return string
     */
    public function escapeIdentifier($identifier): string
    {
        if (is_array($identifier)) {
            return $this->escape($identifier[0]).".".$this->escape($identifier[1]);
        }
        return $this->escape($identifier);
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
     * @param $tableName
     * @param null $schemaName
     * @return bool
     * @throws \Exception
     */
    public function tableExists($tableName, $schemaName = null): bool
    {
        if ($schemaName) {
            $sqlStatement = "SELECT IF(COUNT(*) > 0, 1, 0)  AS `count` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_NAME`= '".$tableName."' AND `TABLE_SCHEMA` = '".$schemaName."'";
        } else {
            $sqlStatement = "SELECT IF(COUNT(*) > 0, 1, 0) AS `count` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_NAME` = '".$tableName."' AND `TABLE_SCHEMA` = DATABASE()";
        }
        return $this->fetchOne($sqlStatement, \Pdo::FETCH_NUM)['count'] > 0;
    }

    /**
     * Author:Robert
     *
     * @param $viewName
     * @param null $schemaName
     * @return bool
     * @throws \Exception
     */
    public function viewExists($viewName, $schemaName = null): bool
    {
        if ($schemaName) {
            $sqlStatement = "SELECT IF(COUNT(*) > 0, 1, 0) AS `count` FROM `INFORMATION_SCHEMA`.`VIEWS` WHERE `TABLE_NAME`= '".$viewName."' AND `TABLE_SCHEMA`='".$schemaName."'";
        } else {
            $sqlStatement = "SELECT IF(COUNT(*) > 0, 1, 0) AS `count` FROM `INFORMATION_SCHEMA`.`VIEWS` WHERE `TABLE_NAME`='".$viewName."' AND `TABLE_SCHEMA` = DATABASE()";
        }
        return $this->fetchOne($sqlStatement)['count'] > 0;
    }


}
