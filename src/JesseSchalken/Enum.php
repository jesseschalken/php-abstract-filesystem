<?php

namespace JesseSchalken\Enum;

interface EnumAbstract {
    /** @return int[] */
    static function values();
}

abstract class Enum implements EnumAbstract {
    /** @var int */
    private $value;

    /**
     * @param int $value
     * @throws EnumException
     */
    function __construct($value) {
        $this->value = (int)$value;
        if (!in_array($this->value, static::values())) {
            throw new EnumException("'$this->value' must be one of " . join("', '", static::values()));
        }
    }

    final function value() { return $this->value; }
    final function equals(self $that) { return $this->value === $that->value; }
}

class EnumException extends \Exception {
}
