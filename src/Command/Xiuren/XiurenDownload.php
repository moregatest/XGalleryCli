<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Xiuren;

use App\Command\CrawlerCommand;
use App\Service\Crawler\XiurenCrawler;
use App\Traits\HasStorage;
use App\Utils\Filesystem;
use SplFileInfo;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

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

    /**
     * @return boolean
     */
    protected function prepareGetImageLinks()
    {
        $this->images = $this->getClient()->getDetail($this->getOption('url'));

        if (empty($this->images)) {
            return self::PREPARE_FAILED;
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     */
    protected function processDownloads()
    {
        /**
         * @var XiurenCrawler $client
         */
        $client = $this->getClient();

        foreach ($this->images as $image) {
            $splInfo  = new SplFileInfo($image);
            $fileName = $splInfo->getBasename();

            $dirName = str_replace(
                ['https://www.xiuren.org/', 'http://www.xiuren.org/'],
                '',
                $this->getOption('url')
            );

            $dirSaveTo = $this->getStorage('xiuren.org') . DIRECTORY_SEPARATOR . $dirName;

            Filesystem::mkdir($dirSaveTo);

            $saveTo = $dirSaveTo . '/' . $fileName;

            if (Filesystem::exists($saveTo)) {
                continue;
            }

            /**
             * @TODO Use wget
             */
            $this->log('Download ' . $image . ' to ' . $saveTo);
            $client->download($image, $saveTo);
        }

        return true;
    }
}
