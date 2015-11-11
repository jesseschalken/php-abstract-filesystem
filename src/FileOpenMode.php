<?php

final class FileOpenMode {
    const EXISTING_ONLY_READ_ONLY       = 'r';
    const EXISTING_ONLY_READ_WRITE      = 'r+';
    const CREATE_ONLY_WRITE_ONLY        = 'x';
    const CREATE_ONLY_READ_WRITE        = 'x+';
    const CREATE_OR_TRUNCATE_WRITE_ONLY = 'w';
    const CREATE_OR_TRUNCATE_READ_WRITE = 'w+';
    const CREATE_OR_APPEND_WRITE_ONLY   = 'a';
    const CREATE_OR_APPEND_READ_WRITE   = 'a+';
    const CREATE_OR_EXISTING_WRITE_ONLY = 'c';
    const CREATE_OR_EXISTING_READ_WRITE = 'c+';

    private $values = [
        self::EXISTING_ONLY_READ_ONLY,
        self::EXISTING_ONLY_READ_WRITE,
        self::CREATE_ONLY_WRITE_ONLY,
        self::CREATE_ONLY_READ_WRITE,
        self::CREATE_OR_TRUNCATE_WRITE_ONLY,
        self::CREATE_OR_TRUNCATE_READ_WRITE,
        self::CREATE_OR_APPEND_WRITE_ONLY,
        self::CREATE_OR_APPEND_READ_WRITE,
        self::CREATE_OR_EXISTING_WRITE_ONLY,
        self::CREATE_OR_EXISTING_READ_WRITE,
    ];

    private $value;

    function __construct($value) {
        if (!in_array($valuem, self::$values)) {
            throw new Exception("'$value' must be one of: " . join(', ', self::$values));
        }
        $this->value = $value;
    }

    function value() {
        return $this->value;
    }

    function equal(self $that) {
        return $this->value === $that->value;
    }

    function create() {
        return $this->value[0] !== 'r';
    }

    function write() {
        return $this->value !== 'r';
    }

    function append() {
        return $this->value[0] === 'a';
    }

    function read() {
        return $this->value[0] === 'r' ||
               substr($this->value, 1, 1) === '+';
    }

    function truncate() {
        return $this->value[0] === 'w';
    }

    function existing() {
        return $this->value[0] !== 'x';
    }
}
