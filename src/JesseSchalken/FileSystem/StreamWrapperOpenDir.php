<?php

namespace JesseSchalken\FileSystem;

final class StreamWrapperOpenDir implements \Iterator {
    private static function check($function, \Closure $c) {
        return StreamWrapperFileSystem::check($function, $c);
    }

    private $key = 0;
    private $handle;
    private $current;

    /**
     * @param resource $handle
     */
    public function __construct($handle) {
        $this->handle = $handle;
    }

    public function __destruct() {
        if ($this->handle) {
            self::check('closedir', function () {
                closedir($this->handle);
            });
            $this->handle = null;
        }
    }

    public function current() {
        if ($this->current === null) {
            self::check('readdir', function () {
                $this->current = readdir($this->handle);
            });
        }
        return $this->current;
    }

    public function next() {
        if ($this->current === null) {
            self::check('readdir', function () {
                readdir($this->handle);
            });
        } else {
            $this->current = null;
        }
        $this->key++;
    }

    public function valid() {
        return $this->current() !== false;
    }

    public function key() {
        return $this->key;
    }

    public function rewind() {
        $this->key = 0;
        self::check('rewinddir', function () {
            rewinddir($this->handle);
        });
    }
}