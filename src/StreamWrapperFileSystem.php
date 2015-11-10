<?php

namespace StreamWrapper2;

abstract class StreamWrapperFileSystem extends AbstractFileSystem {
    public final function readDirectory($path) {
        $flags = 0;
        if (defined('SCANDIR_SORT_NONE'))
            $flags |= SCANDIR_SORT_NONE;
        return new \ArrayIterator(scandir($this->url($path), $flags, $this->ctx()));
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

    public final function openFile($path, FileOpenMode $mode, $usePath, $reportErrors, &$openedPath) {

        if (!$reportErrors)
            set_error_handler(function () { });

        $result = new StreamWrapperOpenFile(fopen($this->url($path), $mode->toString(), $usePath, $this->ctx()));

        if (!$reportErrors)
            restore_error_handler();

        return $result;
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

        if (!$reportErrors)
            set_error_handler(function () { });

        $stat = $followLinks ? stat($url) : lstat($url);

        if (!$reportErrors)
            restore_error_handler();

        if (!$stat)
            return null;

        $attrs              = new FileAttributes;
        $attrs->groupID     = $stat['gid'];
        $attrs->userID      = $stat['uid'];
        $attrs->type        = FileType::fromInt($stat['mode'] >> 12);
        $attrs->permissions = FilePermissions::fromInt($stat['mode']);
        $attrs->size        = $stat['size'];

        $attrs->lastAccessed = $stat['atime'];
        $attrs->lastModified = $stat['mtime'];
        $attrs->lastChanged  = $stat['ctime'];

        return $attrs;
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

    public function __destruct() {
        if ($this->handle)
            fclose($this->handle);
    }

    public function read($count) {
        return fread($this->handle, $count);
    }

    public function toResource() {
        return $this->handle;
    }

    public function isEndOfFile() {
        return feof($this->handle);
    }

    public function flushWrites() {
        return fflush($this->handle);
    }

    public function setLock(Lock $lock, $noBlock) {
        static $map = [
            Lock::EXCLUSIVE => LOCK_EX,
            Lock::NONE      => LOCK_UN,
            Lock::SHARED    => LOCK_SH,
        ];
        $op = $map[$lock->value()];
        if ($noBlock)
            $op |= LOCK_NB;
        return flock($this->handle, $op);
    }

    public function setPosition($position, SeekRelativeTo $mode) {
        static $map = [
            SeekRelativeTo::CURRENT => SEEK_CUR,
            SeekRelativeTo::END     => SEEK_END,
            SeekRelativeTo::START   => SEEK_SET,
        ];
        return fseek($this->handle, $position, $map[$mode->value()]);
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
        return fstat($this->handle);
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