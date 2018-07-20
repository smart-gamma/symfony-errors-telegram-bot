<?php

namespace Gamma\ErrorsBundle\Channel;

use Gamma\ErrorsBundle\Entity\Error;

/**
 * Class AbstractChannel.
 */
abstract class AbstractChannel
{
    abstract public function send(Error $error);
}
