<?php

namespace JesseSchalken\FileSystem;

final class StreamWrapperFileAttributes extends AbstractFileAttributes {
    private $array;
    public function __construct(array $array) { $this->array = $array; }
    public function getID() { return $this->array['ino']; }
    public function getRefCount() { return $this->array['nlink']; }
    public function getOuterDeviceID() { return $this->array['dev']; }
    public function getInnerDeviceID() { return $this->array['rdev']; }
    public function getType() { return new FileType(($this->array['mode'] >> 12) & 017); }
    public function getPermissions() { return new FilePermissions($this->array['mode'] & 07777); }
    public function getSize() { return $this->array['size']; }
    public function getUserID() { return $this->array['uid']; }
    public function getGroupID() { return $this->array['gid']; }
    public function getLastAccessed() { return $this->array['atime']; }
    public function getLastModified() { return $this->array['mtime']; }
    public function getLastChanged() { return $this->array['ctime']; }
    public function getBlockSize() { return $this->array['blksize']; }
    public function getBlocks() { return $this->array['blocks']; }
}


