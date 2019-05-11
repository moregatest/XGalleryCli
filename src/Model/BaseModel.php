<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Exception;
use XGallery\Factory;
use XGallery\Traits\HasLogger;

/**
 * Class BaseModel
 * @package XGallery\Model
 */
class BaseModel
{
    use HasLogger;

    /**
     * Database connection
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Array of errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * BaseModel constructor.
     * @throws Exception
     */
    public function __construct()
    {
        try {
            $this->connection = Factory::getConnection();
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();
        }
    }

    /**
     * Get class instance
     *
     * @throws Exception
     */
    public static function getInstance()
    {
        static $instance;

        if (isset($instance)) {
            return $instance;
        }

        $instance = new static;

        return $instance;
    }

    /**
     * Clean up while destructing
     */
    public function __destruct()
    {
        $this->reset();
    }

    /**
     * Reset everything
     */
    protected function reset()
    {
        $this->errors = [];
        $this->connection->close();
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Insert record
     *
     * @param       $tableExpression
     * @param array $data
     * @param array $types
     * @return boolean|integer
     */
    protected function insert($tableExpression, array $data, array $types = [])
    {
        try {
            return $this->connection->insert($tableExpression, $data, $types);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }

    /**
     * getIdFrom
     *
     * @param string $table
     * @param array  $data
     * @return integer
     */
    protected function getIdFrom($table, $data)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('id')
            ->from($table);

        foreach ($data as $key => $value) {
            $queryBuilder->andWhere($key.' = :'.$key);
            $queryBuilder->setParameter(':'.$key, $value);
        }

        return (int)$queryBuilder->execute()->fetch(FetchMode::COLUMN);
    }
}
