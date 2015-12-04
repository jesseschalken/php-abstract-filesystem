<?php

namespace JesseSchalken\FileSystem;

/**
 * Stream wrapper for abstract file systems
 */
class AbstractFileSystemStreamWrapper extends AbstractStreamWrapper {
    /**
     * @var AbstractFileSystem[]
     */
    private static $ids = [];

    /**
     * @param int $id
     * @return AbstractFileSystem
     */
    static function getFileSystem($id) {
        return self::$ids[$id];
    }

    /**
     * @var int
     */
    private $id;

    function __construct(AbstractFileSystem $fs) {
        static $id = 1;
        $this->id = $id++;

        self::$ids[$this->id] = $fs;
    }

    function __destruct() {
        unset(self::$ids[$this->id]);
    }

    function getUrl($path) {
        static $protocol;
        if ($protocol === null) {
            $protocol = '__afs' . mt_rand();
            stream_wrapper_register($protocol, __streamWrapper::class, STREAM_IS_URL);
        }
        return "$protocol://$this->id:$path";
    }
}

