<?php

namespace JesseSchalken\FileSystem;

abstract class AbstractOpenFile {
    /**
     * @param int $count
     * @return string
     */
    public abstract function read($count);

    /**
     * @return resource|null
     */
    public abstract function toResource();

    /**
     * @return bool
     */
    public abstract function isEof();

    /**
     * @return void
     * @throws Exception
     */
    public abstract function flush();

    /**
     * @param FileLock $lock
     * @return void
     */
    public abstract function setLock(FileLock $lock);

    /**
     * @param FileLock $lock
     * @return bool
     */
    public abstract function setLockNoBlock(FileLock $lock);

    /**
     * @param $position
     * @return void
     * @throws Exception
     */
    public abstract function addPosition($position);
    /**
     * @param int  $position
     * @param bool $fromEnd
     * @return void
     * @throws Exception
     */
    public abstract function setPosition($position, $fromEnd);

    /**
     * @return int
     */
    public abstract function getPosition();

    /**
     * @param int $size
     * @return void
     * @throws Exception
     */
    public abstract function setSize($size);

    /**
     * @param string $data
     * @return int
     */
    public abstract function write($data);

    /**
     * @return AbstractFileAttributes
     * @throws Exception
     */
    public function getAttributes() { return new AbstractFileAttributes; }

    /**
     * @param bool $blocking
     * @return void
     * @throws Exception
     */
    public function setBlocking($blocking) { }

    /**
     * @param int $seconds
     * @param int $microseconds
     * @return void
     * @throws Exception
     */
    public function setReadTimeout($seconds, $microseconds) { }

    /**
     * @param int $size
     * @return void
     * @throws Exception
     */
    public function setWriteBuffer($size) { }
}
