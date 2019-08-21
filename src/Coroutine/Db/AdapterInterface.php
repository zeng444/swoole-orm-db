<?php

namespace Janfish\Swoole\Coroutine\Db;

/**
 * Author:Robert
 *
 * Interface AdapterInterface
 * @package Janfish\Swoole\Coroutine\Db
 */
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
     * Sends SQL statements to the database server returning the success state.
     * Use this method only when the SQL statement sent to the server doesn't return any rows
     */
    public function execute($sqlStatement, $placeholders = null, $dataTypes = null): bool;


}