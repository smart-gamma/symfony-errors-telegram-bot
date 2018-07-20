<?php

namespace Gamma\ErrorsBundle\Manager;

use Gamma\ErrorsBundle\Channel\AbstractChannel;

/**
 * Class ChannelsManager.
 */
class ChannelsManager
{
    /** @var AbstractChannel[] */
    private $channels = [];

    /**
     * Sets channel.
     *
     * @param string          $alias
     * @param AbstractChannel $channel
     */
    public function addChannel($alias, AbstractChannel $channel)
    {
        $this->channels[$alias] = $channel;
    }

    /**
     * Gets channel object by alisa.
     *
     * @param string $alias
     *
     * @return AbstractChannel
     */
    public function getChannelByAlias($alias)
    {
        if (array_key_exists($alias, $this->channels)) {
            return $this->channels[$alias];
        }
    }

    /**
     * Gets all registered channels.
     *
     * @return AbstractChannel[]
     */
    public function getChannels()
    {
        return $this->channels;
    }
}
