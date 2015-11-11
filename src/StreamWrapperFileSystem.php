<?php

namespace StreamWrapper2;

abstract class StreamWrapperFileSystem extends AbstractFileSystem {
    public final function readDirectory($path) {
        return new StreamWrapperOpenDir($this->url($path), $this->ctx());
    }

    public final function createDirectory($path, FilePermissions $mode, $recursive) {
        return mkdir($this->url($path), $mode->toInt(), $recursive, $this->ctx());
    }

    public final function rename($path1, $path2) {
        return rename($this->url($path1), $this->url($path2), $this->ctx());
    }

    public final function removeDirectory($path) {
        return rmdir($this->url($path), $this->ctx());
    }

    public final function openFile($path, FileOpenMode $mode, $useIncludePath, $reportErrors, &$openedPath) {
        $url  = $this->url($path);
        $mode = $mode->toString();
        $ctx  = $this->ctx();

        if ($reportErrors) {
            $resource = fopen($url, $mode, $useIncludePath, $ctx);
        } else {
            set_error_handler(function () { });
            $resource = fopen($url, $mode, $useIncludePath, $ctx);
            restore_error_handler();
        }

        return new StreamWrapperOpenFile($resource);
    }

    public final function setLastModified($path, $lastModified, $lastAccessed) {
        return touch($this->url($path), $lastModified, $lastAccessed);
    }

    public final function setUserByID($path, $userID) {
        return chown($this->url($path), (int)$userID);
    }

    public final function setUserByName($path, $userName) {
        return chown($this->url($path), (string)$userName);
    }

    public final function setGroupByID($path, $groupID) {
        return chgrp($this->url($path), (int)$groupID);
    }

    public final function setGroupByName($path, $groupName) {
        return chgrp($this->url($path), (string)$groupName);
    }

    public final function setPermissions($path, FilePermissions $mode) {
        return chmod($this->url($path), $mode->toInt());
    }

    public final function getAttributes($path, $followLinks, $reportErrors) {
        $url = $this->url($path);

        if ($reportErrors) {
            $stat = $followLinks ? stat($url) : lstat($url);
        } else {
            set_error_handler(function () { });
            $stat = $followLinks ? stat($url) : lstat($url);
            restore_error_handler();
        }

        return $stat ? new StreamWrapperFileAttributes($stat) : null;
    }

    public final function delete($path) {
        return unlink($this->url($path));
    }

    /**
     * @return resource|null
     */
    protected function ctx() { return null; }

    /**
     * @param string $path
     * @return string
     */
    abstract protected function url($path);
}

final class StreamWrapperOpenFile extends AbstractOpenFile {
    /** @var resource */
    private $handle;

    /**
     * @param resource $resource
     */
    public function __construct($resource) {
        $this->handle = $resource;
    }

    public function close() {
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

    public function flushWrites() {
        return fflush($this->handle);
    }

    public function lock($exclusive, $noBlock) {
        $op = $exclusive ? LOCK_EX : LOCK_SH;
        if ($noBlock)
            $op |= LOCK_NB;
        return flock($this->handle, $op);
    }

    public function unlock($noBlock) {
        $op = LOCK_UN;
        if ($noBlock)
            $op |= LOCK_NB;
        return flock($this->handle, $op);
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
        return $stat ? new StreamWrapperFileAttributes($stat) : null;
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

final class StreamWrapperOpenDir implements \Iterator {
    private $key = 0;
    private $handle;
    private $current;

    public function __construct($path, $context) {
        $this->handle = opendir($path, $context);
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

final class StreamWrapperFileAttributes extends AbstractFileAttributes {
    private $array;
    public function __construct(array $array) { $this->array = $array; }
    public function getID() { return $this->array['ino']; }
    public function getRefCount() { return $this->array['nlink']; }
    public function getOuterDeviceID() { return $this->array['dev']; }
    public function getInnerDeviceID() { return $this->array['rdev']; }
    public function getType() { return new FileType(($this->array['mode'] >> 12) & 017); }
    public function getPermissions() { return FilePermissions::fromInt($this->array['mode'] & 07777); }
    public function getSize() { return $this->array['size']; }
    public function getUserID() { return $this->array['uid']; }
    public function getGroupID() { return $this->array['gid']; }
    public function getLastAccessed() { return $this->array['atime']; }
    public function getLastModified() { return $this->array['mtime']; }
    public function getLastChanged() { return $this->array['ctime']; }
    public function getBlockSize() { return $this->array['blksize']; }
    public function getBlocks() { return $this->array['blocks']; }
}


