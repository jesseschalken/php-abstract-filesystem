<?php

namespace StreamWrapper2;

/**
 * @param \Exception $e
 * @return null
 * @throws \Exception
 */
function throw_(\Exception $e) {
    throw $e;
}

interface StreamWrapper2 {
    /**
     * @param string $path
     * @return Iterator
     */
    public function openDir($path);

    /**
     * @param string $path
     * @param int    $mode
     * @param bool   $recursive
     * @return bool
     */
    public function mkdir($path, $mode, $recursive);

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
    public function rmdir($path);

    /**
     * @param string   $path
     * @param FileMode $mode
     * @param bool     $read
     * @param bool     $write
     * @return null|Stream
     */
    public function openFile($path, FileMode $mode, $read, $write);

    /**
     * @param string $path
     * @param int    $mtime
     * @param int    $atime
     * @return bool
     */
    public function touch($path, $mtime, $atime);

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
     * @param string $path
     * @param int    $permissions
     * @return bool
     */
    public function setPermissions($path, $permissions);

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

interface Stream {
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
    public function isEof();

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

abstract class Enum {
    protected static $values;

    /** @var int|string */
    private $value;

    /**
     * @param int|string $value
     * @throws \Exception
     */
    function __construct($value) {
        if (!in_array($value, self::$values, true) {
            throw new \UnexpectedValueException("'$value' must be one of " . join(', ', static::$values));
        } else {
            $this->value = $value;
        }
    }

    function value() { return $this->value; }
    function equals(self $that) { return $this->value === $that->value; }
}

class Lock extends Enum {
    const SHARED    = LOCK_SH;
    const EXCLUSIVE = LOCK_EX;
    const NONE      = LOCK_UN;

    protected static $values = [
        self::EXCLUSIVE,
        self::SHARED,
        self::NONE,
    ];
}

class FileAttributes {
    public $dev     = 0;
    public $ino     = 0;
    public $mode    = 0;
    public $uid     = 0;
    public $gid     = 0;
    public $size    = 0;
    public $atime   = 0;
    public $mtime   = 0;
    public $ctime   = 0;

    public function toArray() {
        $blockSize = 4096;
        return [
            'dev'     => $this->dev,
            'ino'     => $this->ino,
            'mode'    => $this->mode,
            'nlink'   => 1,
            'uid'     => $this->uid,
            'gid'     => $this->gid,
            'rdev'    => 0,
            'size'    => $this->size,
            'atime'   => $this->atime,
            'mtime'   => $this->mtime,
            'ctime'   => $this->ctime,
            'blksize' => -1,
            'blocks'  => (int)floor($this->size / 512),
        ];
    }
}

class SeekType extends Enum {
    const CURRENT = SEEK_CUR;
    const END     = SEEK_END;
    const START   = SEEK_SET;

    protected static $values = [
        self::CURRENT,
        self::END,
        self::START,
    ];
}

class FileMode extends Enum {
    const NO_CREATE          = 'r';
    const CREATE_OR_TRUNCATE = 'w';
    const CREATE_OR_APPEND   = 'a';
    const CREATE_OR_KEEP     = 'c';
    const CREATE_ONLY        = 'x';

    protected static $values = [
        self::NO_CREATE,
        self::CREATE_OR_TRUNCATE,
        self::CREATE_OR_APPEND,
        self::CREATE_OR_KEEP,
        self::CREATE_ONLY,
    ];
}

final class StreamWrapper2Impl extends \streamWrapper {
    const SCHEME = 'sw2';
    /** @var Iterator */
    private $dir;
    /** @var Stream */
    private $stream;

    public function __construct() {
    }

    public function __destruct() {
    }

    public function dir_closedir() {
        $this->dir = null;
        return true;
    }

    public function dir_opendir($path, $options) {
        $this->dir = $this->instance()->openDir($path);
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
        return $this->instance()->mkdir($path, $mode, !!($options | STREAM_MKDIR_RECURSIVE));
    }

    public function rename($path_from, $path_to) {
        return $this->instance()->rename($path_from, $path_to);
    }

    public function rmdir($path, $options) {
        return $this->instance()->rmdir($path);
    }

    public function stream_cast($cast_as) {
        return $this->stream->toResource();
    }

    public function stream_close() {
        $this->stream->close();
    }

    public function stream_eof() {
        return $this->stream->isEof();
    }

    public function stream_flush() {
        return $this->stream->flush();
    }

    public function stream_lock($operation) {
        $block = !($operation & LOCK_NB);
        if ($operation & LOCK_SH) {
            $lock = new Lock(Lock::SHARED);
        } else if ($operation & LOCK_EX) {
            $lock = new Lock(Lock::EXCLUSIVE);
        } else if ($operation & LOCK_UN) {
            $lock = new Lock(Lock::NONE);
        } else {
            return false;
        }
        return $this->stream->setLock($lock, $block);
    }

    public function stream_metadata($path, $option, $value) {
        $instance = $this->instance();
        switch ($option) {
            case STREAM_META_TOUCH:
                return $instance->touch($path, $value[0], $value[1]);
            case STREAM_META_OWNER:
                return $instance->setUserByID($path, $value);
            case STREAM_META_OWNER_NAME:
                return $instance->setUserByName($path, $value);
            case STREAM_META_GROUP:
                return $instance->setGroupByID($path, $value);
            case STREAM_META_GROUP_NAME:
                return $instance->setGroupByName($path, $value);
            case STREAM_META_ACCESS:
                return $instance->setPermissions($path, $value);
            default:
                return false;
        }
    }

    public function stream_open($path, $mode, $options, &$opened_path) {
        if ($options & STREAM_USE_PATH) {
            throw new Exception('STREAM_USE_PATH is not supported');
        }

        $mode_ = new FileMode(str_replace(['+', 'b', 't'], '', $string));

        $this->stream = $this->instance()->openFile(
            $path,
            $mode_,
            $mode_->value() === 'r' || strpos($mode, '+') !== false,
            $mode_->value() !== 'r' || strpos($mode, '+') !== false
        );
        return !!$this->stream;
    }

    public function stream_read($count) {
        return $this->stream->read($count);
    }

    public function stream_seek($offset, $whence = SEEK_SET) {
        return $this->stream->setPosition($offset, new SeekType($whence));
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
        return $this->stream->getAttributes()->toArray();
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
        return $this->instance()->getAttributes($path, !($flags & STREAM_URL_STAT_LINK));
    }

    /**
     * @return StreamWrapper2
     */
    private function instance() {
        return stream_context_get_options($this->context)[self::SCHEME]['instance'];
    }
}

stream_wrapper_register(StreamWrapper2Impl::SCHEME, StreamWrapper2Impl::class, STREAM_IS_URL);
