<?php
/**
 * @package     XGalleryCli.Application
 * @subpackage  Flickr.Contacts
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Flickr;

defined('_XEXEC') or die;

use XGallery\Application;
use XGallery\Environment;

/**
 * @package     XGallery.Application
 * @subpackage  Flickr.Contacts
 *
 * @since       2.0.0
 */
class Contacts extends Application\Flickr
{
	/**
	 * @return boolean
	 *
	 * @since  2.1.0
	 * @throws \Exception
	 */
	protected function doExecute()
	{
		return $this->insertContactsFromFlickr();
	}

	/**
	 * @return boolean
	 *
	 * @since  2.1.0
	 * @throws \Exception
	 */
	protected function doAfterExecute()
	{
		parent::doAfterExecute();

		$args                = $this->input->getArray();
		$args['application'] = 'Flickr.Photos';

		Environment::execService($args);

		return true;
	}

	/**
	 * Get Flickr contacts and insert into database
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 * @throws  \Exception
	 */
	protected function insertContactsFromFlickr()
	{
		$this->log(__CLASS__ . '.' . __FUNCTION__);

		$lastExecutedTime = (int) $this->get(strtolower(get_class($this)) . '_executed');

		// No need update contact if cache is not expired
		if ($lastExecutedTime && time() - $lastExecutedTime < $this->get('limit_flickr_contacts_executed', 3600))
		{
			$this->log('Cache is not expired. No need update contacts', null, 'notice');

			return true;
		}

		// Get Flickr contacts
		$contacts          = $this->service->contacts->getContactsList();
		$totalContacts     = count($contacts);
		$lastTotalContacts = $this->get('flickr_contacts_count');

		$this->log('Contacts: ' . $totalContacts);

		// No new contact then no need execute database update
		if ($lastTotalContacts && $lastTotalContacts == $totalContacts)
		{
			$this->logger->notice('Have no new contacts');

			return true;
		}

		if (empty($contacts))
		{
			$this->log('Have no contacts', null, 'notice');

			return true;
		}

		if (!$this->getModel()->insertContacts($contacts))
		{
			return false;
		}

		// Update total contacts count
		$this->set('flickr_contacts_count', $totalContacts);

		return true;
	}
}
