<?php

namespace StreamWrapper2;

abstract class StreamWrapper {
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
        if ($this->dir) {
            $this->dir = null;
            return true;
        } else {
            return false;
        }
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
        return !!$this->dir;
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
     * @param int $mode
     * @param int $options
     * @return bool
     */
    public function mkdir($url, $mode, $options) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);
        return $fs->createDirectory($path, FilePermissions::fromInt($mode), (bool)($options & STREAM_MKDIR_RECURSIVE));
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
        return$fs->rename($path1, $path2);
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.rmdir.php
     * @param string $url
     * @return bool
     */
    public function rmdir($url) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);
        return $fs->removeDirectory($path);
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-cast.php
     * @return resource
     */
    public function stream_cast() {
        return $this->stream->toResource();
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-close.php
     * @return void
     */
    public function stream_close() {
        $this->stream->close();
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-eof.php
     * @return bool
     */
    public function stream_eof() {
        return $this->stream->isEOF();
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-flush.php
     * @return bool
     */
    public function stream_flush() {
        return $this->stream->flushWrites();
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-lock.php
     * @param int $operation
     * @return bool
     */
    public function stream_lock($operation) {
        $noBlock = !!($operation & LOCK_NB);
        switch ($operation & ~LOCK_NB) {
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

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-metadata.php
     * @param string $url
     * @param int $option
     * @param mixed $value
     * @return bool
     */
    public function stream_metadata($url, $option, $value) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);
        switch ($option) {
            case STREAM_META_TOUCH:
                return $fs->setLastModified($path, $value[0], $value[1]);
            case STREAM_META_OWNER:
                return $fs->setUserByID($path, $value);
            case STREAM_META_OWNER_NAME:
                return $fs->setUserByName($path, $value);
            case STREAM_META_GROUP:
                return $fs->setGroupByID($path, $value);
            case STREAM_META_GROUP_NAME:
                return $fs->setGroupByName($path, $value);
            case STREAM_META_ACCESS:
                return $fs->setPermissions($path, FilePermissions::fromInt($value));
            default:
                return false;
        }
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.stream-open.php
     * @param string $url
     * @param string $mode
     * @param int $options
     * @param string $opened_path
     * @return bool
     */
    public function stream_open($url, $mode, $options, &$opened_path) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);

        $this->stream = $fs->openFile(
            $path,
            FileOpenMode::fromString($mode),
            !!($options & STREAM_USE_PATH),
            !!($options & STREAM_REPORT_ERRORS),
            $opened_path
        );
        return !!$this->stream;
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
                return $this->stream->setPosition($offset, false);
            case SEEK_END:
                return $this->stream->setPosition($offset, true);
            case SEEK_CUR:
                return $this->stream->addPosition($offset);
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
        return $this->stream->setSize($new_size);
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
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.url-stat.php
     * @param string $url
     * @param int $flags
     * @return array
     */
    public function url_stat($url, $flags) {
        $fs   = $this->getFileSystem($url);
        $path = $this->getPath($url);
        $stat = $fs->getAttributes(
            $path,
            !($flags & STREAM_URL_STAT_LINK),
            !($flags & STREAM_URL_STAT_QUIET)
        );
        return $stat ? $stat->toArray() : false;
    }

    /**
     * @param string $url
     * @return AbstractFileSystem
     */
    protected abstract function getFileSystem($url);

    /**
     * @param string $url
     * @return string
     */
    protected abstract function getPath($url);
}

