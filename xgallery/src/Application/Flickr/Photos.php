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

use Joomla\CMS\Factory;
use XGallery\Application\Base;
use XGallery\Log\Helper;
use XGallery\Model\Flickr;

/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @since       2.0.0
 */
class Photos extends Base
{
	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 * @throws \Exception
	 */
	public function execute()
	{
		parent::execute();

		$input = Factory::getApplication()->input->cli;
		$db    = Factory::getDbo();
		$model = Flickr::getInstance();

		// Custom args
		$url  = $input->get('url', null, 'RAW');
		$nsid = $input->get('nsid', null);

		// Get nsid from URL
		if ($url)
		{
			$nsid = \XGallery\Flickr\Flickr::getInstance()->lookupUser($url);

			if ($nsid && $nsid->stat == "ok")
			{
				$nsid = $nsid->user->id;
			}
		}

		// Transaction: Get a contact then fetch all photos of this contact
		try
		{
			$db->transactionStart();

			if ($nsid === null)
			{
				$nsid = $model->getContact();
			}

			$model->updateContact($nsid);

			$db->transactionCommit();
		}
		catch (\Exception $exception)
		{
			Helper::getLogger()->error($exception->getMessage(), array('query' => (string) $db->getQuery()));
			$db->transactionRollback();
		}

		$db->disconnect();

		$model->insertPhotosFromFlickr($nsid);

		return true;
	}
}
