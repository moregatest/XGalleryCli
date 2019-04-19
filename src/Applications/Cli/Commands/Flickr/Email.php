<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Factory;
use XGallery\Utilities\SystemHelper;

/**
 * Class Email
 * @package XGallery\Applications\Cli\Commands\Flickr
 */
final class Email extends AbstractCommandFlickr
{
    /**
     * @var
     */
    private $connection;

    /**
     * @var array
     */
    private $emailList;

    /**
     * prepareGetEmails
     * @return boolean
     */
    protected function prepareGetEmails()
    {
        $this->connection = Factory::getImapMailer();
        $this->emailList  = imap_search($this->connection, 'SUBJECT "XGalleryCli request"');

        return self::PREPARE_SUCCEED;
    }

    /**
     * processEmails
     * @return boolean
     */
    protected function processEmails()
    {
        if (!$this->emailList) {
            return false;
        }

        $re = '/(?:\[xgallery|(?!^)\G)\K(?:\s+(\w+)=["\'](.*?)["\']|\s*\](.*?)\[\/xgallery\])/m';

        foreach ($this->emailList as $emailNo) {
            $header      = imap_headerinfo($this->connection, $emailNo);
            $fromAddress = $header->from[0]->mailbox."@".$header->from[0]->host;
            $message     = strip_tags(imap_fetchbody($this->connection, $emailNo, '1'));
            $message     = trim(preg_replace('/\s\s+/', '', $message));

            $processCmd [] = 'php';
            $processCmd [] = XGALLERY_ROOT.'/cli.php';
            $processCmd [] = 'flickr:photosdownload';
            $processCmd [] = '--email='.$fromAddress;

            $this->log('Send mail to '.$fromAddress);

            if (preg_match_all($re, $message, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $processCmd[] = '--'.trim(str_replace('"', '', $match[0]));
                }
            }

            $processes[$emailNo] = SystemHelper::getProcess($processCmd);

            // process
            $processes[$emailNo]->run();
            imap_delete($this->connection, $emailNo);
        }

        imap_expunge($this->connection);

        return true;
    }
}
