<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Logger;

use Magento\Framework\Debug;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Monolog
 */
class Monolog extends LoggerAbstract
{
    /**
     * @var int
     */
    private $timer;

    /**
     * @var bool
     */
    private $logAllQueries;

    /**
     * @var float
     */
    private $logQueryTime;

    /**
     * @var bool
     */
    private $logCallStack;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Monolog constructor.
     *
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     */
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        $logAllQueries = false,
        $logQueryTime = 0.05,
        $logCallStack = false
    ) {
        parent::__construct($logAllQueries, $logQueryTime, $logCallStack);
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function log($str)
    {
        $str = '## ' . date('Y-m-d H:i:s') . "\r\n" . $str;
        $this->logger->debug($this->serializer->serialize($str));
    }

    /**
     * {@inheritdoc}
     */
    public function logStats($type, $sql, $bind = [], $result = null)
    {
        $stats = $this->getStatsArray($type, $sql, $bind, $result);
        if (!empty($stats)) {
            $this->logger->debug($this->serializer->serialize($stats));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function critical(\Exception $e)
    {
        $this->log("EXCEPTION \n$e\n\n");
    }


    /**
     * Get formatted statistics message
     *
     * @param string $type Type of query
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return \array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getStatsArray($type, $sql, $bind = [], $result = null)
    {
        $sql = str_replace(PHP_EOL, ' ', $sql);
        $time = sprintf('%.4f', microtime(true) - $this->timer);
        if (!$this->logAllQueries && $time < $this->logQueryTime) {
            return [];
        }
        $message = [
            'pid' => getmypid(),
            'time' => $time,
            'date' => date('Y-m-d H:i:s')
        ];
        switch ($type) {
            case self::TYPE_CONNECT:
                $message['type'] = 'CONNECT';
                break;
            case self::TYPE_TRANSACTION:
                $message['type'] = 'TRANSACTION';
                $message['sql'] = $sql;
                break;
            case self::TYPE_QUERY:
                $message['type'] = 'QUERY';
                $message['sql'] = $sql;
                if ($bind) {
                    $message['bind'] = var_export($bind, true);
                }
                if ($result instanceof \Zend_Db_Statement_Pdo) {
                    $message['aff'] = $result->rowCount();
                }
                break;
        }

        if ($this->logCallStack) {
            $message['trace'] = Debug::backtrace(true, false);
        }
        return $message;
    }
}
