<?php

namespace JesseSchalken\FileSystem;

final class StreamWrapperOpenDir implements \Iterator {
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
            closedir($this->handle);
            $this->handle = null;
        }
    }

    public function current() {
        if ($this->current === null) {
            $this->current = readdir($this->handle);
        }
        return $this->current;
    }

    public function next() {
        if ($this->current === null) {
            readdir($this->handle);
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
        rewinddir($this->handle);
    }
}