<?php

namespace Gamma\ErrorsBundle\Subscriber;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Gamma\ErrorsBundle\Event\ErrorEvent;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class ErrorSendSubscriber.
 */
class ErrorSendSubscriber implements EventSubscriberInterface
{
    /** @var ObjectRepository */
    private $userRepository;
    /** @var Router */
    private $router;
    /** @var TwigEngine */
    private $templating;

    /**
     * ErrorSendSubscriber constructor.
     *
     * @param ObjectRepository $userRepository
     * @param Router           $router
     * @param TwigEngine|\Twig_Environment       $templating
     */
    public function __construct(ObjectRepository $userRepository = null, Router $router, $templating)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ErrorEvent::PRE_ERROR_SEND => ['prepareError'],
        ];
    }

    public function prepareError(ErrorEvent $event)
    {
        $error = $event->getError();
        $username = $error->getUsername();

        /** @var UserInterface $user */
        $user = $this->userRepository ? $this->userRepository->findOneBy(['username' => $username]) : null;
        if (null !== $user) {
            $params = $error->getParams();
            /*
            $userAdminUrl   = $this->router->generate('admin_gamma_common_useraccount_edit', ['id' => $user->getId()], UrlGenerator::ABSOLUTE_URL);
            */
            $userAdminUrl = '#';
            $params['user'] = $this->decorateLink($userAdminUrl, $user->getUsername(), $event->getChannelAlias());

            $error->setParams($params);
        }
    }

    /**
     * Decorates link.
     *
     * @param string $link
     * @param string $value
     * @param string $channelName
     *
     * @return string
     */
    private function decorateLink($link, $value, $channelName)
    {
        return $this->templating->render(
            sprintf('GammaErrorsBundle:Channel:%s/link.html.twig', ucfirst($channelName)),
            [
                'link' => $link,
                'value' => $value,
            ]
        );
    }
}
