<?php

namespace XGallery\Applications\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use XGallery\Exceptions\Exception;
use XGallery\Factory;
use XGallery\Webservices\Services\Flickr;

/**
 * Class CommandFlickr
 * @package XGallery\Applications\Commands
 */
class CommandFlickr extends Command
{
    use LockableTrait;

    protected $options = null;

    protected $arguments = null;

    /**
     * @var Flickr
     */
    protected $flickr;

    /**
     * @var string
     */
    protected $description;

    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        parent::configure();

        $reflect = new \ReflectionClass($this);
        $shortClassname = $reflect->getShortName();

        $this->setName('flickr:'.strtolower($shortClassname));
        $this->setDescription($this->description);

        if (!empty($this->options)) {
            foreach ($this->options as $key => $options) {
                $this->addOption(
                    $key,
                    isset($options['shortcut']) ? $options['shortcut'] : null,
                    isset($options['mode']) ? $options['mode'] : InputOption::VALUE_OPTIONAL,
                    isset($options['description']) ? $options['description'] : '',
                    isset($options['default']) ? $options['default'] : null
                );
            }
        }

        if (!empty($this->arguments)) {
            foreach ($this->arguments as $key => $arguments) {
                $this->addArgument(
                    $key,
                    isset($arguments['mode']) ? $arguments['mode'] : InputArgument::OPTIONAL,
                    isset($arguments['description']) ? $arguments['description'] : '',
                    isset($arguments['default']) ? $arguments['default'] : null
                );
            }
        }

        $this->flickr = Factory::getServices('Flickr');
    }

    /**
     * @param $table
     * @param $rows
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function insertRows($table, $rows)
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

        $connection->beginTransaction();
        $prepare = $connection->prepare($query);

        // Bind values
        foreach ($bindKeys as $index => $columns) {
            foreach ($columns as $columnId => $value) {
                $prepare->bindValue(':'.$columnId, $value);
            }
        }

        try {
            $prepare->execute();
            $connection->commit();
            $connection->close();

            return $prepare->rowCount();
        } catch (Exception $exception) {
            $connection->rollBack();
            $connection->close();

            return false;
        }
    }

    /**
     * @param $output
     */
    protected function complete($output)
    {
        $output->writeln("\nCompleted");
    }
}