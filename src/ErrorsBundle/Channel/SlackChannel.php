<?php

namespace Gamma\ErrorsBundle\Channel;

use Gamma\ErrorsBundle\Entity\Error;
use Gamma\ErrorsBundle\Event\ErrorEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SlackChannel extends AbstractChannel
{
    /** @var Router */
    private $router;
    /** @var TwigEngine */
    private $templating;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var LoggerInterface */
    private $logger;
    /** @var string */
    protected $webhook;
    /** @var string */
    protected $slackChannel;

    /**
     * TelegramChannel constructor.
     *
     * @param Router                   $router
     * @param TwigEngine               $templating
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(
        Router $router,
        $templating,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->router = $router;
        $this->templating = $templating;
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
        $slack = new \Slack($this->webhook);
        $message = new \SlackMessage($slack);
        $message->setText(\htmlspecialchars($this->getMessageBodyByError($error)))->setChannel($this->slackChannel);

        try {
            $message->send();
        } catch (\Exception $ex) {
            $this->logger->warning('Gamma\ErrorsBundle: Failed to send message with error via Slack channel: '.$ex->getMessage());

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
        $this->dispatcher->dispatch(ErrorEvent::PRE_ERROR_SEND, new ErrorEvent($error, 'slack'));

        $params = [];
        foreach ($error->getParams() as $key => $value) {
            $newKey = str_replace('_', ' ', ucfirst($key));
            $params[$newKey] = $value;
        }

        return $this->templating->render(
            'GammaErrorsBundle:Channel:Slack/message.html.twig',
            [
                'error' => $error,
                'params' => $params,
            ]
        );
    }

    /**
     * Sets bot webhook
     *
     * @param string $authKey
     */
    public function setWebhook(string $webhook): void
    {
        $this->webhook = $webhook;
    }

    /**
     * Sets bot auth key.
     *
     * @param string $authKey
     */
    public function setSlackChannel(string $channel): void
    {
        $this->slackChannel = $channel;
    }
}
