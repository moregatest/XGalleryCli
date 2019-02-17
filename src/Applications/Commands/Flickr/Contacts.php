<?php

namespace XGallery\Applications\Commands\Flickr;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XGallery\Applications\Commands\CommandFlickr;

/**
 * Class Contacts
 *
 * @package XGallery\Applications\Commands\Flickr
 */
class Contacts extends CommandFlickr
{

    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->description = 'Fetch contacts from Flickr';

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return boolean
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, 2);

        $output->writeln('Fetching contacts ...');

        if (!$contacts = $this->flickr->flickrContactsGetAll()) {
            $output->writeln('Can not get contacts');

            return true;
        }

        $progressBar->advance();
        $output->writeln("\nTotal contacts: ".count($contacts));

        if (empty($contacts)) {
            return false;
        }

        $output->writeln('Insert contacts ...');
        $rows = $this->insertRows('xgallery_flickr_contacts', $contacts);

        if ($rows === false) {
            return false;
        }

        $progressBar->finish();
        $output->writeln("\nAffected rows: ".(int)$rows);
        $this->complete($output);

        return true;
    }
}