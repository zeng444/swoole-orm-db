<?php

namespace Janfish\Swoole\Coroutine\Db\Result;

/**
 * Author:Robert
 *
 * Class Pdo
 * @package Janfish\Swoole\Coroutine\Db\Result
 */
class Pdo
{

    protected $_connection;

    /**
     * @var
     */
    protected $_pdoStatement;

    /**
     * @var
     */
    protected $_sqlStatement;

    /**
     * @var
     */
    protected $_bindParams;

    /**
     * @var
     */
    protected $_bindTypes;

    /**
     * @var bool
     */
    protected $_rowCount = false;

    /**
     * Pdo constructor.
     * @param $connection
     * @param $result
     * @param $sqlStatement
     * @param $bindParams
     * @param $bindTypes
     */
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

    /**
     * Author:Robert
     *
     * @return mixed
     */
    public function fetch()
    {
        return $this->_pdoStatement->fetch();
    }

    /**
     * Author:Robert
     *
     * @return mixed
     */
    public function fetchArray()
    {
        return $this->fetch();
    }

    //    /**
    //     * Author:Robert
    //     *
    //     * @param int $fetchMode
    //     * @param null $colNoOrClassNameOrObject
    //     * @param null $ctorargs
    //     * @return bool
    //     */
    //    public function setFetchMode(int $fetchMode, $colNoOrClassNameOrObject = null, $ctorargs = null): bool
    //    {
    //        return true;
    //    }
    //
    //    /**
    //     * Author:Robert
    //     *
    //     * @return int
    //     */
    //    public function numRows(): int
    //    {
    //        return 1;
    //    }


}
