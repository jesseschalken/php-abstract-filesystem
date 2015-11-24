<?php

final class FileOpenMode {
    const READ_EXISTING   = 'r';
    const CREATE_NEW      = 'x';
    const CREATE_TRUNCATE = 'w';
    const CREATE_APPEND   = 'a';
    const CREATE_KEEP     = 'c';

    private static $values = [
        self::READ_EXISTING,
        self::CREATE_NEW,
        self::CREATE_TRUNCATE,
        self::CREATE_APPEND,
        self::CREATE_KEEP,
    ];

    private $value;

    public function __construct($value) {
        $this->value = "$value";
        if (!in_array($this->value, self::$values)) {
            throw new Exception("'$this->value' must be one of: " . join(', ', self::$values));
        }
    }

    public function value() { return $this->value; }
    public function equal(self $that) { return $that->value === $this->value; }
}

