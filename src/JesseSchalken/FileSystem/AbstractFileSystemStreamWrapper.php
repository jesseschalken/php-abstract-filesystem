<?php

namespace JesseSchalken\FileSystem;

/**
 * Stream wrapper for abstract file systems
 */
class AbstractFileSystemStreamWrapper extends AbstractStreamWrapper {
    /**
     * @var AbstractFileSystem[]
     */
    private static $ids = [];

    /**
     * @param int $id
     * @return AbstractFileSystem
     */
    static function fromId($id) {
        return self::$ids[$id];
    }

    /**
     * @var int
     */
    private $id;

    function __construct(AbstractFileSystem $fs) {
        static $id = 1;
        $this->id = $id++;

        self::$ids[$this->id] = $fs;
    }

    function __destruct() {
        unset(self::$ids[$this->id]);
    }

    /**
     * @return int
     */
    function getId() {
        return $this->id;
    }

    /**
     * @return AbstractFileSystem
     */
    function getFileSystem() {
        return self::fromId($this->id);
    }

    function getUrl($path) {
        return __streamWrapper::PROTOCOL . '://' . $this->id . ':' . $path;
    }
}

abstract class __streamWrapper {
    const PROTOCOL = '__afs';

    /**
     * @link http://php.net/manual/en/class.streamwrapper.php#streamwrapper.props.context
     * @var resource|null
     */
    public $context;

    /** @var \Iterator|null */
    private $dir;
    /** @var AbstractOpenFile|null */
    private $stream;

    /**
     * @link http://php.net/manual/en/streamwrapper.construct.php
     */
    public function __construct() {
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.destruct.php
     */
    public function __destruct() {
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.dir-closedir.php
     * @return bool
     */
    public function dir_closedir() {
        $this->dir = null;
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.dir-opendir.php
     * @param string $url
     * @return bool
     */
    public function dir_opendir($url) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);

        $this->dir = $fs->readDirectory($path);
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.dir-readdir.php
     * @return string
     */
    public function dir_readdir() {
        if ($this->dir->valid()) {
            $result = $this->dir->current();
            $this->dir->next();
            return $result;
        } else {
            return false;
        }
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.dir-rewinddir.php
     * @return bool
     */
    public function dir_rewinddir() {
        $this->dir->rewind();
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.mkdir.php
     * @param string $url
     * @param int    $mode
     * @param int    $options
     * @return bool
     */
    public function mkdir($url, $mode, $options) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);

        $fs->createDirectory($path, new FilePermissions($mode), !!($options & STREAM_MKDIR_RECURSIVE));
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.rename.php
     * @param string $url1
     * @param string $url2
     * @return bool
     */
    public function rename($url1, $url2) {
        $fs    = $this->getFileSystem($url1);
        $path1 = $this->getPath($url1);
        $path2 = $this->getPath($url2);

        $fs->rename($path1, $path2);
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.rmdir.php
     * @param string $url
     * @return bool
     */
    public function rmdir($url) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);
        $fs->removeDirectory($path);
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-cast.php
     * @return resource
     */
    public function stream_cast() {
        return $this->stream->toResource() ?: false;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-close.php
     * @return void
     */
    public function stream_close() {
        $this->stream = null;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-eof.php
     * @return bool
     */
    public function stream_eof() {
        return $this->stream->isEof();
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-flush.php
     * @return bool
     */
    public function stream_flush() {
        $this->stream->flush();
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-lock.php
     * @param int $operation
     * @return bool
     */
    public function stream_lock($operation) {
        if ($operation & LOCK_NB) {
            return $this->stream->setLockNoBlock(new FileLock($operation & ~LOCK_NB));
        } else {
            $this->stream->setLock(new FileLock($operation));
            return true;
        }
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-metadata.php
     * @param string $url
     * @param int    $option
     * @param mixed  $value
     * @return bool
     */
    public function stream_metadata($url, $option, $value) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);
        switch ($option) {
            case STREAM_META_TOUCH:
                $fs->setLastModified($path, $value[0], $value[1]);
                break;
            case STREAM_META_OWNER:
                $fs->setUserByID($path, $value);
                break;
            case STREAM_META_OWNER_NAME:
                $fs->setUserByName($path, $value);
                break;
            case STREAM_META_GROUP:
                $fs->setGroupByID($path, $value);
                break;
            case STREAM_META_GROUP_NAME:
                $fs->setGroupByName($path, $value);
                break;
            case STREAM_META_ACCESS:
                $fs->setPermissions($path, new FilePermissions($value));
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-open.php
     * @param string $url
     * @param string $mode
     * @param int    $options
     * @return bool
     * @throws Exception
     */
    public function stream_open($url, $mode, $options) {
        if ($options & STREAM_USE_PATH) {
            throw new Exception("STREAM_USE_PATH is not supported");
        }

        try {
            $this->stream = $this->_stream_open($url, $mode);
            return true;
        } catch (Exception $e) {
            if ($options & STREAM_REPORT_ERRORS) {
                throw $e;
            } else {
                return false;
            }
        }
    }

    /**
     * @param string $url
     * @param string $mode
     * @return AbstractOpenFile
     */
    private function _stream_open($url, $mode) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);
        $rw   = strpos(substr($mode, 1), '+') !== false;

        switch (substr($mode, 0, 1)) {
            case 'r':
                return $fs->openFile($path, $rw);
            case 'w':
                return $fs->createOrTruncateFile($path, $rw);
            case 'a':
                return $fs->createOrAppendFile($path, $rw);
            case 'x':
                return $fs->createFile($path, $rw);
            case 'c':
                return $fs->createOrOpenFile($path, $rw);
            default:
                throw new Exception("Invalid fopen() mode: '$mode");
        }
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-read.php
     * @param int $count
     * @return string
     */
    public function stream_read($count) {
        return $this->stream->read($count);
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-seek.php
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    public function stream_seek($offset, $whence = SEEK_SET) {
        switch ($whence) {
            case SEEK_SET:
                $this->stream->setPosition($offset, false);
                return true;
            case SEEK_END:
                $this->stream->setPosition($offset, true);
                return true;
            case SEEK_CUR:
                $this->stream->addPosition($offset);
                return true;
            default:
                return false;
        }
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-set-option.php
     * @param int $option
     * @param int $arg1
     * @param int $arg2
     * @return bool
     */
    public function stream_set_option($option, $arg1, $arg2) {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                $this->stream->setBlocking(!!$arg1);
                return true;
            case STREAM_OPTION_READ_TIMEOUT:
                $this->stream->setReadTimeout($arg1, $arg2);
                return true;
            case STREAM_OPTION_WRITE_BUFFER:
                switch ($arg1) {
                    case STREAM_BUFFER_NONE:
                        $this->stream->setWriteBuffer(0);
                        return true;
                    case STREAM_BUFFER_FULL:
                        $this->stream->setWriteBuffer($arg2);
                        return true;
                    default:
                        return false;
                }
            default:
                return false;
        }
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-stat.php
     * @return array
     */
    public function stream_stat() {
        $stat = $this->stream->getAttributes();
        return $stat ? $stat->toArray() : false;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-tell.php
     * @return int
     */
    public function stream_tell() {
        return $this->stream->getPosition();
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-truncate.php
     * @param int $new_size
     * @return bool
     */
    public function stream_truncate($new_size) {
        $this->stream->setSize($new_size);
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-write.php
     * @param string $data
     * @return int
     */
    public function stream_write($data) {
        return $this->stream->write($data);
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.unlink.php
     * @param string $url
     * @return bool
     */
    public function unlink($url) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);
        $fs->delete($path);
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.url-stat.php
     * @param string $url
     * @param int    $flags
     * @return array
     */
    public function url_stat($url, $flags) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);
        $stat = $fs->getAttributes(
            $path, !($flags & STREAM_URL_STAT_LINK)
        );
        return $stat ? $stat->toArray() : false;
    }

    /**
     * @param string $url
     * @return AbstractFileSystem
     */
    private function getFileSystem($url) {
        $url = explode('://', $url)[1];
        $id  = explode(':', $url)[0];
        return AbstractFileSystemStreamWrapper::fromId($id);
    }

    /**
     * @param string $url
     * @return string
     */
    private function getPath($url) {
        $url  = explode('://', $url)[1];
        $path = explode(':', $url)[1];
        return $path;
    }
}

stream_wrapper_register(__streamWrapper::PROTOCOL, __streamWrapper::class, STREAM_IS_URL);

