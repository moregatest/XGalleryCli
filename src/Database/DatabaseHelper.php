<?php

namespace XGallery\Database;

use XGallery\Exceptions\Exception;
use XGallery\Factory;

/**
 * Class DatabaseHelper
 * @package XGallery\Database
 */
class DatabaseHelper
{

    /**
     * @param $table
     * @param $rows
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function insertRows($table, $rows)
    {
        $connection = Factory::getDbo();
        $query = 'INSERT INTO `'.$table.'`';

        // Columns
        $query .= '(';
        $onDuplicateQuery = [];
        $columnNames = array_keys(get_object_vars($rows[0]));

        // Bind column names
        foreach ($columnNames as $columnName) {
            $query .= '`'.$columnName.'`,';
            $onDuplicateQuery[] = '`'.$columnName.'`='.' VALUES(`'.$columnName.'`)';
            $onDuplicateQuery[] = '`'.$columnName.'`='.' VALUES(`'.$columnName.'`)';
        }

        $query = rtrim($query, ',').')';
        $query .= ' VALUES';

        $bindKeys = [];

        foreach ($rows as $index => $row) {
            $query .= ' (';
            foreach ($columnNames as $columnName) {
                $columnId = 'value_'.uniqid();
                $query .= ':'.$columnId.',';
                $bindKeys[$index][$columnId] = isset($row->{$columnName}) ? $row->{$columnName} : null;
            }

            $query = rtrim($query, ',').'),';
        }

        $query = rtrim($query, ',');
        $query .= ' ON DUPLICATE KEY UPDATE '.implode(',', $onDuplicateQuery).';';

        $logger = Factory::getLogger(get_called_class());
        $logger->debug($query);

        try {
            $connection->beginTransaction();
            $prepare = $connection->prepare($query);

            // Bind values
            foreach ($bindKeys as $index => $columns) {
                foreach ($columns as $columnId => $value) {
                    $prepare->bindValue(':'.$columnId, $value);
                }
            }

            $prepare->execute();
            $connection->commit();
            $connection->close();

            return $prepare->rowCount();
        } catch (Exception $exception) {

            $connection->rollBack();
            $connection->close();
            $logger->error($exception->getMessage());

            return false;
        }
    }
}