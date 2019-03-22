<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Created by PhpStorm.
 * User: SoulEvil
 * Date: 2/17/2019
 * Time: 9:24 PM
 */

namespace XGallery\Events;

use Symfony\Component\EventDispatcher\Event;

class Test
{
    // ...

    public function onFooAction(Event $event)
    {
        echo 'xxx';
    }
}
