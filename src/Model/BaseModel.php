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
use Exception;
use Monolog\Logger;
use XGallery\Factory;

/**
 * Class BaseModel
 * @package XGallery\Model
 */
class BaseModel
{

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
     * Logger
     *
     * @var Logger
     */
    protected $logger;

    /**
     * BaseModel constructor.
     * @throws Exception
     */
    public function __construct()
    {
        try {
            $this->connection = Factory::getConnection();
            $this->logger     = Factory::getLogger(static::class);
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
        $this->cleanup();
    }

    /**
     * Clean up
     */
    protected function cleanup()
    {
        $this->connection->close();

        if (!empty($this->getErrors())) {
            $this->logger->error('', $this->errors);
        }
    }

    /**
     * Reset everything for next request
     */
    protected function reset()
    {
        $this->connection->close();
        $this->errors = [];
    }

    /**
     * Get errors
     *
     * return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Truncate table data
     *
     * @param string $table
     * @return \Doctrine\DBAL\Driver\ResultStatement
     * @throws DBALException
     */
    public function truncate($table)
    {
        return $this->connection->executeQuery('TRUNCATE `'.$table.'`');
    }

    /**
     * Insert multi rows
     *
     * @param string $table
     * @param array  $rows
     * @param array  $excludeFields
     * @return boolean|integer
     */
    public function insertRows($table, $rows, $excludeFields = [])
    {
        $this->reset();
        $query = 'INSERT INTO `'.$table.'`';

        // Columns
        $query            .= '(';
        $onDuplicateQuery = [];
        $columnNames      = array_keys(get_object_vars(reset($rows)));

        // Bind column names
        foreach ($columnNames as $index => $columnName) {
            if (in_array($columnName, $excludeFields)) {
                unset($columnNames[$index]);
                continue;
            }
            $query              .= '`'.$columnName.'`,';
            $onDuplicateQuery[] = '`'.$columnName.'`='.' VALUES(`'.$columnName.'`)';
            $onDuplicateQuery[] = '`'.$columnName.'`='.' VALUES(`'.$columnName.'`)';
        }

        $query = rtrim($query, ',').')';
        $query .= ' VALUES';

        $bindKeys = [];

        foreach ($rows as $index => $row) {
            $query .= ' (';
            foreach ($columnNames as $columnName) {
                $columnId                    = 'value_'.uniqid($columnName, false);
                $query                       .= ':'.$columnId.',';
                $bindKeys[$index][$columnId] = isset($row->{$columnName}) ? $row->{$columnName} : null;
            }

            $query = rtrim($query, ',').'),';
        }

        $query = rtrim($query, ',');
        $query .= ' ON DUPLICATE KEY UPDATE '.implode(',', $onDuplicateQuery).';';

        try {
            $prepare = $this->connection->prepare($query);
        } catch (DBALException $exception) {
            $this->connection->close();
            $this->errors[] = $exception->getMessage();

            return false;
        }

        // Bind values
        foreach ($bindKeys as $index => $columns) {
            foreach ($columns as $columnId => $value) {
                $prepare->bindValue(':'.$columnId, $value);
            }
        }

        if (!$prepare->execute()) {
            return false;
        }

        $this->connection->close();

        return $prepare->rowCount();
    }
}
