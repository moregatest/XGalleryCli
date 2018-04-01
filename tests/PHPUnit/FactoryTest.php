<?php
/**
 * @package     XGallery.PHPUnit
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use PHPUnit\Framework\TestCase;

/**
 * Class FactoryTest
 *
 * @since  2.0.0
 */
class FactoryTest extends TestCase
{
	/**
	 * @return  void
	 *
	 * @since   2.0.0
	 *
	 * @throws  Exception
	 */
	public function testGetLogger()
	{
		$this->assertInstanceOf(
			'Monolog\\Logger',
			\XGallery\Factory::getLogger(),
			'Can not get right logger class'
		);
	}

	/**
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function testGetInput()
	{
		$this->assertInstanceOf(
			'Joomla\\Input\\Input',
			\XGallery\Factory::getInput(),
			'Can not get right input class'
		);
	}

	/**
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function testGetApplication()
	{
		$directories = \XGallery\Environment\Filesystem\Directory::getDirectories(XPATH_ROOT . '/src/Application');

		foreach ($directories as $directory)
		{
			$files = \XGallery\Environment\Filesystem\Directory::getFiles(XPATH_ROOT . '/src/Application/' . $directory);

			foreach ($files as $file)
			{
				$fileName    = basename($file, '.php');
				$application = $directory . '\\' . basename($fileName, '.php');
				$this->assertInstanceOf(
					'XGallery\\Application\\' . $application,
					\XGallery\Factory::getApplication($application),
					'Can not get right application class: ' . $application
				);
			}
		}
	}

	/**
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function testGetApplicationNotFound()
	{
		$this->assertFalse(\XGallery\Factory::getApplication(uniqid()));
	}

	/**
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function testGetService()
	{
		$files = \XGallery\Environment\Filesystem\Directory::getFiles(XPATH_ROOT . '/src/Service');

		foreach ($files as $file)
		{
			$fileName = basename($file, '.php');
			$service  = basename($fileName, '.php');
			$this->assertInstanceOf(
				'XGallery\\Service\\' . $service,
				\XGallery\Factory::getService($service),
				'Can not get right service class: ' . $service
			);
		}
	}

	/**
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function testGetServiceNotFound()
	{
		$this->assertFalse(\XGallery\Factory::getService(uniqid()));
	}

	/**
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function testGetDbo()
	{
		$this->assertInstanceOf(
			'Joomla\\Database\\DatabaseDriver',
			\XGallery\Factory::getDbo(),
			'Can not get right Dbo class'
		);
	}
}
