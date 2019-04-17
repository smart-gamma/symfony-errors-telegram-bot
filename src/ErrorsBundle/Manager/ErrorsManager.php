<?php

namespace Gamma\ErrorsBundle\Manager;

use Gamma\ErrorsBundle\Entity\Error;
use Gamma\ErrorsBundle\Exception\ExtendedExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class ErrorsManager.
 */
class ErrorsManager
{
    /** @var ChannelsManager */
    private $channelManager;
    /** @var RequestStack */
    private $requestStack;
    /** @var TokenStorage */
    private $tokenStorage;
    /** @var LoggerInterface */
    private $logger;
    /** @var Error[] */
    private $errors;
    /** @var string */
    private $baseHost;
    /** @var int */
    private $repeatNotificationMinutes;

    /**
     * ErrorsManager constructor.
     *
     * @param \Gamma\ErrorsBundle\Manager\ChannelsManager $channelsManager
     * @param RequestStack                                $requestStack
     * @param TokenStorage                                $tokenStorage
     * @param LoggerInterface                             $logger
     * @param string                                      $baseHost
     */
    public function __construct(
        ChannelsManager $channelsManager,
        RequestStack $requestStack,
        TokenStorage $tokenStorage,
        LoggerInterface $logger,
        $baseHost
    ) {
        $this->channelManager = $channelsManager;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->baseHost = $baseHost;
    }

    /**
     * Logs error.
     *
     * @param Error $error
     */
    public function logError(Error $error)
    {
        $this->errors[] = $error;
        $this->proceedErrors();
    }

    /**
     * Logs exception.
     *
     * @param \Exception $ex
     */
    public function logException(\Exception $ex)
    {
        $params = $ex instanceof ExtendedExceptionInterface ? $ex->getParams() : array();
        $hash = $ex instanceof ExtendedExceptionInterface ? $ex->getHash() : md5($ex->getMessage().$ex->getFile().$ex->getLine());

        $error = new Error();
        $error
            ->setCode($ex->getCode())
            ->setMessage($ex->getMessage())
            ->setFile($ex->getFile())
            ->setLine($ex->getLine())
            ->setBacktrace($ex->getTraceAsString())
            ->setType(get_class($ex))
            ->setSeverity(Error::SEVERITY_FATAL)
            ->setParams($params)
            ->setHash($hash);

        $this->logError($error);
    }

    /**
     * Logs user error.
     *
     * @param $message
     */
    public function logMessage($message)
    {
        if (!is_string($message) || 0 == strlen($message)) {
            throw new \InvalidArgumentException('Not set error message');
        }

        $ex = new \Exception();
        $prevTraceItem = [];
        foreach ($ex->getTrace() as $traceItem) {
            if (array_key_exists('class', $traceItem) && __CLASS__ !== $traceItem['class']) {
                break;
            }

            $prevTraceItem = $traceItem;
        }

        $file = array_key_exists('file', $prevTraceItem) ? $prevTraceItem['file'] : '';
        $line = array_key_exists('line', $prevTraceItem) ? $prevTraceItem['file'] : 0;

        $error = new Error();
        $error
            ->setMessage($message)
            ->setFile($file)
            ->setLine($line)
            ->setType('message')
            ->setSeverity(Error::SEVERITY_NOTICE)
            ->setBacktrace($ex->getTraceAsString());

        $this->logError($error);
    }

    /**
     * Proceed errors.
     */
    public function proceedErrors()
    {
        if (count($this->errors)) {
            $preparedErrors = [];
            foreach ($this->errors as $error) {
                if (!$error->getHash()) {
                    $error->setHash(md5($error->getMessage().$error->getFile().$error->getLine()));
                }

                try {
                    $request = $this->requestStack->getMasterRequest();
                    if ($request) {
                        $error->setUrl($request->getPathInfo());
                        $error->setReferrer($request->headers->get('referer', ''));
                        $error->setGlobalVars($request->server->all());
                    } else {
                        $error->setUrl('');
                        $error->setReferrer('');
                        $error->setGlobalVars([]);
                    }
                    $error->setBaseHost($this->baseHost);
                    $error->setHostName(gethostname());
                    $error->setRequestContent($request->getContent());

                    $token = $this->tokenStorage->getToken();
                    if (null !== $token) {
                        $error->setUsername($token->getUsername());
                    }
                    $preparedErrors[] = $error;
                } catch (\Exception $ex) {
                    $this->logger->error('Gamma\ErrorsBundle: Error with persist error: '.$ex->getMessage());
                    $this->logger->error(
                        $error->getMessage(),
                        [
                            'file' => $error->getFile(),
                            'line' => $error->getLine(),
                            'traceAsString' => $error->getBacktrace(),
                        ]
                    );
                }
            }

            if (count($preparedErrors)) {
                $this->distributeToChannels($preparedErrors);
            }

            $this->errors = [];
        }
    }

    /**
     * @param array $errors
     */
    private function distributeToChannels(array &$errors)
    {
        foreach ($this->channelManager->getChannels() as $channel) {
            foreach ($errors as $error) {
                try {
                    $channel->send($error);
                } catch (\Exception $ex) {
                    $this->logger->error('Gamma\ErrorsBundle: chanel '.$channel.' send error: '.$ex->getMessage());
                }
            }
        }
    }

    /**
     * Checks if required to repeat notification.
     *
     * @param Error $error
     *
     * @return bool
     */
    private function checkNotificationRepeatDate(Error $error)
    {
        $minRepeatDate = new \DateTime(sprintf('-%d minute', $this->repeatNotificationMinutes));

        return $error->getNotificationDate() < $minRepeatDate;
    }

    /**
     * Sets minutes number before notification about repeated error.
     *
     * @param int $minutes
     */
    public function setRepeatNotificationMinutes($minutes)
    {
        $this->repeatNotificationMinutes = $minutes;
    }
}
