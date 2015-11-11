<?php

namespace StreamWrapper2;

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
     * @return null|FileAttributes
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
    public abstract function isEndOfFile();

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
     * @return FileAttributes|null
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
    public $perms = 0777;

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
            $this->perms &= 1 << $bit;
    }
}

final class FileType {
    const PIPE   = 'pipe';
    const CHAR   = 'char';
    const DIR    = 'dir';
    const BLOCK  = 'block';
    const FILE   = 'file';
    const LINK   = 'link';
    const SOCKET = 'socket';
    const DOOR   = 'door';

    private static $ints = [
        self::PIPE   => 001,
        self::CHAR   => 002,
        self::DIR    => 004,
        self::BLOCK  => 006,
        self::FILE   => 010,
        self::LINK   => 012,
        self::SOCKET => 014,
        self::DOOR   => 015,
    ];

    /**
     * @param int $int
     * @return self
     */
    static function fromInt($int) {
        $map = array_flip(self::$ints);
        return new self($map[$int]);
    }

    /** @var string */
    private $value;

    function __construct($value) {
        $value = (string)$value;
        if (!array_key_exists($value, self::$ints)) {
            throw new \Exception("'$value' must be one of: " . join(', ', array_keys(self::$ints)));
        }
        $this->value = $value;
    }

    final function toInt() { return self::$ints[$this->value]; }
    final function toString() { return $this->value; }
    final function equals(self $that) { return $that->value === $this->value; }
}

final class FileAttributes {
    static function fromArray(array $array) {
        $self               = new self;
        $self->groupID      = $array['gid'];
        $self->userID       = $array['uid'];
        $self->type         = FileType::fromInt($array['mode'] >> 12);
        $self->permissions  = FilePermissions::fromInt($array['mode']);
        $self->size         = $array['size'];
        $self->lastAccessed = $array['atime'];
        $self->lastModified = $array['mtime'];
        $self->lastChanged  = $array['ctime'];
        return $self;
    }

    /** @var int The "inode number" */
    public $id = 0;
    /** @var int The ID of the device on which this file resides */
    public $outerDeviceID = 0;
    /** @var int If this is a device file, the ID of the device to which it refers */
    public $innerDeviceID = 0;
    /** @var int The number of directory entries which refer to this file */
    public $links = 1;

    /** @var FileType type of file */
    public $type;
    /** @var FilePermissions permissions of file */
    public $permissions;
    /** @var int file size in bytes, or number of bytes in the contents of a symlink */
    public $size = 0;

    /** @var int ID of owning user */
    public $userID = 0;
    /** @var int ID of owning group */
    public $groupID = 0;

    /** @var int Last time the file was read */
    public $lastAccessed = 0;
    /** @var int Last time the file contents was modified */
    public $lastModified = 0;
    /** @var int Last time the file metadata was modified */
    public $lastChanged = 0;

    /** @var int The size of blocks on the file system */
    public $blockSize = -1;
    /** @var int The number of blocks this file occupies */
    public $blocks = -1;

    public function __construct() {
        $this->permissions = new FilePermissions;
        $this->type        = new FileType(FileType::FILE);
    }

    public function __clone() {
        $this->permissions = clone $this->permissions;
        $this->type        = clone $this->type;
    }

    public function toArray() {
        return [
            'dev'     => $this->outerDeviceID,
            'ino'     => $this->id,
            'mode'    => $this->permissions->toInt() | ($this->type->toInt() << 12),
            'nlink'   => $this->links,
            'uid'     => $this->userID,
            'gid'     => $this->groupID,
            'rdev'    => $this->innerDeviceID,
            'size'    => $this->size,
            'atime'   => $this->lastAccessed,
            'mtime'   => $this->lastModified,
            'ctime'   => $this->lastChanged,
            'blksize' => $this->blockSize,
            'blocks'  => $this->blocks,
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
     * Whether to a new file should be created if it doesn't already exist (otherwise error).
     * @return bool
     */
    function createNew() { return false; }

    /**
     * Whether writes should always append, regardless of file position.
     * @return bool
     */
    function appendWrites() { return false; }

    /**
     * Whether an existing file should be truncated to 0 bytes.
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

final class StreamWrapper2Impl extends \streamWrapper {
    const SCHEME = 'sw2';

    /** @var \Iterator|null */
    private $dir;
    /** @var AbstractOpenFile|null */
    private $stream;

    public function __construct() {
    }

    public function __destruct() {
    }

    public function dir_closedir() {
        if ($this->dir) {
            $this->dir = null;
            return true;
        } else {
            return false;
        }
    }

    public function dir_opendir($path, $options) {
        $this->dir = $this->instance()->readDirectory($path);
        return !!$this->dir;
    }

    public function dir_readdir() {
        if ($this->dir->valid()) {
            $result = $this->dir->current();
            $this->dir->next();
            return $result;
        } else {
            return false;
        }
    }

    public function dir_rewinddir() {
        $this->dir->rewind();
        return true;
    }

    public function mkdir($path, $mode, $options) {
        return $this->instance()->createDirectory($path, FilePermissions::fromInt($mode), (bool)($options & STREAM_MKDIR_RECURSIVE));
    }

    public function rename($path_from, $path_to) {
        return $this->instance()->rename($path_from, $path_to);
    }

    public function rmdir($path, $options) {
        return $this->instance()->removeDirectory($path);
    }

    public function stream_cast($cast_as) {
        return $this->stream->toResource();
    }

    public function stream_close() {
        return $this->stream->close();
    }

    public function stream_eof() {
        return $this->stream->isEndOfFile();
    }

    public function stream_flush() {
        return $this->stream->flushWrites();
    }

    public function stream_lock($operation) {
        $noBlock = !!($operation & LOCK_NB);
        switch ($operation & ~LOCK_UN) {
            case LOCK_SH:
                return $this->stream->lock(false, $noBlock);
            case LOCK_EX:
                return $this->stream->lock(true, $noBlock);
            case LOCK_UN:
                return $this->stream->unlock($noBlock);
            default:
                return false;
        }
    }

    public function stream_metadata($path, $option, $value) {
        $instance = $this->instance();
        switch ($option) {
            case STREAM_META_TOUCH:
                return $instance->setLastModified($path, $value[0], $value[1]);
            case STREAM_META_OWNER:
                return $instance->setUserByID($path, $value);
            case STREAM_META_OWNER_NAME:
                return $instance->setUserByName($path, $value);
            case STREAM_META_GROUP:
                return $instance->setGroupByID($path, $value);
            case STREAM_META_GROUP_NAME:
                return $instance->setGroupByName($path, $value);
            case STREAM_META_ACCESS:
                return $instance->setPermissions($path, FilePermissions::fromInt($value));
            default:
                return false;
        }
    }

    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->stream = $this->instance()->openFile(
            $path,
            FileOpenMode::fromString($mode),
            !!($options & STREAM_USE_PATH),
            !!($options & STREAM_REPORT_ERRORS),
            $opened_path
        );
        return !!$this->stream;
    }

    public function stream_read($count) {
        return $this->stream->read($count);
    }

    public function stream_seek($offset, $whence = SEEK_SET) {
        switch ($whence) {
            case SEEK_SET:
                return $this->stream->setPosition($offset, false);
            case SEEK_END:
                return $this->stream->setPosition($offset, true);
            case SEEK_CUR:
                return $this->stream->addPosition($offset);
            default:
                return false;
        }
    }

    public function stream_set_option($option, $arg1, $arg2) {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return $this->stream->setBlocking(!!$arg1);
            case STREAM_OPTION_READ_TIMEOUT:
                return $this->stream->setReadTimeout($arg1, $arg2);
            case STREAM_OPTION_WRITE_BUFFER:
                switch ($arg1) {
                    case STREAM_BUFFER_NONE:
                        return $this->stream->setWriteBuffer(0);
                    case STREAM_BUFFER_FULL:
                        return $this->stream->setWriteBuffer($arg2);
                    default:
                        return false;
                }
            default:
                return false;
        }
    }

    public function stream_stat() {
        $stat = $this->stream->getAttributes();
        return $stat ? $stat->toArray() : false;
    }

    public function stream_tell() {
        return $this->stream->getPosition();
    }

    public function stream_truncate($new_size) {
        return $this->stream->setSize($new_size);
    }

    public function stream_write($data) {
        return $this->stream->write($data);
    }

    public function unlink($path) {
        $this->instance()->delete($path);
    }

    public function url_stat($path, $flags) {
        $stat = $this->instance()->getAttributes(
            $path,
            !($flags & STREAM_URL_STAT_LINK),
            !($flags & STREAM_URL_STAT_QUIET)
        );
        return $stat ? $stat->toArray() : false;
    }

    /**
     * @return AbstractFileSystem
     */
    private function instance() {
        return stream_context_get_options($this->context)[self::SCHEME]['instance'];
    }
}

stream_wrapper_register(StreamWrapper2Impl::SCHEME, StreamWrapper2Impl::class, STREAM_IS_URL);
