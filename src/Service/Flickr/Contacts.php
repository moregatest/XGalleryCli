<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Service\Flickr;

use XGallery\Oauth\Service\Flickr;

defined('_XEXEC') or die;

/**
 * Class Contacts
 * @package   XGallery\Service\Flickr
 *
 * @since     2.1.0
 */
class Contacts extends Flickr
{
	/**
	 * @param   array $contacts Contacts
	 * @param   array $params   Params
	 *
	 * @return  array
	 * @since   2.1.0
	 *
	 * @throws  \Exception
	 */
	public function getContactsList(&$contacts = array(), $params = array())
	{
		$return = $this->getContacts($params);

		if ($return)
		{
			$contacts = array_merge($contacts, $return->contacts->contact);

			if ($return->contacts->pages > $return->contacts->page)
			{
				$this->getContactsList($contacts, array('page' => (int) $return->contacts->page + 1));
			}
		}

		return $contacts;
	}

	/**
	 * @param   array $params Parameters
	 *
	 * @return  boolean|object
	 * @since   2.1.0
	 *
	 * @throws  \Exception
	 */
	protected function getContacts($params = array())
	{
		return ($this->execute(
			array_merge(
				array('method' => 'flickr.contacts.getList', 'per_page' => XGALLERY_FLICKR_CONTACTS_GETLIST_PERPAGE),
				$params
			)
		));
	}
}
