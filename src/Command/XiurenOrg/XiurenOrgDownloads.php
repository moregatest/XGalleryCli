<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\XiurenOrg;


use XGallery\Command\XiurenOrgCommand;

class XiurenOrgDownloads extends XiurenOrgCommand
{
    private $itemLinks = [];

    protected function prepareGetItems()
    {
        $pages = $this->client->getPages('http://www.xiuren.org/');

        $this->io->newLine();
        $this->io->progressStart($pages);

        for ($index = 1; $index <= $pages; $index++) {
            $url             = 'http://www.xiuren.org/page-' . $index . '.html';
            $this->itemLinks = array_merge($this->itemLinks, $this->client->getItemLinks($url));

            $this->io->progressAdvance();
        }

        return self::PREPARE_SUCCEED;
    }

    protected function processDownloads()
    {
        $this->io->newLine();
        $this->io->progressStart(count($this->itemLinks));

        foreach ($this->itemLinks as $itemLink) {
            $this->getProcess(
                [
                    'xiurenorg:download',
                    '--url=' . $itemLink,
                ]
            )->run();

            $this->io->progressAdvance();
        }

        return true;
    }
}
