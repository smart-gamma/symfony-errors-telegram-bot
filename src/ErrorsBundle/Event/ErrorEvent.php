<?php

namespace Gamma\ErrorsBundle\Event;

use Gamma\ErrorsBundle\Entity\Error;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ErrorEvent.
 */
class ErrorEvent extends Event
{
    const PRE_ERROR_SEND = 'gamma_errors.pre_error_send';

    /** @var Error */
    private $error;
    /** @var string */
    private $channelAlias;

    /**
     * ErrorEvent constructor.
     *
     * @param Error  $error
     * @param string $channelAlias
     */
    public function __construct(Error $error, $channelAlias)
    {
        $this->error = $error;
        $this->channelAlias = $channelAlias;
    }

    /**
     * Gets error.
     *
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Gets channel alias.
     *
     * @return string
     */
    public function getChannelAlias()
    {
        return $this->channelAlias;
    }
}
