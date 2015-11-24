<?php

abstract class AbstractFileSystem {
    /**
     * @param string $path
     * @return \Iterator
     */
    public abstract function readDirectory($path);

    /**
     * @param string          $path
     * @param FilePermissions $mode
     * @param bool            $recursive
     * @return void
     */
    public abstract function createDirectory($path, FilePermissions $mode, $recursive);

    /**
     * @param string $path1
     * @param string $path2
     * @return void
     */
    public abstract function rename($path1, $path2);

    /**
     * @param string $path
     * @return void
     */
    public abstract function removeDirectory($path);

    /**
     * r/r+ mode
     * @param string $path
     * @param bool   $writable
     * @return AbstractOpenFile
     */
    public abstract function openFile($path, $writable);

    /**
     * x/x+ mode
     * @param string $path
     * @param bool   $readable
     * @return AbstractOpenFile
     */
    public abstract function createFile($path, $readable);

    /**
     * c/c+ mode
     * @param string $path
     * @param bool   $readable
     * @return AbstractOpenFile
     */
    public abstract function createOrOpenFile($path, $readable);

    /**
     * a/a+ mode
     * @param string $path
     * @param bool   $readable
     * @return AbstractOpenFile
     */
    public abstract function createOrAppendFile($path, $readable);

    /**
     * w/w+ mode
     * @param string $path
     * @param bool   $readable
     * @return AbstractOpenFile
     */
    public abstract function createOrTruncateFile($path, $readable);

    /**
     * @param string $path
     * @param int    $lastModified
     * @param int    $lastAccessed
     * @return void
     */
    public abstract function setLastModified($path, $lastModified, $lastAccessed);

    /**
     * @param string $path
     * @param int    $userID
     * @return void
     */
    public abstract function setUserByID($path, $userID);

    /**
     * @param string $path
     * @param string $userName
     * @return void
     */
    public abstract function setUserByName($path, $userName);

    /**
     * @param string $path
     * @param int    $groupID
     * @return void
     */
    public abstract function setGroupByID($path, $groupID);

    /**
     * @param string $path
     * @param string $groupName
     * @return void
     */
    public abstract function setGroupByName($path, $groupName);

    /**
     * @param string          $path
     * @param FilePermissions $mode
     * @return void
     */
    public abstract function setPermissions($path, FilePermissions $mode);

    /**
     * @param string $path
     * @param bool   $followLinks
     * @return AbstractFileAttributes
     */
    public abstract function getAttributes($path, $followLinks);

    /**
     * @param string $path
     * @return void
     */
    public abstract function delete($path);
}

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
    public abstract function isEOF();

    /**
     * @return void
     * @throws FileSystemException
     */
    public abstract function flush();

    /**
     * @param Lock $lock
     * @return void
     */
    public abstract function setLock(Lock $lock);

    /**
     * @param Lock $lock
     * @return bool
     */
    public abstract function setLockNoBlock(Lock $lock);

    /**
     * @param $position
     * @return void
     * @throws FileSystemException
     */
    public abstract function addPosition($position);
    /**
     * @param int  $position
     * @param bool $fromEnd
     * @return void
     * @throws FileSystemException
     */
    public abstract function setPosition($position, $fromEnd);

    /**
     * @return int
     */
    public abstract function getPosition();

    /**
     * @param int $size
     * @return void
     * @throws FileSystemException
     */
    public abstract function setSize($size);

    /**
     * @param string $data
     * @return int
     */
    public abstract function write($data);

    /**
     * @return AbstractFileAttributes
     * @throws FileSystemException
     */
    public function getAttributes() { return new AbstractFileAttributes; }

    /**
     * @param bool $blocking
     * @return void
     * @throws FileSystemException
     */
    public function setBlocking($blocking) { }

    /**
     * @param int $seconds
     * @param int $microseconds
     * @return void
     * @throws FileSystemException
     */
    public function setReadTimeout($seconds, $microseconds) { }

    /**
     * @param int $size
     * @return void
     * @throws FileSystemException
     */
    public function setWriteBuffer($size) { }
}

/**
 * @param int $int
 * @param int $bit
 * @return bool
 */
function get_bit($int, $bit) {
    return !!($int & (1 << $bit));
}

/**
 * @param int  $int
 * @param int  $bit
 * @param bool $bool
 */
function set_bit(&$int, $bit, $bool) {
    if ($bool)
        $int |= 1 << $bit;
    else
        $int &= ~(1 << $bit);
}

/**
 * Mutable class representing a file's permissions
 */
final class FilePermissions {
    /** * @var int */
    private $perms = 0;
    /** @var FileUserPermissions */
    private $user;
    /** @var FileUserPermissions */
    private $group;
    /** @var FileUserPermissions */
    private $other;

    /** @param int $int */
    public function __construct($int = 00777) {
        $this->perms = ($int >> 9) & 07;
        $this->user  = new FileUserPermissions($int >> 6);
        $this->group = new FileUserPermissions($int >> 3);
        $this->other = new FileUserPermissions($int >> 0);
    }

    /** @return FileUserPermissions */
    public function user() { return $this->user; }
    /** @return FileUserPermissions */
    public function group() { return $this->group; }
    /** @return FileUserPermissions */
    public function other() { return $this->other; }

    /** @return bool */
    public function getSetUID() { return get_bit($this->perms, 2); }
    /** @return bool */
    public function getSetGID() { return get_bit($this->perms, 1); }
    /** @return bool */
    public function getSticky() { return get_bit($this->perms, 0); }

    /** @param bool $bool */
    public function setSetUID($bool) { set_bit($this->perms, 2, $bool); }
    /** @param bool $bool */
    public function setSetGID($bool) { set_bit($this->perms, 1, $bool); }
    /** @param bool $bool */
    public function setSticky($bool) { set_bit($this->perms, 0, $bool); }

    /** * @return int */
    public function toInt() {
        $int = $this->perms << 9;
        $int |= $this->user->toInt() << 6;
        $int |= $this->group->toInt() << 3;
        $int |= $this->other->toInt() << 0;
        return $int;
    }
}

/**
 * Mutable class for the permissions of a particular class of user
 */
final class FileUserPermissions {
    /** @var int */
    private $perms = 07;
    /** @param int $perms */
    public function __construct($perms = 07) { $this->perms = $perms; }
    /** @param bool $bool */
    public function setRead($bool) { set_bit($this->perms, 2, $bool); }
    /** @param bool $bool */
    public function setWrite($bool) { set_bit($this->perms, 1, $bool); }
    /** @param bool $bool */
    public function setExecute($bool) { set_bit($this->perms, 0, $bool); }
    /** @return bool */
    public function getRead() { get_bit($this->perms, 2); }
    /** @return bool */
    public function getWrite() { get_bit($this->perms, 1); }
    /** @return bool */
    public function getExecute() { get_bit($this->perms, 0); }
    /** @return int */
    public function toInt() { return $this->perms; }
}

final class Lock extends Enum {
    const SHARED    = LOCK_SH;
    const EXCLUSIVE = LOCK_EX;
    const NONE      = LOCK_UN;

    static function values() {
        static $values = [
            self::NONE,
            self::EXCLUSIVE,
            self::SHARED,
        ];
        return $values;
    }
}

final class FileType extends Enum {
    const PIPE   = 001;
    const CHAR   = 002;
    const DIR    = 004;
    const BLOCK  = 006;
    const FILE   = 010;
    const LINK   = 012;
    const SOCKET = 014;
    const DOOR   = 015;

    static function values() {
        static $values = [
            self::PIPE,
            self::CHAR,
            self::DIR,
            self::BLOCK,
            self::FILE,
            self::LINK,
            self::SOCKET,
            self::DOOR,
        ];
        return $values;
    }
}

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

/**
 * Exception to be thrown if any operation fails
 */
class FileSystemException extends \RuntimeException {
}

