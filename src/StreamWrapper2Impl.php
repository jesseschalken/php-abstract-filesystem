<?php

namespace StreamWrapper2;

interface StreamWrapper2 {
    /**
     * @param string $path
     * @return \Iterator
     */
    public function readDirectory($path);

    /**
     * @param string   $path
     * @param FileMode $mode
     * @param bool     $recursive
     * @return bool
     */
    public function createDirectory($path, FileMode $mode, $recursive);

    /**
     * @param string $path1
     * @param string $path2
     * @return bool
     */
    public function rename($path1, $path2);

    /**
     * @param string $path
     * @return bool
     */
    public function removeDirectory($path);

    /**
     * @param string       $path
     * @param FileOpenMode $mode
     * @param bool         $read
     * @param bool         $write
     * @return null|OpenFile
     */
    public function openFile($path, FileOpenMode $mode, $read, $write);

    /**
     * @param string $path
     * @param int    $lastModified
     * @param int    $lastAccessed
     * @return bool
     */
    public function setLastModified($path, $lastModified, $lastAccessed);

    /**
     * @param string $path
     * @param int    $userID
     * @return bool
     */
    public function setUserByID($path, $userID);

    /**
     * @param string $path
     * @param string $userName
     * @return bool
     */
    public function setUserByName($path, $userName);

    /**
     * @param string $path
     * @param int    $groupID
     * @return bool
     */
    public function setGroupByID($path, $groupID);

    /**
     * @param string $path
     * @param string $groupName
     * @return bool
     */
    public function setGroupByName($path, $groupName);

    /**
     * @param string   $path
     * @param FileMode $mode
     * @return bool
     */
    public function setPermissions($path, FileMode $mode);

    /**
     * @param string $path
     * @param bool   $followLinks
     * @return null|FileAttributes
     */
    public function getAttributes($path, $followLinks);

    /**
     * @param string $path
     * @return bool
     */
    public function delete($path);
}

interface OpenFile {
    /**
     * @param int $count
     * @return string
     */
    public function read($count);

    /**
     * @return resource|null
     */
    public function toResource();

    /**
     * @return bool
     */
    public function isEOF();

    /**
     * @return void
     */
    public function close();

    /**
     * @return bool
     */
    public function flush();

    /**
     * @param Lock $lock
     * @param bool $block
     * @return bool
     */
    public function setLock(Lock $lock, $block);

    /**
     * @param int      $position
     * @param SeekType $mode
     * @return bool
     */
    public function setPosition($position, SeekType $mode);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $size
     * @return bool
     */
    public function setSize($size);

    /**
     * @param string $data
     * @return int
     */
    public function write($data);

    /**
     * @return FileAttributes|null
     */
    public function getAttributes();

    /**
     * @param bool $blocking
     * @return bool
     */
    public function setBlocking($blocking);

    /**
     * @param int $microseconds
     * @return bool
     */
    public function setReadTimeout($microseconds);

    /**
     * @param int $size
     * @return bool
     */
    public function setWriteBuffer($size);
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

    final function toString() { return $this->value; }
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

class FileMode {
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
     * @return self
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
    final function toChar() { return self::$chars[$this->toString()]; }
    final function toInt() { return self::$ints[$this->toString()]; }
}

class FileAttributes {
    /** @var FileType */
    public $type;
    /** @var FileMode */
    public $mode;
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
        $this->mode = new FileMode;
    }

    public function __clone() {
        $this->mode = clone $this->mode;
        $this->type = clone $this->type;
    }
}

class SeekType extends Enum {
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

class FileOpenMode extends Enum {
    /**
     * Read an existing file and error if it doesn't exist
     */
    const NO_CREATE = 'r';

    /**
     * Create a new file and truncate one if it already exists
     */
    const CREATE_OR_TRUNCATE = 'w';

    /**
     * Create a new file and append to one if it already exists.
     *
     * !! Important: Under this mode, all writes append to the file
     * regardless of the position later set with fseek().
     */
    const CREATE_OR_APPEND = 'a';

    /**
     * Create a new file and start writing from position 0 if it already exists.
     */
    const CREATE_OR_KEEP = 'c';

    /**
     * Only create a new file. Error if it already exists.
     */
    const CREATE_ONLY = 'x';

    private static $values = [
        self::NO_CREATE,
        self::CREATE_OR_TRUNCATE,
        self::CREATE_OR_APPEND,
        self::CREATE_OR_KEEP,
        self::CREATE_ONLY,
    ];

    static function values() { return self::$values; }
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
                'mode'    => $stat->mode->toInt() | ($stat->type->toInt() << 12),
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
    /** @var OpenFile|null */
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
        return $this->instance()->createDirectory($path, FileMode::fromInt($mode), (bool)($options & STREAM_MKDIR_RECURSIVE));
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
        $this->stream->close();
    }

    public function stream_eof() {
        return $this->stream->isEOF();
    }

    public function stream_flush() {
        return $this->stream->flush();
    }

    public function stream_lock($operation) {
        static $map = [
            LOCK_SH => Lock::SHARED,
            LOCK_EX => Lock::EXCLUSIVE,
            LOCK_UN => Lock::NONE,
        ];
        return $this->stream->setLock(
            new Lock($map[$operation & !LOCK_UN]),
            !($operation & LOCK_NB)
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
                return $instance->setPermissions($path, FileMode::fromInt($value));
            default:
                return false;
        }
    }

    public function stream_open($path, $mode, $options, &$opened_path) {
        if ($options & STREAM_USE_PATH) {
            throw new \Exception('STREAM_USE_PATH is not supported');
        }

        $mode_ = new FileOpenMode(str_replace(['+', 'b', 't'], '', $mode));

        $this->stream = $this->instance()->openFile(
            $path,
            $mode_,
            $mode_->toString() === 'r' || strpos($mode, '+') !== false,
            $mode_->toString() !== 'r' || strpos($mode, '+') !== false
        );
        return !!$this->stream;
    }

    public function stream_read($count) {
        return $this->stream->read($count);
    }

    public function stream_seek($offset, $whence = SEEK_SET) {
        static $map = [
            SEEK_SET => SeekType::START,
            SEEK_CUR => SeekType::CURRENT,
            SEEK_END => SeekType::END,
        ];
        return $this->stream->setPosition($offset, new SeekType($map[$whence]));
    }

    public function stream_set_option($option, $arg1, $arg2) {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return $this->stream->setBlocking(!!$arg1);
            case STREAM_OPTION_READ_TIMEOUT:
                return $this->stream->setReadTimeout($arg1 * 1000000 + $arg2);
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
        return self::statResult($this->instance()->getAttributes($path, !($flags & STREAM_URL_STAT_LINK)));
    }

    /**
     * @return StreamWrapper2
     */
    private function instance() {
        return stream_context_get_options($this->context)[self::SCHEME]['instance'];
    }
}

stream_wrapper_register(StreamWrapper2Impl::SCHEME, StreamWrapper2Impl::class, STREAM_IS_URL);
