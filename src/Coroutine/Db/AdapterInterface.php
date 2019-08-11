<?php

namespace Janfish\Swoole\Coroutine\Db;

interface AdapterInterface
{

    /**
     * Returns the first row in a SQL query result
     *
     * @param string sqlQuery
     * @param int fetchMode
     * @param int placeholders
     * @return array
     */
    public function fetchOne(string $sqlQuery, $fetchMode = 2, $placeholders = null);

    /**
     * Dumps the complete result of a query into an array
     *
     * @param string sqlQuery
     * @param int fetchMode
     * @param int placeholders
     * @return array
     */
    public function fetchAll(string $sqlQuery, $fetchMode = 2, $placeholders = null);

    /**
     * Inserts data into a table using custom RDBMS SQL syntax
     *
     * @param    string table
     * @param    array values
     * @param    array fields
     * @param    array dataTypes
     * @return    boolean
     */
    public function insert(string $table, $values, $fields = null, $dataTypes = null);

    /**
     * Updates data on a table using custom RDBMS SQL syntax
     *
     * @param    string table
     * @param    array fields
     * @param    array values
     * @param    string whereCondition
     * @param    array dataTypes
     * @return    boolean
     */
    public function update($table, $fields, $values, $whereCondition = null, $ataTypes = null);

    /**
     * Deletes data from a table using custom RDBMS SQL syntax
     *
     * @param  string table
     * @param  string whereCondition
     * @param  array placeholders
     * @param  array dataTypes
     * @return boolean
     */
    public function delete($table, $whereCondition = null, $placeholders = null, $dataTypes = null);


    /**
     * Appends a LIMIT clause to sqlQuery argument
     *
     * @param    string sqlQuery
     * @param    int number
     * @return    string
     */
    public function limit($sqlQuery, $number);


    /**
     * Returns a SQL modified with a FOR UPDATE clause
     */
    public function forUpdate($sqlQuery): string;

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     */
    public function sharedLock($sqlQuery): string;


    /**
     * Return descriptor used to connect to the active database
     *
     * @return array
     */
    public function getDescriptor();


    /**
     * This method is automatically called in \Phalcon\Db\Adapter\Pdo constructor.
     * Call it when you need to restore a database connection
     */
    public function connect(array $descriptor = null): bool;


    /**
     * Sends SQL statements to the database server returning the success state.
     * Use this method only when the SQL statement sent to the server doesn't return any rows
     */
    public function execute($sqlStatement, $placeholders = null, $dataTypes = null): bool;

    /**
     * Returns the number of affected rows by the last INSERT/UPDATE/DELETE reported by the database system
     */
    public function affectedRows(): int;

    /**
     * Closes active connection returning success. Phalcon automatically closes
     * and destroys active connections within Phalcon\Db\Pool
     */
    public function close(): bool;


    /**
     * Escapes a value to avoid SQL injections
     */
    public function escapeString(string $str): string;

    /**
     * Returns insert id for the auto_increment column inserted in the last SQL statement
     *
     * @param string sequenceName
     * @return int
     */
    public function lastInsertId($sequenceName = null);

    /**
     * Starts a transaction in the connection
     */
    public function begin(bool $nesting = true): bool;

    /**
     * Rollbacks the active transaction in the connection
     */
    public function rollback(bool $nesting = true): bool;

    /**
     * Commits the active transaction in the connection
     */
    public function commit(bool $nesting = true): bool;

    /**
     * Checks whether connection is under database transaction
     */
    public function isUnderTransaction(): bool;

    /**
     * Return internal PDO handler
     */
    public function getInternalHandler();


}