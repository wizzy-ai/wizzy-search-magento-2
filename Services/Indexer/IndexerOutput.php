<?php

namespace Wizzy\Search\Services\Indexer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class IndexerOutput
{

    const LOG_ERROR_TYPE = "error";
    const LOG_WARNING_TYPE = "warning";
    const LOG_INFO_TYPE = "info";

    private $output;
    private $logger;

    public function __construct(ConsoleOutput $output, LoggerInterface $logger)
    {
        $this->output = $output;
        $this->logger = $logger;
    }

    public function writeln($message)
    {
        if (php_sapi_name() === 'cli') {
            $this->output->writeln($message);
        }
    }

    public function writeDiv()
    {
        if (php_sapi_name() === 'cli') {
            $i = 50;
            while ($i > 0) {
                $this->output->write('-');
                $i--;
            }
            $this->output->writeln('');
        }
    }

    public function log(array $data, $type = IndexerOutput::LOG_ERROR_TYPE)
    {
        if ($this->isValidLogType($type)) {
            $message = $this->getLogMessage($data);
            $this->logger->$type($message);
            $this->writeln($message);
        }
    }

    private function getLogMessage(array $data)
    {
        $message = PHP_EOL . "[Wizzy Log]" . PHP_EOL;
        foreach ($data as $key => $value) {
            $message .= $key . " : " . $value . PHP_EOL;
        }

        return $message;
    }

    private function isValidLogType($type)
    {
        $validLogTypes = [
         self::LOG_ERROR_TYPE => true,
         self::LOG_WARNING_TYPE => true,
         self::LOG_INFO_TYPE => true,
        ];

        return isset($validLogTypes[$type]);
    }
}
