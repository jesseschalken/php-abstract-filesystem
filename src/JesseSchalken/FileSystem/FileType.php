<?php

namespace JesseSchalken\FileSystem;

use JesseSchalken\Enum;

final class FileType extends Enum {
    const PIPE   = 001;
    const CHAR   = 002;
    const DIR    = 004;
    const BLOCK  = 006;
    const FILE   = 010;
    const LINK   = 012;
    const SOCKET = 014;
    const DOOR   = 015;

    static function values() {
        static $values = [
            self::PIPE,
            self::CHAR,
            self::DIR,
            self::BLOCK,
            self::FILE,
            self::LINK,
            self::SOCKET,
            self::DOOR,
        ];
        return $values;
    }
}