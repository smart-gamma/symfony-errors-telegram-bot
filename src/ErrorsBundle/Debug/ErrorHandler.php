<?php

namespace Gamma\ErrorsBundle\Debug;

use Gamma\ErrorsBundle\Entity\Error;
use Gamma\ErrorsBundle\Manager\ErrorsManager;
use Symfony\Component\Debug\ErrorHandler as SymfonyErrorHandler;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ErrorHandler.
 */
class ErrorHandler
{
    const MAX_ERRORS_PER_SCRIPT = 1000;

    /** @var ErrorsManager */
    private $errorManager;
    /** @var mixed */
    private $oldErrorHandler;
    /** @var bool */
    private $logStrictErrors = false;

    /**
     * ErrorHandler constructor.
     *
     * @param ErrorsManager $errorManager
     * @param bool          $logStrictErrors
     */
    public function __construct(ErrorsManager $errorManager, $logStrictErrors)
    {
        $this->errorManager = $errorManager;
        $this->logStrictErrors = $logStrictErrors;
    }

    /**
     * Registers PHP errors handlers.
     */
    public function register()
    {
        $previousErrorHandler = set_error_handler([$this, 'onError']);
        if (null !== $previousErrorHandler) {
            $this->oldErrorHandler = $previousErrorHandler;
        }

        register_shutdown_function([$this, 'onExit']);
    }

    /**
     * Processes uncatched exceptions.
     *
     * @param \Exception $ex
     */
    public function onException(\Exception $ex)
    {
        if ($ex instanceof NotFoundHttpException ||
            $ex instanceof AccessDeniedHttpException ||
            $ex instanceof MethodNotAllowedHttpException ||
            $ex instanceof ContextErrorException
        ) {
            return;
        }

        // Fix fatal errors double logging for PHP version prior to 7.0
        if (version_compare(phpversion(), '7.0', '<') && $ex instanceof FatalErrorException) {
            return;
        }

        $this->errorManager->logException($ex);
    }

    /**
     * Checks errors log on script shutdown to process fatal errors.
     */
    public function onExit()
    {
        $lastError = error_get_last();

        if (null !== $lastError && Error::SEVERITY_FATAL === $this->getSeverityByCode($lastError['type'])) {
            /** @var SymfonyErrorHandler $coreHandler */
            $coreHandler = $this->oldErrorHandler;
            $this->oldErrorHandler = null;
            $this->onError($lastError['type'], $lastError['message'], $lastError['file'], $lastError['line'], []);

            SymfonyErrorHandler::register($coreHandler, true);
            SymfonyErrorHandler::handleFatalError();
        }
    }

    /**
     * Handles PHP error.
     *
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @return bool
     */
    public function onError($code, $message, $file, $line, array $context)
    {
        static $count = 0;

        if ($message && $this->isErrorLoggableByCode($code) && !$this->isSuppressedError()) {
            if (++$count <= self::MAX_ERRORS_PER_SCRIPT) {
                $severity = $this->getSeverityByCode($code);

                $error = new Error();
                $error->setMessage($message)
                    ->setCode($code)
                    ->setFile($file)
                    ->setLine($line)
                    ->setSeverity($severity)
                    ->setType('php')
                    ->setBacktrace((new \Exception())->getTraceAsString());

                if (Error::SEVERITY_FATAL === $severity) {
                    if (null !== ($altBacktrace = $this->getAlternativeBacktrace())) {
                        $error->setBacktrace($altBacktrace);
                    }
                }

                $this->errorManager->logError($error);
            }
        }

        if (null !== $this->oldErrorHandler) {
            return call_user_func_array($this->oldErrorHandler, [$code, $message, $file, $line, $context]);
        }

        return false;
    }

    /**
     * Gets severity alias by PHP error.
     *
     * @return string
     */
    private function getSeverityByCode($code)
    {
        $codeRelations = [
            E_ERROR => Error::SEVERITY_FATAL,
            E_USER_ERROR => Error::SEVERITY_FATAL,
            E_PARSE => Error::SEVERITY_FATAL,
            E_RECOVERABLE_ERROR => Error::SEVERITY_FATAL,
            E_CORE_ERROR => Error::SEVERITY_FATAL,
            E_COMPILE_ERROR => Error::SEVERITY_FATAL,
            E_WARNING => Error::SEVERITY_WARNING,
            E_USER_WARNING => Error::SEVERITY_WARNING,
            E_CORE_WARNING => Error::SEVERITY_WARNING,
            E_COMPILE_WARNING => Error::SEVERITY_WARNING,
            E_NOTICE => Error::SEVERITY_NOTICE,
            E_USER_NOTICE => Error::SEVERITY_NOTICE,
            E_STRICT => Error::SEVERITY_STRICT,
            E_DEPRECATED => Error::SEVERITY_DEPRECATED,
            E_USER_DEPRECATED => Error::SEVERITY_DEPRECATED,
        ];

        return array_key_exists($code, $codeRelations) ? $codeRelations[$code] : Error::SEVERITY_NOTICE;
    }

    /**
     * Gets aliternative backtrace for fatal error case.
     *
     * @return string|null
     */
    private function getAlternativeBacktrace()
    {
        if (function_exists('xdebug_get_function_stack')) {
            $trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);

            return $this->translateBacktraceArrayToString($trace);
        }
    }

    /**
     * Checks if error is loggable by its PHP code.
     *
     * @param int $code
     *
     * @return bool
     */
    private function isErrorLoggableByCode($code)
    {
        $severity = $this->getSeverityByCode($code);
        if (false === $this->logStrictErrors) {
            $isStrictError = in_array($severity, [Error::SEVERITY_STRICT, Error::SEVERITY_DEPRECATED], true);

            return !$isStrictError;
        }

        return true;
    }

    /**
     * Checks if error is suppressed.
     *
     * @return bool
     */
    private function isSuppressedError()
    {
        return 0 === error_reporting();
    }

    /**
     * Formats backtrace array as string.
     *
     * @param array $trace
     *
     * @return string
     */
    private function translateBacktraceArrayToString(array $trace)
    {
        $result = '';
        $count = 0;
        foreach ($trace as $row) {
            $args = '';
            if (array_key_exists('params', $row) && is_array($row['params'])) {
                $args = implode(', ', array_keys($row['params']));
            }

            $result .= sprintf(
                "#%s %s(%s): %s(%s)\n",
                $count,
                array_key_exists('file', $row) ? $row['file'] : 'unknown file',
                array_key_exists('line', $row) ? $row['line'] : 'unknown line',
                array_key_exists('class', $row) ?
                    $row['class'].'->'.$row['function'] :
                    (array_key_exists('function', $row) ? $row['function'] : ''),
                $args
            );
            ++$count;
        }

        return $result;
    }
}
