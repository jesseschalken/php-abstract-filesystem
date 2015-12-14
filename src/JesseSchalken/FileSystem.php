<?php

namespace JesseSchalken\FileSystem;

use JesseSchalken\Enum\Enum;
use JesseSchalken\StreamWrapper\FileSystemStreamWrapper;
use JesseSchalken\StreamWrapper\StreamWrapper;

abstract class FileSystem {
    /**
     * Joins the given paths together like `"$path1/$path2"` but without adding a directory separator unnecessarily.
     * @param string $path1
     * @param string $path2
     * @return string
     */
    public final function joinPaths($path1, $path2) {
        foreach ($this->getValidDirectorySeparators() as $sep) {
            $len = strlen($sep);
            if (
                $len == 0 ||
                substr($path1, -$len) === $sep ||
                substr($path2, 0, $len) === $sep
            ) {
                return $path1 . $path2;
            }
        }

        return $path1 . $this->getDirectorySeparator() . $path2;
    }

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
     * @return OpenFile
     */
    public abstract function openFile($path, $writable);

    /**
     * x/x+ mode
     * @param string $path
     * @param bool   $readable
     * @return OpenFile
     */
    public abstract function createFile($path, $readable);

    /**
     * c/c+ mode
     * @param string $path
     * @param bool   $readable
     * @return OpenFile
     */
    public abstract function createOrOpenFile($path, $readable);

    /**
     * a/a+ mode
     * @param string $path
     * @param bool   $readable
     * @return OpenFile
     */
    public abstract function createOrAppendFile($path, $readable);

    /**
     * w/w+ mode
     * @param string $path
     * @param bool   $readable
     * @return OpenFile
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
     * @return FileAttributes
     */
    public abstract function getAttributes($path, $followLinks);

    /**
     * Remove the directory entry denoted by the given path.
     * @param string $path
     * @return void
     */
    public abstract function delete($path);

    /**
     * Get the path used to resolve relative paths. The returned path will be in canonical form, as if passed through
     * realPath().
     * @return string
     */
    public abstract function getCurrentDirectory();

    /**
     * Set the path used to resolve relative paths. Error if the given path does not exist or is not a directory.
     * @param string $path
     * @return void
     */
    public abstract function setCurrentDirectory($path);

    /**
     * Create a symbolic link.
     * @param string $linkPath
     * @param string $linkContents
     * @return void
     */
    public abstract function createLink($linkPath, $linkContents);

    /**
     * Read the given symbolic link.
     * @param string $linkPath
     * @return void
     */
    public abstract function readLink($linkPath);

    /**
     * Return the canonical path for the directory entry denoted by the given path. The canonical path should be
     * absolute and have all symbolic links, /./, /../ and // resolved.
     * @param string $path
     * @return string
     */
    public abstract function realPath($path);

    /**
     * ['/'] on Mac/Linux, ['/', '\\'] on Windows
     * @return string[]
     */
    public function getValidDirectorySeparators() { return [$this->getDirectorySeparator()]; }

    /**
     * '/' on Mac/Linux, '\\' on Windows
     * @return string
     */
    public function getDirectorySeparator() { return '/'; }

    /**
     * Get a stream wrapper for this filesystem. The caller must hold on to a reference to the returned object for
     * stream wrapper URLs generated by the stream wrapper to continue to work.
     * @return StreamWrapper
     */
    public function getStreamWrapper() {
        return new FileSystemStreamWrapper($this);
    }
}

abstract class OpenFile {
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
     * Set the current type of lock (none, shared or exclusive).
     * @param FileLock $lock
     * @return void
     */
    public abstract function setLock(FileLock $lock);

    /**
     * Same as setLock() but if the operation would block, return false instead.
     * @param FileLock $lock
     * @return bool
     */
    public abstract function setLockNoBlock(FileLock $lock);

    /**
     * @param int $position
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
     * @return FileAttributes
     * @throws Exception
     */
    public function getAttributes() { return new FileAttributes; }

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

class FileAttributes {
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

final class FileLock extends Enum {
    const SHARED    = LOCK_SH;
    const EXCLUSIVE = LOCK_EX;
    const NONE      = LOCK_UN;

    public static function values() {
        static $values = [
            self::NONE,
            self::EXCLUSIVE,
            self::SHARED,
        ];
        return $values;
    }
}

final class Bit {
    /**
     * @param int $int
     * @param int $bit
     * @return bool
     */
    public static function get($int, $bit) {
        return !!($int & (1 << $bit));
    }

    /**
     * @param int  $int
     * @param int  $bit
     * @param bool $bool
     */
    public static function set(&$int, $bit, $bool) {
        if ($bool)
            $int |= 1 << $bit;
        else
            $int &= ~(1 << $bit);
    }
}

/**
 * Exception to be thrown if any operation fails but the system is stable. This includes things like "permission
 * denied" and "file not found", for example.
 */
class Exception extends \RuntimeException {
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

    public function __clone() {
        $this->user  = clone $this->user;
        $this->group = clone $this->group;
        $this->other = clone $this->other;
    }

    /** @return FileUserPermissions */
    public function user() { return $this->user; }
    /** @return FileUserPermissions */
    public function group() { return $this->group; }
    /** @return FileUserPermissions */
    public function other() { return $this->other; }

    /** @return bool */
    public function getSetUID() { return Bit::get($this->perms, 2); }
    /** @return bool */
    public function getSetGID() { return Bit::get($this->perms, 1); }
    /** @return bool */
    public function getSticky() { return Bit::get($this->perms, 0); }

    /** @param bool $bool */
    public function setSetUID($bool) { Bit::set($this->perms, 2, $bool); }
    /** @param bool $bool */
    public function setSetGID($bool) { Bit::set($this->perms, 1, $bool); }
    /** @param bool $bool */
    public function setSticky($bool) { Bit::set($this->perms, 0, $bool); }

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
 * Mutable class for the permissions of a particular class of user (owning user, owning group, other)
 */
final class FileUserPermissions {
    /** @var int */
    private $perms = 07;

    /** @param int $perms */
    public function __construct($perms = 07) { $this->perms = $perms; }

    /** @param bool $bool */
    public function setRead($bool) { Bit::set($this->perms, 2, $bool); }
    /** @param bool $bool */
    public function setWrite($bool) { Bit::set($this->perms, 1, $bool); }
    /** @param bool $bool */
    public function setExecute($bool) { Bit::set($this->perms, 0, $bool); }

    /** @return bool */
    public function getRead() { Bit::get($this->perms, 2); }
    /** @return bool */
    public function getWrite() { Bit::get($this->perms, 1); }
    /** @return bool */
    public function getExecute() { Bit::get($this->perms, 0); }

    /** @return int */
    public function toInt() { return $this->perms; }
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

    public static function values() {
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

