<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Nct
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application;

use GuzzleHttp\Client;
use Joomla\Registry\Registry;
use XGallery\Environment;


/**
 * Class Nct
 * @package XGallery\Application
 *
 * @since   2.1.0
 */
class Nct extends Cli
{
	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @var \XGallery\Service\Nct
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

		$this->service = new \XGallery\Service\Nct;
	}

	/**
	 * @return boolean
	 *
	 * @since  2.1.0
	 * @throws \Exception
	 */
	protected function doAfterExecute()
	{
		$args                = $this->input->getArray();
		$args['application'] = 'Nct.Download';

		Environment::execService($args);

		return parent::doAfterExecute();
	}
}
