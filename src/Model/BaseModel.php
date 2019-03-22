<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Monolog\Logger;
use XGallery\Factory;

class BaseModel
{

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * AbstractModel constructor.
     */
    public function __construct()
    {
        try {
            $this->connection = Factory::getConnection();
            $this->logger     = Factory::getLogger(get_called_class());
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }

    public function __destruct()
    {
        $this->cleanup();
    }

    protected function cleanup()
    {
        $this->connection->close();

        if (!empty($this->getErrors())) {
            $this->logger->error('', $this->errors);
        }
    }

    protected function reset()
    {
        $this->connection->close();
        $this->errors = [];
    }

    /**
     * return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $table
     * @param $rows
     * @return boolean|integer
     */
    public function insertRows($table, $rows)
    {
        $this->reset();
        $query = 'INSERT INTO `'.$table.'`';

        // Columns
        $query            .= '(';
        $onDuplicateQuery = [];
        $columnNames      = array_keys(get_object_vars($rows[0]));

        // Bind column names
        foreach ($columnNames as $columnName) {
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
                $columnId                    = 'value_'.uniqid();
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
