<?php

namespace Gamma\ErrorsBundle\Exception;

/**
 * Class ExtendedExceptionInterface.
 */
abstract class ExtendedExceptionInterface extends \Exception
{
    /**
     * Get addiotional exception parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return [];
    }

    /**
     * Generate error hash.
     *
     * @return string
     */
    public function getHash()
    {
        return md5($this->getMessage().$this->getFile().$this->getLine());
    }
}
