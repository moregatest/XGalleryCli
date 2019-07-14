<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Flickr;

use DateTime;
use Exception;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\FlickrCommand;

/**
 * Class FlickrContact
 * @package App\Command\Flickr
 */
final class FlickrContact extends FlickrCommand
{
    /**
     * @var object
     */
    private $contact;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Manual insert a contact')
            ->setDefinition(
                new InputDefinition(
                    [new InputOption('nsid', 'id', InputOption::VALUE_OPTIONAL, 'Specific NSID for process')]
                )
            );

        parent::configure();
    }

    /**
     * Fetch contact information
     *
     * @return boolean
     */
    protected function prepareContact()
    {
        if (!$nsid = $this->getOption('nsid')) {
            $nsid = $this->io->ask('Enter NSID');
        }

        if (!$nsid) {
            return self::PREPARE_FAILED;
        }

        $this->contact = $this->client->flickrPeopleGetInfo($this->client->getNsid($nsid));

        if (!$this->contact) {
            $this->log('Can not get contact or contact not found', 'notice');

            return self::PREPARE_FAILED;
        }

        /**
         * @TODO : Console output show one blank line before Prepare succeed if NSID manual input
         */
        return self::PREPARE_SUCCEED;
    }

    /**
     * Process insert contact into db
     *
     * @return boolean
     * @throws Exception
     */
    protected function processInsertContact()
    {
        $this->log('Process with NSID: ' . $this->contact->person->nsid);

        $contactEntity = $this->entityManager
            ->getRepository(\App\Entity\FlickrContact::class)
            ->find($this->contact->person->nsid);
        $now           = new DateTime();

        // Contact not found
        if ($contactEntity === null) {
            $contactEntity = new \App\Entity\FlickrContact;
            $contactEntity->setCreated($now);
            $contactEntity->setNsid($this->contact->person->nsid);
        }

        $contactEntity->setIconserver($this->contact->person->iconserver);
        $contactEntity->setIconfarm($this->contact->person->iconfarm);
        $contactEntity->setPathAlias($this->contact->person->path_alias);
        $contactEntity->setIgnored($this->contact->person->ignored);
        $contactEntity->setFriend($this->contact->person->friend);
        $contactEntity->setFamily($this->contact->person->family);
        $contactEntity->setUsername($this->contact->person->username->_content);
        $contactEntity->setRealname($this->contact->person->realname->_content ?? null);
        $contactEntity->setLocation($this->contact->person->location->_content ?? null);
        $contactEntity->setDescription($this->contact->person->description->_content ?? null);
        $contactEntity->setPhotos($this->contact->person->photos->count->_content);
        $contactEntity->setUpdated(new DateTime);

        $this->entityManager->persist($contactEntity);
        $this->entityManager->flush();

        return true;
    }
}
