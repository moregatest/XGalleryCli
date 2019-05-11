<?php

namespace XGallery\Applications\Cli\Commands\Now;

use PHPMailer\PHPMailer\Exception;
use ReflectionException;
use XGallery\Applications\Cli\Commands\AbstractCommandNow;
use XGallery\Factory;

/**
 * Class Notifications
 * @package XGallery\Applications\Cli\Commands\Now
 */
class Notifications extends AbstractCommandNow
{
    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Send notifications');
        $this->options = [
            'categories' => [
            ],
        ];

        parent::configure();
    }

    /**
     * processSendNotifications
     * @return bool
     * @throws Exception
     */
    protected function processSendNotifications()
    {
        $categoryIds = $this->getOption('categories');

        if (empty($categoryIds)) {
            return false;
        };

        $template = Factory::getTemplate(XGALLERY_ROOT.'/templates/email/%name%');
        $html     = $template->render(
            'now.php',
            ['data' => $this->model->getDeliveriesWithPromotion($categoryIds)]
        );

        return $this->sendMail($html);
    }

    /**
     * sendMail
     * @param $html
     * @return boolean
     * @throws Exception
     */
    private function sendMail($html)
    {
        $mail          = Factory::getMailer();
        $mail->Subject = "Now - Daily promotions";
        $mail->AddAddress("soulevilx@gmail.com", 'Viet Vu');
        //$mail->AddAddress('trandieuvi.cseiu@gmail.com');
        //$mail->AddAddress('lelinh42@gmail.com');
        $mail->Body = $html;

        return $mail->Send();
    }
}
