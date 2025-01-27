<?php

use devpirates\MVC\Base\LoggerBase;
use devpirates\MVC\Interfaces\LogLevels;

class HTMLCommentLogger extends LoggerBase {
    public function __construct() {
        echo "<!-- Logger initialized -->";
    }

    public function _Log(string $trace, string $message, int $level): void {
        $levelName = "";
        switch ($level) {
            case LogLevels::TRACE:
                $levelName = "TRACE";
                break;
            case LogLevels::DEBUG:
                $levelName = "DEBUG";
                break;
            case LogLevels::INFO:
                $levelName = "INFO";
                break;
            case LogLevels::WARNING:
                $levelName = "WARNING";
                break;
            case LogLevels::ERROR:
                $levelName = "ERROR";
                break;
        }

        echo "<!-- $levelName: $trace - $message -->";
    }
}

?>