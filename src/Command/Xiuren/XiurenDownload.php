<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Xiuren;

use App\Service\Crawler\XiurenCrawler;
use App\Traits\HasStorage;
use SplFileInfo;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\CrawlerCommand;

/**
 * Class XiurenOrgDownload
 * @package App\Command\XiurenOrg
 */
final class XiurenDownload extends CrawlerCommand
{
    use HasStorage;

    /**
     * @var array
     */
    private $images;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Download images from xiuren.org')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption(
                            'url',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Detail URL page for download'
                        ),
                    ]
                )
            );

        parent::configure();
    }

    protected function prepareGetImageLinks()
    {
        $this->images = $this->getClient()->getDetail($this->getOption('url'));

        if (empty($this->images)) {
            return self::PREPARE_FAILED;
        }

        return self::PREPARE_SUCCEED;
    }

    protected function processDownloads()
    {
        /**
         * @var XiurenCrawler $client
         */
        $client     = $this->getClient();
        $fileSystem = new Filesystem;

        foreach ($this->images as $image) {
            $splInfo  = new SplFileInfo($image);
            $fileName = $splInfo->getBasename();

            $dirName = str_replace(
                ['https://www.xiuren.org/', 'http://www.xiuren.org/'],
                '',
                $this->getOption('url')
            );

            $dirSaveTo = $this->getStorage('xiuren.org') . DIRECTORY_SEPARATOR . $dirName;

            $fileSystem->mkdir($dirSaveTo);

            $saveTo = $dirSaveTo . '/' . $fileName;

            /**
             * @TODO Use wget
             */
            $this->log('Download ' . $image . ' to ' . $saveTo);
            $client->download($image, $saveTo);
        }

        return true;
    }
}
