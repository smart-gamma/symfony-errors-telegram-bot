<?php

namespace Gamma\ErrorsBundle\Channel;

use Gamma\ErrorsBundle\Entity\Error;
use Gamma\ErrorsBundle\Event\ErrorEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TelegramBot\Api\BotApi;

/**
 * Class TelegramChannel.
 */
class TelegramChannel extends AbstractChannel
{
    /** @var Router */
    private $router;
    /** @var TwigEngine */
    private $twigEngine;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var LoggerInterface */
    private $logger;
    /** @var string */
    protected $authKey;
    /** @var string */
    protected $chatId;

    /**
     * TelegramChannel constructor.
     *
     * @param Router                   $router
     * @param TwigEngine|\Twig_Environment               $twigEngine
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(
        Router $router,
        $twigEngine,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->router = $router;
        $this->twigEngine = $twigEngine;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * Sends error to email.
     *
     * @param Error $error
     *
     * @return bool
     */
    public function send(Error $error)
    {
        $botApi = new BotApi($this->authKey);
        try {
            $botApi->sendMessage($this->chatId, $this->getMessageBodyByError($error), 'html');
        } catch (\Exception $ex) {
            $this->logger->warning('Gamma\ErrorsBundle: Failed to send message with error via Telegram channel: '.$ex->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Prepares error mail body.
     *
     * @param Error $error
     *
     * @return string
     */
    private function getMessageBodyByError(Error $error)
    {
        $this->dispatcher->dispatch(ErrorEvent::PRE_ERROR_SEND, new ErrorEvent($error, 'telegram'));

        $params = [];
        foreach ($error->getParams() as $key => $value) {
            $newKey = str_replace('_', ' ', ucfirst($key));
            $params[$newKey] = $value;
        }

        return $this->twigEngine->render(
            'GammaErrorsBundle:Channel:Telegram/message.html.twig',
            [
                'error' => $error,
                'params' => $params,
            ]
        );
    }

    /**
     * Sets bot auth key.
     *
     * @param string $authKey
     */
    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;
    }

    /**
     * Sets Telegram chat ID.
     *
     * @param string $chatId
     */
    public function setChatId($chatId)
    {
        $this->chatId = $chatId;
    }
}
