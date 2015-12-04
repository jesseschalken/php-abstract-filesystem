<?php

namespace JesseSchalken\FileSystem;

final class StreamWrapperOpenFile extends AbstractOpenFile {
    private static function check($function, \Closure $c) {
        return StreamWrapperFileSystem::check($function, $c);
    }

    /** @var resource */
    private $handle;

    /**
     * @param resource $resource
     */
    public function __construct($resource) {
        $this->handle = $resource;
    }

    public function __destruct() {
        self::check('fclose', function () {
            return fclose($this->handle);
        });
    }

    public function read($count) {
        self::check('fread', function () use ($count) {
            return fread($this->handle, $count);
        });
    }

    public function toResource() {
        return $this->handle;
    }

    public function isEof() {
        self::check('feof', function () use (&$eof) {
            $eof = feof($this->handle);
        });
        return $eof;
    }

    public function flush() {
        self::check('fflush', function () {
            fflush($this->handle);
        });
    }

    public function setLock(FileLock $lock) {
        self::check('flock', function () use ($lock) {
            return flock($this->handle, $lock->value());
        });
    }

    public function setLockNoBlock(FileLock $lock) {
        self::check('flock', function () use ($lock, &$success) {
            $success = flock($this->handle, $lock->value() & LOCK_NB);
        });
        return $success;
    }

    public function setPosition($position, $fromEnd) {
        self::check('fseek', function () use ($position, $fromEnd) {
            return fseek($this->handle, $position, $fromEnd ? SEEK_END : SEEK_SET);
        });
    }

    public function addPosition($position) {
        self::check('fseek', function () use ($position) {
            return fseek($this->handle, $position, SEEK_CUR);
        });
    }

    public function getPosition() {
        return self::check('ftell', function () {
            return ftell($this->handle);
        });
    }

    public function setSize($size) {
        self::check('ftruncate', function () use ($size) {
            return ftruncate($this->handle, $size);
        });
    }

    public function write($data) {
        self::check('fwrite', function () use ($data) {
            return fwrite($this->handle, $data);
        });
    }

    public function getAttributes() {
        $stat = self::check('fstat', function () {
            return fstat($this->handle);
        });
        return $stat ? new StreamWrapperFileAttributes($stat) : null;
    }

    public function setBlocking($blocking) {
        self::check('stream_set_blocking', function () use ($blocking) {
            return stream_set_blocking($this->handle, $blocking ? 1 : 0);
        });
    }

    public function setReadTimeout($seconds, $microseconds) {
        self::check('stream_set_timeout', function () use ($seconds, $microseconds) {
            return stream_set_timeout($this->handle, $seconds, $microseconds);
        });
    }

    public function setWriteBuffer($size) {
        self::check('stream_set_write_buffer', function () use ($size) {
            return stream_set_write_buffer($this->handle, $size);
        });
    }
}
