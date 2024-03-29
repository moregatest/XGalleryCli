<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Flickr;

use App\Command\FlickrCommand;
use DateTime;
use Exception;

/**
 * Class FlickrMyContacts
 * @package App\Command\Flickr
 */
final class FlickrMyContacts extends FlickrCommand
{
    /**
     * Array of contact object
     *
     * @var object[]
     */
    private $contacts;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Fetchall contacts of current user');

        parent::configure();
    }

    /**
     * Fetch my contacts
     *
     * @return boolean
     */
    protected function prepareGetContacts()
    {
        // 2 days expire
        $this->contacts = $this->client->flickrContactsGetAll();

        if (!$this->contacts || empty($this->contacts)) {
            $this->log('Can not get contacts or empty', 'notice');

            return self::PREPARE_FAILED;
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * Insert all contacts
     *
     * @return boolean
     * @throws Exception
     */
    protected function processInsertContacts()
    {
        $this->io->newLine();
        $this->io->progressStart(count($this->contacts));

        $now = new DateTime;

        foreach ($this->contacts as $index => $contact) {
            /**
             * @TODO Try catch if can't execute query to find entity
             */
            $contactEntity = $this->getContact($contact->nsid);

            if ($contactEntity === null) {
                $contactEntity = new \App\Entity\FlickrContact;
                $contactEntity->setCreated($now);
                $contactEntity->setNsid($contact->nsid);
            }

            $contactEntity->setIconserver((int)$contact->iconserver);
            $contactEntity->setIconfarm((int)$contact->iconfarm);
            $contactEntity->setPathAlias($contact->path_alias);
            $contactEntity->setIgnored((bool)$contact->ignored);
            $contactEntity->setRevIgnored((bool)$contact->rev_ignored);
            $contactEntity->setFriend((bool)$contact->friend);
            $contactEntity->setFamily((bool)$contact->family);
            $contactEntity->setUsername($contact->username);
            $contactEntity->setRealname($contact->realname);
            $contactEntity->setLocation($contact->location ?? null);

            $this->batchInsert($contactEntity, $index);

            $this->io->progressAdvance();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
    }
}
