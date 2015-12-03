<?php

namespace JesseSchalken\FileSystem;

class AbstractFileAttributes {
    public final function toArray() {
        return [
            'dev'     => $this->getOuterDeviceID(),
            'ino'     => $this->getID(),
            'mode'    => $this->getPermissions()->toInt() | ($this->getType()->value() << 12),
            'nlink'   => $this->getRefCount(),
            'uid'     => $this->getUserID(),
            'gid'     => $this->getGroupID(),
            'rdev'    => $this->getInnerDeviceID(),
            'size'    => $this->getSize(),
            'atime'   => $this->getLastAccessed(),
            'mtime'   => $this->getLastModified(),
            'ctime'   => $this->getLastChanged(),
            'blksize' => $this->getBlockSize(),
            'blocks'  => $this->getBlocks(),
        ];
    }
    /** @return int The ID of the device on which this file resides */
    public function getOuterDeviceID() { return 0; }
    /** @return int The ID of the file (multiple directory entries can point to the same file) */
    public function getID() { return 0; }
    /** @return FilePermissions permissions of file */
    public function getPermissions() { return new FilePermissions(); }
    /** @return FileType type of file */
    public function getType() { return new FileType(FileType::FILE); }
    /** @return int The number of directory entries which refer to this file */
    public function getRefCount() { return 1; }
    /** @return int ID of owning user */
    public function getUserID() { return 0; }
    /** @return int ID of owning group */
    public function getGroupID() { return 0; }
    /** @return int If this is a device file, the ID of the device to which it refers */
    public function getInnerDeviceID() { return 0; }
    /** @return int file size in bytes, or number of bytes in the contents of a symlink */
    public function getSize() { return 0; }
    /** @return int Last time the file was read */
    public function getLastAccessed() { return 0; }
    /** @return int Last time the file contents was modified */
    public function getLastModified() { return 0; }
    /** @return int Last time the file metadata was modified */
    public function getLastChanged() { return 0; }
    /** @return int The size of blocks on the file system */
    public function getBlockSize() { return -1; }
    /** @return int The number of blocks this file occupies */
    public function getBlocks() { return -1; }
}

