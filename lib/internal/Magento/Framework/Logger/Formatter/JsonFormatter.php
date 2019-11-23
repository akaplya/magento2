<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * Class JsonFormatter
 */
class JsonFormatter extends \Monolog\Formatter\JsonFormatter implements FormatterInterface
{

    /**
     * @param array $record
     * @return array|mixed|string
     */
    public function format(array $record)
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
            $record['message'] = [
                'message' => $record['context']['exception']->getMessage(),
                'trace' => $record['context']['exception']->getTrace()
            ];
        }
        $string = parent::format($record);
        return $string;
    }
}
