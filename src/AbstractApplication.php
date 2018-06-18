<?php
/**
 * @package     XGalleryCli
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Monolog\Logger;
use Psr\Log\LogLevel;
use XGallery\Environment\Filesystem\File;

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
	 * @var Registry|null
	 */
	protected $config = null;

	/**
	 * @var Logger|null
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
		$filePath     = XPATH_LOG . '/' . md5(get_class($this)) . '.json';

		if (File::exists($filePath))
		{
			$this->config->loadFile($filePath);
		}

		$this->logger = Factory::getLogger(get_class($this), Factory::getConfiguration()->get('logger_level', LogLevel::NOTICE));
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
		$buffer = $this->config->toString();
		File::write(XPATH_LOG . '/' . md5(get_class($this)) . '.json', $buffer);

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
	protected function log($message, $data = array(), $type = 'info')
	{
		if ($data)
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
		if (Factory::getConfiguration()->get('debug', false))
		{
			$start = (float) memory_get_peak_usage(true);
			$this->set('memory_start', $start);
			$this->set('execution_start', microtime(true));
		}

		// Primary execute
		if (!$this->doExecute())
		{
			return false;
		}

		if (Factory::getConfiguration()->get('debug', false))
		{
			$end = (float) memory_get_peak_usage(true);
			$this->set('memory_end', $end);
			$this->set('execution_end', microtime(true));

			$this->set(strtolower(get_class($this)) . '_executed', time());
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
		if (Factory::getConfiguration()->get('debug', false))
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
