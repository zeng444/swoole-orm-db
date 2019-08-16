<?php

namespace Janfish\Swoole\Coroutine\Db\Result;

class Pdo
{

    protected $_connection;
    protected $_pdoStatement;
    protected $_sqlStatement;
    protected $_bindParams;
    protected $_bindTypes;

    protected $_rowCount = false;

    public function __construct($connection, $result, $sqlStatement, $bindParams, $bindTypes)
    {

        $this->_connection = $connection;
        $this->_pdoStatement = $result;

        if ($sqlStatement !== null) {
            $this->_sqlStatement = $sqlStatement;
        }
        if ($bindParams !== null) {
            $this->_bindParams = $bindParams;
        }
        if ($bindTypes !== null) {
            $this->_bindTypes = $bindTypes;
        }
    }

    public function fetch($fetchStyle = null)
    {
        return $this->_pdoStatement->fetch();
    }

    public function fetchArray()
    {
        return $this->_pdoStatement->fetch();
    }

    public function setFetchMode(int $fetchMode, $colNoOrClassNameOrObject = null, $ctorargs = null): bool
    {
        return true;
    }

    /**
     * Author:Robert
     *
     * @return int
     */
    public function numRows(): int
    {
        return 1;
    }


}
