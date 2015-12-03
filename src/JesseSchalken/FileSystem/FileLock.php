<?php

namespace JesseSchalken\FileSystem;

use JesseSchalken\Enum;

final class FileLock extends Enum {
    const SHARED    = LOCK_SH;
    const EXCLUSIVE = LOCK_EX;
    const NONE      = LOCK_UN;

    static function values() {
        static $values = [
            self::NONE,
            self::EXCLUSIVE,
            self::SHARED,
        ];
        return $values;
    }
}