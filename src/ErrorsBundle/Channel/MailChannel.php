<?php

namespace Gamma\ErrorsBundle\Channel;

use Gamma\ErrorsBundle\Entity\Error;
use Gamma\ErrorsBundle\Event\ErrorEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class MailChannel.
 */
class MailChannel extends AbstractChannel
{
    /** @var \Swift_Mailer */
    private $mailer;
    /** @var Router */
    private $router;
    /** @var TwigEngine */
    private $twigEngine;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var LoggerInterface */
    private $logger;
    /** @var array */
    protected $emails = [];
    /** @var string */
    protected $fromEmail;
    /** @var string */
    protected $fromName;

    /**
     * MailChannel constructor.
     *
     * @param \Swift_Mailer   $mailer
     * @param Router          $router
     * @param TwigEngine|\Twig_Environment      $twigEngine
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Swift_Mailer $mailer,
        Router $router,
        $twigEngine,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
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
        if (count($this->emails)) {
            $message = \Swift_Message::newInstance()
                ->setFrom($this->fromEmail, $this->fromName)
                ->setTo($this->emails)
                ->setSubject($this->getMailSubjectByError($error))
                ->setBody($this->getMailBodyByError($error), 'text/html');

            try {
                $this->mailer->send($message);
            } catch (\Exception $ex) {
                $this->logger->warning('Gamma\ErrorsBundle: Failed to send message with error via mail channel: '.$ex->getMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * Gets error mail subject.
     *
     * @param Error $error
     *
     * @return string
     */
    private function getMailSubjectByError(Error $error)
    {
        $message = substr($error->getMessage(), 0, 100);
        $message = str_ireplace(["\r", "\n", '<br/>', '<br />'], ' ', $message);

        return sprintf('%s(%s) [%s - %s]: %s', $error->getBaseHost(), $error->getHostName(), $error->getType(), $error->getSeverity(), $message);
    }

    /**
     * Prepares error mail body.
     *
     * @param Error $error
     *
     * @return string
     */
    private function getMailBodyByError(Error $error)
    {
        $errorAdminUrl = $this->router->generate('admin_gamma_errors_error_show', ['id' => $error->getId()], UrlGenerator::ABSOLUTE_URL);

        $this->dispatcher->dispatch(ErrorEvent::PRE_ERROR_SEND, new ErrorEvent($error, 'mail'));

        $params = [];
        foreach ($error->getParams() as $key => $value) {
            $newKey = str_replace('_', ' ', ucfirst($key));
            $params[$newKey] = $value;
        }

        $globalVars = [];
        foreach ($error->getGlobalVars() as $key => $value) {
            $newValue = is_array($value) ? implode(', ', $value) : $value;
            $globalVars[$key] = $newValue;
        }

        return $this->twigEngine->render(
            'GammaErrorsBundle:Channel:Mail/message.html.twig',
            [
                'error' => $error,
                'params' => $params,
                'globalVars' => $globalVars,
                'errorAdminUrl' => $errorAdminUrl,
            ]
        );
    }

    /**
     * Sets emails list to send errors to.
     *
     * @param array $emails
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
    }

    /**
     * Sets from email and name of the letters.
     *
     * @param string $fromEmail
     * @param string $fromName
     */
    public function setFromData($fromEmail, $fromName)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }
}
