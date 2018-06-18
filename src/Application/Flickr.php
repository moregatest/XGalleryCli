<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application;

defined('_XEXEC') or die;

use Joomla\Registry\Registry;
use XGallery\Factory;
use XGallery\Model;

/**
 * Class Flickr
 * @package      XGallery\Application
 * @subpackage   Cli.Flickr
 *
 * @since        2.0.0
 */
class Flickr extends Cli
{
	/**
	 * @var  boolean|\XGallery\Service\Flickr
	 */
	protected $service;

	/**
	 * Flickr constructor.
	 *
	 * @param   Registry|null $config Configuration
	 *
	 * @throws  \Exception
	 * @since   2.1.0
	 */
	public function __construct(Registry $config = null)
	{
		parent::__construct($config);

		$this->service = Factory::getService('Flickr');
	}

	/**
	 * @param   string $name Model name
	 *
	 * @return Model\Flickr|mixed
	 */
	protected function getModel($name = 'Flickr')
	{
		return Model::getInstance($name);
	}
}
