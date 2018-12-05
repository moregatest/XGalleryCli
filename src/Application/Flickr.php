<?php
/**
 * @package     XGalleryCli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application;

defined('_XEXEC') or die;

use Joomla\Registry\Registry;
use XGallery\Environment;
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
	 * @var  boolean|\XGallery\Webservices\Services\Flickr
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

		$this->service = \XGallery\Webservices\Services\Flickr::getInstance();
	}

	/**
	 * @param   string $name Model name
	 *
	 * @return  Model\Flickr|mixed
	 */
	protected function getModel($name = 'Flickr')
	{
		return Model::getInstance($name);
	}

	/**
	 * @param   string $application Application name
	 * @param   array  $data        Extra data
	 *
	 * @return  void
	 * @throws \Exception
	 *
	 * @since   2.2.0
	 */
	protected function execService($application, $data = array())
	{
		$args                = $this->input->getArray();
		$args['application'] = 'Flickr.' . ucfirst($application);
		$args                = array_merge($args, $data);

		Environment::execService($args);
	}
}
