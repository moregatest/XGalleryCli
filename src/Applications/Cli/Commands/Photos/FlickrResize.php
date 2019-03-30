<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Photos;

use Doctrine\DBAL\DBALException;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use ReflectionException;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\Applications\Cli\Commands\AbstractCommandPhotos;
use XGallery\Utilities\SystemHelper;

/**
 * Class FlickrResize
 * @package XGallery\Applications\Cli\Commands\Photos
 */
class FlickrResize extends AbstractCommandPhotos
{

    /**
     * Photo object
     *
     * @var stdClass
     */
    private $photo;

    /**
     * Full path local file
     *
     * @var string
     */
    private $localFile;

    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->options = [
            'photo_id' => [
                'description' => 'Specific photo id',
                'default' => null,
            ],
            'width' => [
                'description' => 'Resize width',
                'default' => 1920,
            ],
            'height' => [
                'description' => 'Resize height',
                'default' => 1080,
            ],
            'position' => [
                'description' => '1: Top; 2: Center; 3: Bottom; 4: Left; 5: Right',
                'default' => ImageResize::CROPCENTER,
            ],
        ];

        parent::configure();
    }

    /**
     * Prepare photo before resize
     *
     * @return boolean|mixed
     * @throws DBALException
     */
    protected function preparePhoto()
    {
        static $retry = false;

        $photoId = $this->getOption('photo_id');

        if (!$photoId) {
            $this->log('No photo id provided', 'notice');

            return self::PREPARE_FAILED;
        }

        $this->log('Work on photo id: '.$photoId);

        if (!$retry) {
            $this->log('Try to download photo');
            $retry = true;

            /**
             * @TODO Support skip re-download
             */
            SystemHelper::getProcess([
                'php',
                XGALLERY_ROOT.'/cli.php',
                'flickr:photodownload',
                '--photo_id='.$photoId,
            ])->run();

            return $this->preparePhoto();
        }

        $this->photo = $this->model->getPhotoById($photoId);

        if (!$this->photo) {
            $this->log('Can not get photo from database', 'notice', $this->model->getErrors());

            return self::PREPARE_FAILED;
        }

        if ($this->photo->params === null && $retry === true) {
            $this->log('Photo have no params', 'notice');

            return self::PREPARE_FAILED;
        }

        $this->photo->params = json_decode($this->photo->params);

        return self::NEXT_PREPARE;
    }

    /**
     * Verify media file
     *
     * @return boolean
     */
    protected function prepareMediaFile()
    {
        $lastSize = end($this->photo->params);

        if (!$lastSize) {
            return false;
        }

        $this->log('Got sized', 'info', (array)$lastSize);

        $fileName        = basename($lastSize->source);
        $this->localFile = getenv('flickr_storage').'/'.$this->photo->owner.'/'.$fileName;

        return self::PREPARE_SUCCEED;
    }

    /**
     * processResize
     *
     * @return boolean
     * @throws ImageResizeException
     */
    protected function processResize()
    {
        if (!(new Filesystem())->exists($this->localFile)) {
            $this->log('Local file not found', 'notice');

            return false;
        }

        chmod($this->localFile, 644);
        $imageSize    = getimagesize($this->localFile);
        $resizeWidth  = $this->input->getOption('width');
        $resizeHeight = $this->input->getOption('height');

        $this->log('Local file: '.$this->localFile);
        $this->log('Dimension: '.implode(',', $imageSize));
        $this->log('Resize: '.$resizeWidth.'x'.$resizeHeight);

        if ($imageSize[0] < $resizeWidth && $imageSize[1] < $resizeHeight) {
            $this->log('Target image dimension is larger then source', 'notice');

            return false;
        }

        $dirSaveTo = getenv('photos_storage').'/'.$this->photo->owner.'/'.$resizeWidth.'x'.$resizeHeight;
        $saveTo    = $dirSaveTo.'/'.basename($this->localFile);
        (new Filesystem())->mkdir($dirSaveTo);

        $imager = new ImageResize($this->localFile);
        $imager
            ->crop($resizeWidth, $resizeHeight, false, $this->input->getOption('position'))
            ->save($saveTo, null, 100);
        $this->log('Resized: '.$saveTo.' with dimension: '.$imager->getDestWidth().' x '.$imager->getDestHeight());

        return true;
    }
}
