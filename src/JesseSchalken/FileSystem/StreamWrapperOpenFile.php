<?php

namespace JesseSchalken\FileSystem;

final class StreamWrapperOpenFile extends AbstractOpenFile {
    /** @var resource */
    private $handle;

    /**
     * @param resource $resource
     */
    public function __construct($resource) {
        $this->handle = $resource;
    }

    public function __destruct() {
        return fclose($this->handle);
    }

    public function read($count) {
        return fread($this->handle, $count);
    }

    public function toResource() {
        return $this->handle;
    }

    public function isEOF() {
        return feof($this->handle);
    }

    public function flush() {
        return fflush($this->handle);
    }

    public function setLock(FileLock $lock) {
        return flock($this->handle, $lock->value());
    }

    public function setLockNoBlock(FileLock $lock) {
        return flock($this->handle, $lock->value() & LOCK_NB);
    }

    public function setPosition($position, $fromEnd) {
        return fseek($this->handle, $position, $fromEnd ? SEEK_END : SEEK_SET);
    }

    public function addPosition($position) {
        return fseek($this->handle, $position, SEEK_CUR);
    }

    public function getPosition() {
        return ftell($this->handle);
    }

    public function setSize($size) {
        return ftruncate($this->handle, $size);
    }

    public function write($data) {
        return fwrite($this->handle, $data);
    }

    public function getAttributes() {
        $stat = fstat($this->handle);
        return $stat ? new \JesseSchalken\FileSystem\StreamWrapperFileAttributes($stat) : null;
    }

    public function setBlocking($blocking) {
        return stream_set_blocking($this->handle, $blocking ? 1 : 0);
    }

    public function setReadTimeout($seconds, $microseconds) {
        return stream_set_timeout($this->handle, $seconds, $microseconds);
    }

    public function setWriteBuffer($size) {
        return stream_set_write_buffer($this->handle, $size);
    }
}