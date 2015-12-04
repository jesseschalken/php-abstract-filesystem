<?php

namespace JesseSchalken\FileSystem;

use Exception;

class ErrorException extends Exception {
    public function __construct($code, $message, $file, $line) {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
    }
}