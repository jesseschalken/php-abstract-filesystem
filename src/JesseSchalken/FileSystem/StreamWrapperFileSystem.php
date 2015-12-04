<?php

namespace JesseSchalken\FileSystem;

final class StreamWrapperFileSystem extends AbstractFileSystem {
    private $sw;

    public function __construct(AbstractStreamWrapper $sw) {
        $this->sw = $sw;
    }

    public final function readDirectory($path) {
        $handle = opendir($this->sw->getUrl($path), $this->sw->getContext($path));
        return $handle === false ? null : new StreamWrapperOpenDir($handle);
    }

    public final function createDirectory($path, FilePermissions $mode, $recursive) {
        return mkdir($this->sw->getUrl($path), $mode->toInt(), $recursive, $this->sw->getContext($path));
    }

    public final function rename($path1, $path2) {
        return rename($this->sw->getUrl($path1), $this->sw->getUrl($path2), $this->sw->getContext($path1));
    }

    public final function removeDirectory($path) {
        return rmdir($this->sw->getUrl($path), $this->sw->getContext($path));
    }

    public function createFile($path, $readable) {
        return $this->_openFile($path, $readable ? 'x+' : 'x');
    }

    public function createOrOpenFile($path, $readable) {
        return $this->_openFile($path, $readable ? 'c+' : 'c');
    }

    public function createOrAppendFile($path, $readable) {
        return $this->_openFile($path, $readable ? 'a+' : 'a');
    }

    public function createOrTruncateFile($path, $readable) {
        return $this->_openFile($path, $readable ? 'w+' : 'w');
    }

    public function openFile($path, $writable) {
        return $this->_openFile($path, $writable ? 'r+' : 'r');
    }

    private function _openFile($path, $mode) {
        $url = $this->sw->getUrl($path);
        $ctx = $this->sw->getContext($path);

        return new StreamWrapperOpenFile(fopen($url, $mode . 'b', null, $ctx));
    }

    public final function setLastModified($path, $lastModified, $lastAccessed) {
        return touch($this->sw->getUrl($path), $lastModified, $lastAccessed);
    }

    public final function setUserByID($path, $userID) {
        return chown($this->sw->getUrl($path), (int)$userID);
    }

    public final function setUserByName($path, $userName) {
        return chown($this->sw->getUrl($path), (string)$userName);
    }

    public final function setGroupByID($path, $groupID) {
        return chgrp($this->sw->getUrl($path), (int)$groupID);
    }

    public final function setGroupByName($path, $groupName) {
        return chgrp($this->sw->getUrl($path), (string)$groupName);
    }

    public final function setPermissions($path, FilePermissions $mode) {
        return chmod($this->sw->getUrl($path), $mode->toInt());
    }

    public final function getAttributes($path, $followLinks) {
        $url  = $this->sw->getUrl($path);
        $stat = $followLinks ? stat($url) : lstat($url);

        return $stat ? new StreamWrapperFileAttributes($stat) : null;
    }

    public final function delete($path) {
        return unlink($this->sw->getUrl($path));
    }

    public function getStreamWrapper() {
        return $this->sw;
    }
}
