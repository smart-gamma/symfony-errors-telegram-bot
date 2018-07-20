<?php

namespace Gamma\ErrorsBundle\Entity;

class Error
{
    const SEVERITY_DEPRECATED = 'DEPRECATED';
    const SEVERITY_STRICT = 'STRICT';
    const SEVERITY_NOTICE = 'NOTICE';
    const SEVERITY_WARNING = 'WARNING';
    const SEVERITY_FATAL = 'FATAL';

    protected $message;
    protected $code = 0;
    protected $file;
    protected $line;
    protected $type;
    protected $severity;
    protected $url;
    protected $referrer;
    protected $username;
    protected $params = [];
    protected $repeatCount;
    protected $hash;
    protected $globalVars = array();
    protected $backtrace;
    protected $addDate;
    protected $updateDate;
    protected $sent = false;
    protected $notificationDate;
    protected $baseHost;
    protected $hostName;

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set code.
     *
     * @param int $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set file.
     *
     * @param string $file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set line.
     *
     * @param int $line
     *
     * @return $this
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * Get line.
     *
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set severity.
     *
     * @param string $severity
     *
     * @return $this
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;

        return $this;
    }

    /**
     * Get severity.
     *
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Gets Referrer.
     *
     * @return string
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * Sets Referrer.
     *
     * @param string $referrer
     *
     * @return $this
     */
    public function setReferrer($referrer)
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * Gets Username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets Username.
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set params.
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get params.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set repeatCount.
     *
     * @param int $repeatCount
     *
     * @return $this
     */
    public function setRepeatCount($repeatCount)
    {
        $this->repeatCount = $repeatCount;

        return $this;
    }

    /**
     * Get repeatCount.
     *
     * @return int
     */
    public function getRepeatCount()
    {
        return $this->repeatCount;
    }

    /**
     * Set hash.
     *
     * @param string $hash
     *
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set globalVars.
     *
     * @param array $globalVars
     *
     * @return $this
     */
    public function setGlobalVars($globalVars)
    {
        $this->globalVars = $globalVars;

        return $this;
    }

    /**
     * Get globalVars.
     *
     * @return array
     */
    public function getGlobalVars()
    {
        return $this->globalVars;
    }

    /**
     * Set backtrace.
     *
     * @param string $backtrace
     *
     * @return $this
     */
    public function setBacktrace($backtrace)
    {
        $this->backtrace = $backtrace;

        return $this;
    }

    /**
     * Get backtrace.
     *
     * @return string
     */
    public function getBacktrace()
    {
        return $this->backtrace;
    }

    /**
     * Set addDate.
     *
     * @param \DateTime $addDate
     *
     * @return $this
     */
    public function setAddDate($addDate)
    {
        $this->addDate = $addDate;

        return $this;
    }

    /**
     * Get addDate.
     *
     * @return \DateTime
     */
    public function getAddDate()
    {
        return $this->addDate;
    }

    /**
     * Set updateDate.
     *
     * @param \DateTime $updateDate
     *
     * @return $this
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate.
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Gets Sent.
     *
     * @return bool
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Sets Sent.
     *
     * @param bool $sent
     *
     * @return $this
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Gets NotificationDate.
     *
     * @return \DateTime
     */
    public function getNotificationDate()
    {
        return $this->notificationDate;
    }

    /**
     * Sets NotificationDate.
     *
     * @param \DateTime $notificationDate
     *
     * @return $this
     */
    public function setNotificationDate(\DateTime $notificationDate)
    {
        $this->notificationDate = $notificationDate;

        return $this;
    }

    /**
     * Gets BaseHost.
     *
     * @return string
     */
    public function getBaseHost()
    {
        return $this->baseHost;
    }

    /**
     * Sets BaseHost.
     *
     * @param string $baseHost
     *
     * @return $this
     */
    public function setBaseHost($baseHost)
    {
        $this->baseHost = $baseHost;

        return $this;
    }

    /**
     * Gets HostName.
     *
     * @return string
     */
    public function getHostName()
    {
        return $this->hostName;
    }

    /**
     * Sets HostName.
     *
     * @param string $hostName
     *
     * @return $this
     */
    public function setHostName($hostName)
    {
        $this->hostName = $hostName;

        return $this;
    }
}
