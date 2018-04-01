<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Flickr;

defined('_XEXEC') or die;

use XGallery\Application;
use XGallery\Environment\Helper;
use XGallery\Factory;
use XGallery\System\Configuration;

/**
 * @package     XGallery.Application
 * @subpackage  Flickr.Contacts
 *
 * @since       2.0.0
 */
class Contacts extends Application\Flickr
{
	/**
	 * Entry point
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	public function execute()
	{
		if (!$this->insertContactsFromFlickr())
		{
			return false;
		}

		Configuration::getInstance()->set('flickr_contacts_last_executed', time());
		Configuration::getInstance()->save();

		$args                = $this->input->getArray();
		$args['application'] = 'Flickr.Photos';

		Helper::execService($args);

		return true;
	}

	/**
	 * Get Flickr contacts and insert into database
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	protected function insertContactsFromFlickr()
	{
		Factory::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

		$config = Configuration::getInstance();

		$lastExecutedTime = (int) $config->get('flickr_contacts_last_executed');

		// No need update contact if cache is not expired
		if ($lastExecutedTime && time() - $lastExecutedTime < 3600)
		{
			Factory::getLogger()->notice('Cache is not expired. No need update contacts');

			return true;
		}

		// Get Flickr contacts
		$contacts          = Factory::getService('Flickr')->getContactsList();
		$totalContacts     = count($contacts);
		$lastTotalContacts = $config->get('flickr_contacts_count');

		Factory::getLogger()->info('Contacts: ' . $totalContacts);

		// No new contact then no need execute database update
		if ($lastTotalContacts && $lastTotalContacts == $totalContacts)
		{
			Factory::getLogger()->notice('Have no new contacts');

			return true;
		}

		if (empty($contacts))
		{
			Factory::getLogger()->notice('Have no contacts');

			return true;
		}

		if (!$this->getModel()->insertContacts($contacts))
		{
			return false;
		}

		// Update total contacts count
		$config->set('flickr_contacts_count', $totalContacts);

		return $config->save();
	}
}
