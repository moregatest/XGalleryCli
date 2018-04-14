<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

use Joomla\Registry\Registry;
use XGallery\System\Configuration;

defined('_XEXEC') or die;

/**
 * Class Application
 * @package   XGallery.Application
 *
 * @since     2.0.0
 */
abstract class AbstractApplication
{
	/**
	 * @var \Joomla\Input\Input
	 */
	protected $input;

	/**
	 * @var Registry|null
	 */
	protected $config = null;

	/**
	 * @var \Monolog\Logger|null
	 */
	protected $logger = null;

	/**
	 * Application constructor.
	 *
	 * @param   Registry|null $config Configuration
	 *
	 * @throws  \Exception
	 */
	public function __construct(Registry $config = null)
	{
		$this->input  = Factory::getInput();
		$this->config = $config instanceof Registry ? $config : new Registry;
		$this->logger = Factory::getLogger(get_class($this));
	}

	/**
	 * @since   2.0.0
	 */
	public function __destruct()
	{
		$this->cleanup();
	}

	/**
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function cleanup()
	{
		$this->input  = null;
		$this->config = null;
	}

	/**
	 * @param   string $key   Key
	 * @param   mixed  $value Value
	 *
	 * @return  void
	 */
	public function set($key, $value = null)
	{
		$this->config->set($key, $value);
	}

	/**
	 * @param   string $key     Key
	 * @param   mixed  $default Value
	 *
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function execute()
	{
		$start = (float) memory_get_peak_usage(true);
		$this->set('memory.start', $start);
		$this->set('execution.start', microtime(true));

		// Primary execute
		if (!$this->doExecute())
		{
			return false;
		}

		$end = (float) memory_get_peak_usage(true);
		$this->set('memory.end', $end);
		$this->set('execution.end', microtime(true));

		Configuration::getInstance()->set(strtolower(get_class($this)) . '_executed', time());
		Configuration::getInstance()->save();

		return $this->doAfterExecute();
	}

	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.1.0
	 */
	abstract protected function doExecute();

	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.1.0
	 */
	protected function doAfterExecute()
	{
		$memoryUsage = (float) $this->config->get('memory.end') - (float) $this->config->get('memory.start');
		$executeTime = (float) $this->config->get('execution.end') - (float) $this->config->get('execution.start');

		$this->logger->info('Task execute completed');
		$this->logger->debug('Memory usage: ' . $memoryUsage);
		$this->logger->debug('Executed time: ' . $executeTime);

		return true;
	}
}
