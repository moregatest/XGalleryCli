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
use XGallery\Environment\Filesystem\File;
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
		$filePath     = XPATH_CONFIGURATIONS_DIR . '/' . md5(get_class($this)) . '.json';

		if (File::exists($filePath))
		{
			$this->config->loadFile($filePath);
		}

		$this->logger = Factory::getLogger(get_class($this));
	}

	/**
	 * @since   2.0.0
	 */
	public function __destruct()
	{
		$buffer = $this->config->toString();
		File::write(XPATH_CONFIGURATIONS_DIR . '/' . md5(get_class($this)) . '.json', $buffer);

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
		$this->set('memory_start', $start);
		$this->set('execution_start', microtime(true));

		// Primary execute
		if (!$this->doExecute())
		{
			return false;
		}

		$end = (float) memory_get_peak_usage(true);
		$this->set('memory_end', $end);
		$this->set('execution_end', microtime(true));

		$this->set(strtolower(get_class($this)) . '_executed', time());

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
		$memoryUsage = (float) $this->get('memory_end') - (float) $this->get('memory_start');
		$executeTime = (float) $this->get('execution_end') - (float) $this->get('execution_start');

		$this->logger->info('Task execute completed');
		$this->logger->debug('Memory usage: ' . $memoryUsage);
		$this->logger->debug('Executed time: ' . $executeTime);

		return true;
	}
}
