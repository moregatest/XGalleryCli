<?php
/**
 * @package     XGalleryCli
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application;

use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Monolog\Logger;
use Psr\Log\LogLevel;
use XGallery\Environment\Filesystem\File;
use XGallery\Factory;

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
	 * @var Input
	 */
	protected $input;

	/**
	 * @var Registry
	 */
	protected $config = null;

	protected $globalConfig;

	/**
	 * @var Logger
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
		$this->globalConfig = Factory::getConfiguration();
		$this->input        = Factory::getInput();

		// Application config file
		$this->config       = $config instanceof Registry ? $config : new Registry;
		$applicationLogFile = $this->globalConfig->get('log_path') . '/' . md5(get_class($this)) . '.json';

		if ($applicationLogFile)
		{
			$this->config->loadFile($applicationLogFile);
		}

		$this->config->set('application_config_file', $applicationLogFile);
		$this->logger = Factory::getLogger(get_class($this), $this->globalConfig->get('logger_level', LogLevel::NOTICE));
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 *
	 * @since  2.1.0
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * @since  2.0.0
	 */
	public function __destruct()
	{
		$this->cleanup();
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 *
	 * @since  2.1.0
	 */
	public function toString()
	{
		$reflect = new \ReflectionClass($this);

		return $reflect->getShortName();
	}

	/**
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function cleanup()
	{
		$buffer = $this->config->toString();
		File::write($this->config->get('application_config_file'), $buffer);

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
	 * @return  mixed
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * @param   string $message Log message
	 * @param   array  $data    Extend data
	 * @param   string $type    Log type
	 *
	 * @return  mixed
	 */
	protected function log($message, $data = null, $type = 'info')
	{
		if (!empty($data))
		{
			return call_user_func_array(array($this->logger, $type), array($message, $data));
		}

		return call_user_func_array(array($this->logger, $type), array($message));
	}

	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function execute()
	{
		$config = Factory::getConfiguration();

		if (Factory::isDebug())
		{
			$this->set('memory_start', (float) memory_get_peak_usage(true));
			$this->set('execution_start', microtime(true));
		}

		// Primary execute
		if (!$this->doExecute())
		{
			return false;
		}

		if (Factory::isDebug())
		{
			$this->set('memory_end', (float) memory_get_peak_usage(true));
			$this->set('execution_end', microtime(true));
			$this->set(strtolower(get_class($this)) . '_executed', time());
		}

		if (!$config->get('execute_chain', true))
		{
			return true;
		}

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
		if (Factory::isDebug())
		{
			$memoryUsage = (float) $this->get('memory_end') - (float) $this->get('memory_start');
			$executeTime = (float) $this->get('execution_end') - (float) $this->get('execution_start');

			$this->logger->info('Task execute completed');
			$this->logger->debug('Memory usage: ' . $memoryUsage);
			$this->logger->debug('Executed time: ' . $executeTime);
		}

		return true;
	}
}
