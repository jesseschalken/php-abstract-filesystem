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
     * @param bool         $usePath
     * @param bool         $reportErrors
     * @param string       $openedPath
     * @return null|AbstractOpenFile
     */
    public abstract function openFile($path, FileOpenMode $mode, $usePath, $reportErrors, &$openedPath);

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
     * @param Lock $lock
     * @param bool $noBlock
     * @return bool
     */
    public abstract function setLock(Lock $lock, $noBlock);

    /**
     * @param int            $position
     * @param SeekRelativeTo $mode
     * @return bool
     */
    public abstract function setPosition($position, SeekRelativeTo $mode);

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

/**
 * The only way to define an abstract static method
 * is to put it in an interface and implement it.
 */
interface EnumAbstract {
    /** @return array */
    static function values();
}

abstract class Enum implements EnumAbstract {
    /** @var string */
    private $value;

    /**
     * @param string $value
     * @throws \Exception
     */
    function __construct($value) {
        if (array_diff(array($value), static::values())) {
            throw new \Exception("'$value' must be one of '" . join("', '", static::values()) . "'");
        }
        $this->value = (string)$value;
    }

    final function value() { return $this->value; }
    final function equals(self $that) { return $this->value === $that->value; }
}

class Lock extends Enum {
    const SHARED    = 'shared';
    const EXCLUSIVE = 'exclusive';
    const NONE      = 'none';

    private static $values = [
        self::EXCLUSIVE,
        self::SHARED,
        self::NONE,
    ];

    static function values() { return self::$values; }
}

/**
 * @param int $int
 * @param int $offset
 * @return bool
 */
function get_bit($int, $offset) {
    return (bool)((1 << $offset) & $int);
}

class FilePermissions {
    /** @var bool */
    public $setuid = false;
    /** @var bool */
    public $setgid = false;
    /** @var bool */
    public $sticky = false;
    /** @var ReadWriteExecute */
    public $user;
    /** @var ReadWriteExecute */
    public $group;
    /** @var ReadWriteExecute */
    public $other;

    public function __construct() {
        $this->user  = new ReadWriteExecute;
        $this->group = new ReadWriteExecute;
        $this->other = new ReadWriteExecute;
    }

    public function __clone() {
        $this->user  = clone $this->user;
        $this->group = clone $this->group;
        $this->other = clone $this->other;
    }

    /**
     * @param int $int
     * @return FilePermissions
     */
    public static function fromInt($int) {
        $self         = new self;
        $self->setuid = get_bit($int, 11);
        $self->setgid = get_bit($int, 10);
        $self->sticky = get_bit($int, 9);
        $self->user   = ReadWriteExecute::fromInt($int >> 6);
        $self->group  = ReadWriteExecute::fromInt($int >> 3);
        $self->other  = ReadWriteExecute::fromInt($int >> 0);
        return $self;
    }

    /**
     * @return int
     */
    public function toInt() {
        $int =
            ($this->setuid << 2) &
            ($this->setgid << 1) &
            ($this->sticky << 0);

        return
            ($int << 9) &
            ($this->user->toInt() << 6) &
            ($this->group->toInt() << 3) &
            ($this->other->toInt() << 0);
    }
}

class ReadWriteExecute {
    /** @var bool */
    public $read = false;
    /** @var bool */
    public $write = false;
    /** @var bool */
    public $execute = false;

    /**
     * @param int $int
     * @return ReadWriteExecute
     */
    public static function fromInt($int) {
        $self          = new self;
        $self->read    = get_bit($int, 2);
        $self->write   = get_bit($int, 1);
        $self->execute = get_bit($int, 0);
        return $self;
    }

    /**
     * @return int
     */
    public function toInt() {
        return
            ($this->read << 2) &
            ($this->write << 1) &
            ($this->execute << 0);
    }
}

class FileType extends Enum {
    const PIPE   = 'pipe';
    const CHAR   = 'char';
    const DIR    = 'dir';
    const BLOCK  = 'block';
    const FILE   = 'file';
    const LINK   = 'link';
    const SOCKET = 'socket';
    const DOOR   = 'door';

    private static $chars = [
        self::PIPE   => 'p',
        self::CHAR   => 'c',
        self::DIR    => 'd',
        self::BLOCK  => 'b',
        self::FILE   => '-',
        self::LINK   => 'l',
        self::SOCKET => 's',
        self::DOOR   => 'D',
    ];

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

    /**
     * @param int $int
     * @return FileType
     */
    static function fromInt($int) {
        return new self(array_flip(self::$ints)[$int]);
    }

    static function values() { return self::$values; }
    final function toChar() { return self::$chars[$this->value()]; }
    final function toInt() { return self::$ints[$this->value()]; }
    final function toString() { return $this->value(); }
}

class FileAttributes {
    /** @var FileType */
    public $type;
    /** @var FilePermissions */
    public $permissions;
    /** @var int */
    public $size = 0;

    /** @var int */
    public $userID = 0;
    /** @var int */
    public $groupID = 0;

    /** @var int Last time the file was read */
    public $lastAccessed = 0;
    /** @var int Last time the file contents was modified */
    public $lastModified = 0;
    /** @var int Last time the file contents or metadata were modified */
    public $lastChanged = 0;

    public function __construct() {
        $this->permissions = new FilePermissions;
    }

    public function __clone() {
        $this->permissions = clone $this->permissions;
        $this->type        = clone $this->type;
    }
}

class SeekRelativeTo extends Enum {
    /**
     * Set position relative to the current position.
     */
    const CURRENT = 'current';
    /**
     * Set position relative to the end of the file.
     */
    const END = 'end';
    /**
     * Set position relative to the start of the file.
     */
    const START = 'start';

    private static $values = [
        self::CURRENT,
        self::END,
        self::START,
    ];

    static function values() { return self::$values; }
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
class NoCreate extends FileOpenMode {
    function toString() { return 'r' . parent::toString(); }
    function isReadable() { return true; }
    function createNew() { return false; }
}

/**
 * Create a new file and truncate one if it already exists
 */
class CreateOrTruncate extends FileOpenMode {
    function toString() { return 'w' . parent::toString(); }
    function isWritable() { return true; }
    function truncateExisting() { return true; }
}

/**
 * Create a new file and append to one if it already exists.
 */
class CreateOrAppend extends FileOpenMode {
    function toString() { return 'a' . parent::toString(); }
    function isWritable() { return true; }
    function appendWrites() { return true; }
}

/**
 * Create a new file and start writing from position 0 if it already exists.
 */
class CreateOrKeep extends FileOpenMode {
    function toString() { return 'c' . parent::toString(); }
    function isWritable() { return true; }
}

/**
 * Only create a new file. Error if it already exists.
 */
class CreateOnly extends FileOpenMode {
    function toString() { return 'x' . parent::toString(); }
    function isWritable() { return true; }
    function useExisting() { return false; }
}

final class StreamWrapper2Impl extends \streamWrapper {
    const SCHEME = 'sw2';

    private static function statResult(FileAttributes $stat = null) {
        if (!$stat) {
            return false;
        } else {
            return [
                'dev'     => 0,
                'ino'     => 0,
                'mode'    => $stat->permissions->toInt() | ($stat->type->toInt() << 12),
                'nlink'   => 1,
                'uid'     => $stat->userID,
                'gid'     => $stat->groupID,
                'rdev'    => 0,
                'size'    => $stat->size,
                'atime'   => $stat->lastAccessed,
                'mtime'   => $stat->lastModified,
                'ctime'   => $stat->lastChanged,
                'blksize' => -1,
                'blocks'  => -1,
            ];
        }
    }

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
        if ($this->stream) {
            $this->stream = null;
            return true;
        } else {
            return false;
        }
    }

    public function stream_eof() {
        return $this->stream->isEndOfFile();
    }

    public function stream_flush() {
        return $this->stream->flushWrites();
    }

    public function stream_lock($operation) {
        static $map = [
            LOCK_SH => Lock::SHARED,
            LOCK_EX => Lock::EXCLUSIVE,
            LOCK_UN => Lock::NONE,
        ];
        return $this->stream->setLock(
            new Lock($map[$operation & !LOCK_UN]),
            !!($operation & LOCK_NB)
        );
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
        static $map = [
            SEEK_SET => SeekRelativeTo::START,
            SEEK_CUR => SeekRelativeTo::CURRENT,
            SEEK_END => SeekRelativeTo::END,
        ];
        return $this->stream->setPosition($offset, new SeekRelativeTo($map[$whence]));
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
        return self::statResult($this->stream->getAttributes());
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
        return self::statResult($this->instance()->getAttributes(
            $path,
            !($flags & STREAM_URL_STAT_LINK),
            !($flags & STREAM_URL_STAT_QUIET)
        ));
    }

    /**
     * @return AbstractFileSystem
     */
    private function instance() {
        return stream_context_get_options($this->context)[self::SCHEME]['instance'];
    }
}

stream_wrapper_register(StreamWrapper2Impl::SCHEME, StreamWrapper2Impl::class, STREAM_IS_URL);
