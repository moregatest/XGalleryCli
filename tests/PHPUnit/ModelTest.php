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
 * Class ModelTest
 *
 * @since  2.0.0
 */
final class ModelTest extends TestCase
{
	/**
	 * @var    array
	 *
	 * @since  2.0.0
	 */
	protected $models = array('Flickr');

	/**
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function testGetValidModel()
	{
		foreach ($this->models as $model)
		{
			$this->assertInstanceOf(
				'XGallery\\Model\\' . $model,
				\XGallery\Model::getInstance($model),
				'Can not get right model: ' . $model
			);
		}
	}

	/**
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function testGetInvalidModel()
	{
		$this->assertFalse(\XGallery\Model::getInstance('Test'));
	}
}
