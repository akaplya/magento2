<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * PHP Code Mess v1.3.3 tool wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool;

use \Magento\TestFramework\CodingStandard\ToolInterface;

class CodeMessDetector implements ToolInterface
{
    /**
     * Ruleset directory
     *
     * @var string
     */
    private $rulesetFile;

    /**
     * Report file
     *
     * @var string
     */
    private $reportFile;

    /**
     * Constructor
     *
     * @param string $rulesetDir \Directory that locates the inspection rules
     * @param string $reportFile Destination file to write inspection report to
     */
    public function __construct($rulesetFile, $reportFile)
    {
        $this->reportFile = $reportFile;
        $this->rulesetFile = $rulesetFile;
    }

    /**
     * Whether the tool can be ran on the current environment
     *
     * @return bool
     */
    public function canRun()
    {
        /** TODO: Remove provided check after PHPMD will support PHP version 7 */
        $isPhpVersionSupported = version_compare(
            '7.0.0',
            preg_replace('#^([^~+-]+).*$#', '$1', PHP_VERSION),
            '>'
        );
        return class_exists('PHPMD\TextUI\Command') && $isPhpVersionSupported;
    }

    /**
     * {@inheritdoc}
     */
    public function run(array $whiteList)
    {
        if (empty($whiteList)) {
            return \PHPMD\TextUI\Command::EXIT_SUCCESS;
        }

        $commandLineArguments = [
            'run_file_mock', //emulate script name in console arguments
            implode(',', $whiteList),
            'xml', //report format
            $this->rulesetFile,
            '--reportfile',
            $this->reportFile,
        ];

        $options = new \PHPMD\TextUI\CommandLineOptions($commandLineArguments);

        $command = new \PHPMD\TextUI\Command();

        return $command->run($options, new \PHPMD\RuleSetFactory());
    }
}
