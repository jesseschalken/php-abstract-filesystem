<?php

namespace StreamWrapper2;

abstract class AbstractFileSystem {
    /**
     * @param string $path
     * @return \Iterator|null
     */
    public abstract function readDirectory($path);

    /**
     * @param string          $path
     * @param FilePermissions $mode
     * @param bool            $recursive
     * @return bool
     */
    public abstract function createDirectory($path, FilePermissions $mode, $recursive);

    /**
     * @param string $path1
     * @param string $path2
     * @return bool
     */
    public abstract function rename($path1, $path2);

    /**
     * @param string $path
     * @return bool
     */
    public abstract function removeDirectory($path);

    /**
     * @param string       $path
     * @param FileOpenMode $mode
     * @param bool         $useIncludePath
     * @param bool         $reportErrors
     * @param string       $openedPath
     * @return null|AbstractOpenFile
     */
    public abstract function openFile($path, FileOpenMode $mode, $useIncludePath, $reportErrors, &$openedPath);

    /**
     * @param string $path
     * @param int    $lastModified
     * @param int    $lastAccessed
     * @return bool
     */
    public abstract function setLastModified($path, $lastModified, $lastAccessed);

    /**
     * @param string $path
     * @param int    $userID
     * @return bool
     */
    public abstract function setUserByID($path, $userID);

    /**
     * @param string $path
     * @param string $userName
     * @return bool
     */
    public abstract function setUserByName($path, $userName);

    /**
     * @param string $path
     * @param int    $groupID
     * @return bool
     */
    public abstract function setGroupByID($path, $groupID);

    /**
     * @param string $path
     * @param string $groupName
     * @return bool
     */
    public abstract function setGroupByName($path, $groupName);

    /**
     * @param string          $path
     * @param FilePermissions $mode
     * @return bool
     */
    public abstract function setPermissions($path, FilePermissions $mode);

    /**
     * @param string $path
     * @param bool   $followLinks
     * @param bool   $reportErrors
     * @return null|AbstractFileAttributes
     */
    public abstract function getAttributes($path, $followLinks, $reportErrors);

    /**
     * @param string $path
     * @return bool
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
     * @return bool
     */
    public abstract function flushWrites();

    /**
     * @return bool
     */
    public abstract function close();

    /**
     * @param bool $exclusive
     * @param bool $noBlock
     * @return bool
     */
    public abstract function lock($exclusive, $noBlock);

    /**
     * @param bool $noBlock
     * @return bool
     */
    public abstract function unlock($noBlock);

    /**
     * @param int  $position
     * @param bool $fromEnd
     * @return bool
     */
    public abstract function setPosition($position, $fromEnd);

    /**
     * @param $position
     * @return bool
     */
    public abstract function addPosition($position);

    /**
     * @return int
     */
    public abstract function getPosition();

    /**
     * @param int $size
     * @return bool
     */
    public abstract function setSize($size);

    /**
     * @param string $data
     * @return int
     */
    public abstract function write($data);

    /**
     * @return AbstractFileAttributes|null
     */
    public abstract function getAttributes();

    /**
     * @param bool $blocking
     * @return bool
     */
    public abstract function setBlocking($blocking);

    /**
     * @param int $seconds
     * @param int $microseconds
     * @return bool
     */
    public abstract function setReadTimeout($seconds, $microseconds);

    /**
     * @param int $size
     * @return bool
     */
    public abstract function setWriteBuffer($size);
}

/**
 * Mutable class representing a file's permissions
 */
final class FilePermissions {
    /**
     * @param int $int
     * @return self
     */
    public static function fromInt($int) {
        $self        = new self;
        $self->perms = $int & 07777;
        return $self;
    }

    /** * @var int */
    private $perms = 0777;

    /** @return bool */
    public function getSetUID() { return $this->getBit(11); }
    /** @return bool */
    public function getSetGID() { return $this->getBit(10); }
    /** @return bool */
    public function getSticky() { return $this->getBit(9); }

    /** @return bool */
    public function getUserRead() { return $this->getBit(8); }
    /** @return bool */
    public function getUserWrite() { return $this->getBit(7); }
    /** @return bool */
    public function getUserExecute() { return $this->getBit(6); }

    /** @return bool */
    public function getGroupRead() { return $this->getBit(5); }
    /** @return bool */
    public function getGroupWrite() { return $this->getBit(4); }
    /** @return bool */
    public function getGroupExecute() { return $this->getBit(3); }

    /** @return bool */
    public function getOtherRead() { return $this->getBit(2); }
    /** @return bool */
    public function getOtherWrite() { return $this->getBit(1); }
    /** @return bool */
    public function getOtherExecute() { return $this->getBit(0); }

    /** @param bool $bool */
    public function setSetUID($bool) { $this->setBit(11, $bool); }
    /** @param bool $bool */
    public function setSetGID($bool) { $this->setBit(10, $bool); }
    /** @param bool $bool */
    public function setSticky($bool) { $this->setBit(9, $bool); }

    /** @param bool $bool */
    public function setUserRead($bool) { $this->setBit(8, $bool); }
    /** @param bool $bool */
    public function setUserWrite($bool) { $this->setBit(7, $bool); }
    /** @param bool $bool */
    public function setUserExecute($bool) { $this->setBit(6, $bool); }

    /** @param bool $bool */
    public function setGroupRead($bool) { $this->setBit(5, $bool); }
    /** @param bool $bool */
    public function setGroupWrite($bool) { $this->setBit(4, $bool); }
    /** @param bool $bool */
    public function setGroupExecute($bool) { $this->setBit(3, $bool); }

    /** @param bool $bool */
    public function setOtherRead($bool) { $this->setBit(2, $bool); }
    /** @param bool $bool */
    public function setOtherWrite($bool) { $this->setBit(1, $bool); }
    /** @param bool $bool */
    public function setOtherExecute($bool) { $this->setBit(0, $bool); }

    /** * @return int */
    public function toInt() { return $this->perms; }

    /**
     * @param int $bit
     * @return bool
     */
    private function getBit($bit) {
        return !!($this->perms & (1 << $bit));
    }

    /**
     * @param int  $bit
     * @param bool $bool
     */
    private function setBit($bit, $bool) {
        if ($bool)
            $this->perms |= 1 << $bit;
        else
            $this->perms &= ~(1 << $bit);
    }
}

final class FileType {
    const PIPE   = 001;
    const CHAR   = 002;
    const DIR    = 004;
    const BLOCK  = 006;
    const FILE   = 010;
    const LINK   = 012;
    const SOCKET = 014;
    const DOOR   = 015;

    private static $values = [
        self::PIPE,
        self::CHAR,
        self::DIR,
        self::BLOCK,
        self::FILE,
        self::LINK,
        self::SOCKET,
        self::DOOR,
    ];

    /** @var int */
    private $value;

    /**
     * @param int $value
     * @throws \Exception
     */
    function __construct($value) {
        if (!in_array($value, self::$values, true)) {
            throw new \Exception("'$value' must be one of: " . join(', ', self::$values));
        }
        $this->value = $value;
    }

    final function value() { return $this->value; }
    final function equals(self $that) { return $that->value === $this->value; }
}

abstract class AbstractFileAttributes {
    /** @return int The ID of the file (multiple directory entries can point to the same file) */
    public function getID() { return 0; }
    /** @return int The number of directory entries which refer to this file */
    public function getRefCount() { return 1; }

    /** @return int The ID of the device on which this file resides */
    public function getOuterDeviceID() { return 0; }
    /** @return int If this is a device file, the ID of the device to which it refers */
    public function getInnerDeviceID() { return 0; }

    /** @return FileType type of file */
    public function getType() { return new FileType(FileType::FILE); }
    /** @return FilePermissions permissions of file */
    public function getPermissions() { return new FilePermissions(); }

    /** @return int file size in bytes, or number of bytes in the contents of a symlink */
    public function getSize() { return 0; }

    /** @return int ID of owning user */
    public function getUserID() { return 0; }
    /** @return int ID of owning group */
    public function getGroupID() { return 0; }

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
}

abstract class FileOpenMode {
    /**
     * @param string $mode
     * @return self
     * @throws \Exception
     */
    final static function fromString($mode) {
        $rw   = strpos($mode, '+') !== false;
        $text = strpos($mode, 'b') === false;

        switch (str_replace($mode, ['+', 'b', 't'], '')) {
            case 'r':
                return new NoCreate($rw, $text);
            case 'w':
                return new CreateOrTruncate($rw, $text);
            case 'a':
                return new CreateOrAppend($rw, $text);
            case 'c':
                return new CreateOrKeep($rw, $text);
            case 'x':
                return new CreateOnly($rw, $text);
            default:
                throw new \Exception("Invalid fopen() mode: $mode");
        }
    }

    private $rw   = false;
    private $text = false;

    /**
     * @param bool $rw
     * @param bool $text
     */
    function __construct($rw, $text) {
        $this->rw   = $rw;
        $this->text = $text;
    }

    /**
     * Whether the stream should be readable
     * @return bool
     */
    function isReadable() { return $this->rw; }

    /**
     * Whether the stream should be writable
     * @return bool
     */
    function isWritable() { return $this->rw; }

    /**
     * Whether to open the file in text mode (true) or binary mode (false).
     * Generally only makes a difference on Windows.
     * @return bool
     */
    function isText() { return $this->text; }

    /**
     * Whether a new file should be created if it doesn't already exist (otherwise error).
     * @return bool
     */
    function createNew() { return false; }

    /**
     * Whether writes should always append, regardless of file position.
     * @return bool
     */
    function appendWrites() { return false; }

    /**
     * Whether an existing file should be truncated to 0 bytes. Only meaningful if useExisting() returns true.
     * @return bool
     */
    function truncateExisting() { return false; }

    /**
     * Whether an existing file should be used if it already exists (otherwise error).
     * @return bool
     */
    function useExisting() { return true; }

    function toString() {
        return ($this->text ? '' : 'b') . ($this->rw ? '+' : '');
    }
}

/**
 * Read an existing file and error if it doesn't exist
 */
final class NoCreate extends FileOpenMode {
    function toString() { return 'r' . parent::toString(); }
    function isReadable() { return true; }
    function createNew() { return false; }
}

/**
 * Create a new file and truncate one if it already exists
 */
final class CreateOrTruncate extends FileOpenMode {
    function toString() { return 'w' . parent::toString(); }
    function isWritable() { return true; }
    function truncateExisting() { return true; }
}

/**
 * Create a new file and append to one if it already exists.
 */
final class CreateOrAppend extends FileOpenMode {
    function toString() { return 'a' . parent::toString(); }
    function isWritable() { return true; }
    function appendWrites() { return true; }
}

/**
 * Create a new file and start writing from position 0 if it already exists.
 */
final class CreateOrKeep extends FileOpenMode {
    function toString() { return 'c' . parent::toString(); }
    function isWritable() { return true; }
}

/**
 * Only create a new file. Error if it already exists.
 */
final class CreateOnly extends FileOpenMode {
    function toString() { return 'x' . parent::toString(); }
    function isWritable() { return true; }
    function useExisting() { return false; }
}

