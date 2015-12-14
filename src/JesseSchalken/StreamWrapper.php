<?php

namespace JesseSchalken\StreamWrapper;

use JesseSchalken\FileSystem;

/**
 * Abstract class for PHP stream wrappers
 */
abstract class StreamWrapper {
    /**
     * @param string $path
     * @return string
     */
    abstract function getUrl($path);

    /**
     * @param string $path
     * @return resource|null
     */
    function getContext(
        /** @noinspection PhpUnusedParameterInspection */
        $path
    ) {
        return null;
    }
}

final class FileAttributes extends FileSystem\FileAttributes {
    private $array;
    public function __construct(array $array) { $this->array = $array; }
    public function getID() { return $this->array['ino']; }
    public function getRefCount() { return $this->array['nlink']; }
    public function getOuterDeviceID() { return $this->array['dev']; }
    public function getInnerDeviceID() { return $this->array['rdev']; }
    public function getType() { return new FileSystem\FileType(($this->array['mode'] >> 12) & 017); }
    public function getPermissions() { return new FileSystem\FilePermissions($this->array['mode'] & 07777); }
    public function getSize() { return $this->array['size']; }
    public function getUserID() { return $this->array['uid']; }
    public function getGroupID() { return $this->array['gid']; }
    public function getLastAccessed() { return $this->array['atime']; }
    public function getLastModified() { return $this->array['mtime']; }
    public function getLastChanged() { return $this->array['ctime']; }
    public function getBlockSize() { return $this->array['blksize']; }
    public function getBlocks() { return $this->array['blocks']; }
}

final class StreamWrapperOpenFile extends FileSystem\OpenFile {
    /** @var resource */
    private $handle;

    /**
     * @param resource $resource
     */
    public function __construct($resource) {
        $this->handle = $resource;
    }

    public function __destruct() {
        Errors::check('fclose', function () {
            return fclose($this->handle);
        });
    }

    public function read($count) {
        Errors::check('fread', function () use ($count) {
            return fread($this->handle, $count);
        });
    }

    public function toResource() {
        return $this->handle;
    }

    public function isEof() {
        Errors::check('feof', function () use (&$eof) {
            $eof = feof($this->handle);
        });
        return $eof;
    }

    public function flush() {
        Errors::check('fflush', function () {
            fflush($this->handle);
        });
    }

    public function setLock(FileSystem\FileLock $lock) {
        Errors::check('flock', function () use ($lock) {
            return flock($this->handle, $lock->value());
        });
    }

    public function setLockNoBlock(FileSystem\FileLock $lock) {
        Errors::check('flock', function () use ($lock, &$success) {
            $success = flock($this->handle, $lock->value() & LOCK_NB);
        });
        return $success;
    }

    public function setPosition($position, $fromEnd) {
        Errors::check('fseek', function () use ($position, $fromEnd) {
            return fseek($this->handle, $position, $fromEnd ? SEEK_END : SEEK_SET);
        });
    }

    public function addPosition($position) {
        Errors::check('fseek', function () use ($position) {
            return fseek($this->handle, $position, SEEK_CUR);
        });
    }

    public function getPosition() {
        return Errors::check('ftell', function () {
            return ftell($this->handle);
        });
    }

    public function setSize($size) {
        Errors::check('ftruncate', function () use ($size) {
            return ftruncate($this->handle, $size);
        });
    }

    public function write($data) {
        Errors::check('fwrite', function () use ($data) {
            return fwrite($this->handle, $data);
        });
    }

    public function getAttributes() {
        $stat = Errors::check('fstat', function () {
            return fstat($this->handle);
        });
        return $stat ? new FileAttributes($stat) : null;
    }

    public function setBlocking($blocking) {
        Errors::check('stream_set_blocking', function () use ($blocking) {
            return stream_set_blocking($this->handle, $blocking ? 1 : 0);
        });
    }

    public function setReadTimeout($seconds, $microseconds) {
        Errors::check('stream_set_timeout', function () use ($seconds, $microseconds) {
            return stream_set_timeout($this->handle, $seconds, $microseconds);
        });
    }

    public function setWriteBuffer($size) {
        Errors::check('stream_set_write_buffer', function () use ($size) {
            return stream_set_write_buffer($this->handle, $size);
        });
    }
}

class Errors {
    /**
     * @param string   $function
     * @param \Closure $c
     * @return mixed
     * @throws \Exception
     */
    static function check($function, \Closure $c) {
        static $handler;
        if (!$handler) {
            $handler = function ($code, $message, $file, $line) {
                throw new ErrorException($code, $message, $file, $line);
            };
        }
        set_error_handler($handler);
        try {
            $result = $c();
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }
        restore_error_handler();
        if ($result === false) {
            throw new FileSystem\Exception("$function() failed");
        }
        return $result;
    }
}

abstract class StreamWrapperFileSystem extends FileSystem\FileSystem {
    private $sw;

    public function __construct(StreamWrapper $sw) {
        $this->sw = $sw;
    }

    public final function readDirectory($path) {
        $handle = Errors::check('opendir', function () use ($path) {
            return opendir($this->sw->getUrl($path), $this->sw->getContext($path));
        });
        return $handle === false ? null : new OpenDir($handle);
    }

    public final function createDirectory($path, FileSystem\FilePermissions $mode, $recursive) {
        Errors::check('mkdir', function () use ($path, $mode, $recursive) {
            return mkdir($this->sw->getUrl($path), $mode->toInt(), $recursive, $this->sw->getContext($path));
        });
    }

    public final function rename($path1, $path2) {
        Errors::check('rename', function () use ($path1, $path2) {
            return rename($this->sw->getUrl($path1), $this->sw->getUrl($path2), $this->sw->getContext($path1));
        });
    }

    public final function removeDirectory($path) {
        Errors::check('rmdir', function () use ($path) {
            return rmdir($this->sw->getUrl($path), $this->sw->getContext($path));
        });
    }

    public function createFile($path, $readable) {
        return $this->_openFile($path, $readable ? 'x+' : 'x');
    }

    public function createOrOpenFile($path, $readable) {
        return $this->_openFile($path, $readable ? 'c+' : 'c');
    }

    public function createOrAppendFile($path, $readable) {
        return $this->_openFile($path, $readable ? 'a+' : 'a');
    }

    public function createOrTruncateFile($path, $readable) {
        return $this->_openFile($path, $readable ? 'w+' : 'w');
    }

    public function openFile($path, $writable) {
        return $this->_openFile($path, $writable ? 'r+' : 'r');
    }

    private function _openFile($path, $mode) {
        $handle = Errors::check('fopen', function () use ($path, $mode) {
            $url = $this->sw->getUrl($path);
            $ctx = $this->sw->getContext($path);
            return fopen($url, $mode . 'b', null, $ctx);
        });
        return new StreamWrapperOpenFile($handle);
    }

    public final function setLastModified($path, $lastModified, $lastAccessed) {
        Errors::check('touch', function () use ($path, $lastAccessed, $lastModified) {
            return touch($this->sw->getUrl($path), $lastModified, $lastAccessed);
        });
    }

    public final function setUserByID($path, $userID) {
        Errors::check('chown', function () use ($path, $userID) {
            return chown($this->sw->getUrl($path), (int)$userID);
        });
    }

    public final function setUserByName($path, $userName) {
        Errors::check('chown', function () use ($path, $userName) {
            return chown($this->sw->getUrl($path), (string)$userName);
        });
    }

    public final function setGroupByID($path, $groupID) {
        Errors::check('chgrp', function () use ($path, $groupID) {
            return chgrp($this->sw->getUrl($path), (int)$groupID);
        });
    }

    public final function setGroupByName($path, $groupName) {
        Errors::check('chgrp', function () use ($path, $groupName) {
            return chgrp($this->sw->getUrl($path), (string)$groupName);
        });
    }

    public final function setPermissions($path, FileSystem\FilePermissions $mode) {
        Errors::check('chmod', function () use ($path, $mode) {
            return chmod($this->sw->getUrl($path), $mode->toInt());
        });
    }

    public final function getAttributes($path, $followLinks) {
        $stat = Errors::check($followLinks ? 'stat' : 'lstat', function () use ($path, $followLinks) {
            $url = $this->sw->getUrl($path);
            return $followLinks ? stat($url) : lstat($url);
        });

        return $stat ? new FileAttributes($stat) : null;
    }

    public final function delete($path) {
        Errors::check('unlink', function () use ($path) {
            return unlink($this->sw->getUrl($path));
        });
    }

    public function getStreamWrapper() {
        return $this->sw;
    }
}

class ErrorException extends FileSystem\Exception {
    public function __construct($code, $message, $file, $line) {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
    }
}

final class OpenDir implements \Iterator {
    private $key = 0;
    private $handle;
    private $current;

    /**
     * @param resource $handle
     */
    public function __construct($handle) {
        $this->handle = $handle;
    }

    public function __destruct() {
        if ($this->handle) {
            Errors::check('closedir', function () {
                closedir($this->handle);
            });
            $this->handle = null;
        }
    }

    public function current() {
        if ($this->current === null) {
            Errors::check('readdir', function () {
                $this->current = readdir($this->handle);
            });
        }
        return $this->current;
    }

    public function next() {
        if ($this->current === null) {
            Errors::check('readdir', function () {
                readdir($this->handle);
            });
        } else {
            $this->current = null;
        }
        $this->key++;
    }

    public function valid() {
        return $this->current() !== false;
    }

    public function key() {
        return $this->key;
    }

    public function rewind() {
        $this->key = 0;
        Errors::check('rewinddir', function () {
            rewinddir($this->handle);
        });
    }
}

/**
 * Stream wrapper for FileSystem objects
 */
class FileSystemStreamWrapper extends StreamWrapper {
    /**
     * @var FileSystem\FileSystem[]
     */
    private static $ids = [];

    /**
     * @param int $id
     * @return FileSystem\FileSystem
     */
    static function getFileSystem($id) {
        if (isset(self::$ids[$id])) {
            return self::$ids[$id];
        } else {
            throw new FileSystem\Exception(<<<s
Unknown file system with ID '$id'. Remember, you must hold a reference to the StreamWrapper as long as you need the URLs to work.
s
            );
        }
    }

    /**
     * @var int
     */
    private $id;

    function __construct(FileSystem\FileSystem $fs) {
        static $id = 1;
        $this->id = $id++;

        self::$ids[$this->id] = $fs;
    }

    function __destruct() {
        unset(self::$ids[$this->id]);
    }

    function getUrl($path) {
        $path = new FileSystemStreamWrapperPath($this->id, $path);
        return $path->getUrl();
    }
}

class FileSystemStreamWrapperPath {
    /** @var string|null */
    private static $protocol;

    /**
     * @param string $url
     * @return self
     */
    public static function parse($url) {
        $parts = explode('://', $url, 2);
        if (count($parts) < 2 || $parts[0] !== self::$protocol) {
            throw new FileSystem\Exception("Invalid URL: $url");
        }
        $parts = explode(':', $parts[1], 2);
        if (count($parts) < 2) {
            throw new FileSystem\Exception("Invalid URL: $url");
        }
        return new self($parts[0], $parts[1]);
    }

    /** @var int */
    private $id;
    /** @var string */
    private $path;

    /**
     * @param int    $id
     * @param string $path
     */
    public function __construct($id, $path) {
        $this->id   = $id;
        $this->path = $path;
    }

    public function getUrl() {
        if (self::$protocol === null) {
            self::$protocol = '__afs' . mt_rand();
            stream_wrapper_register(self::$protocol, __streamWrapper::class, STREAM_IS_URL);
        }
        return self::$protocol . "://$this->id:$this->path";
    }

    public function getPath() {
        return $this->path;
    }

    public function getFileSystem() {
        return FileSystemStreamWrapper::getFileSystem($this->id);
    }
}

abstract class __streamWrapper {
    private static function parse($url) {
        return FileSystemStreamWrapperPath::parse($url);
    }

    /**
     * @link http://php.net/manual/en/class.streamwrapper.php#streamwrapper.props.context
     * @var resource|null
     */
    public $context;

    /** @var \Iterator|null */
    private $dir;
    /** @var FileSystem\OpenFile|null */
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
        $url = self::parse($url);

        $this->dir = $url->getFileSystem()->readDirectory($url->getPath());
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
        $url = self::parse($url);
        $url->getFileSystem()->createDirectory(
            $url->getPath(),
            new FileSystem\FilePermissions($mode),
            !!($options & STREAM_MKDIR_RECURSIVE)
        );
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.rename.php
     * @param string $url1
     * @param string $url2
     * @return bool
     */
    public function rename($url1, $url2) {
        $url1 = self::parse($url1);
        $url2 = self::parse($url2);
        $url1->getFileSystem()->rename(
            $url1->getPath(),
            $url2->getPath()
        );
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.rmdir.php
     * @param string $url
     * @return bool
     */
    public function rmdir($url) {
        $url = self::parse($url);
        $url->getFileSystem()->removeDirectory($url->getPath());
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
            return $this->stream->setLockNoBlock(new FileSystem\FileLock($operation & ~LOCK_NB));
        } else {
            $this->stream->setLock(new FileSystem\FileLock($operation));
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
        $url  = self::parse($url);
        $fs   = $url->getFileSystem();
        $path = $url->getPath();
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
                $fs->setPermissions($path, new FileSystem\FilePermissions($value));
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
     * @throws FileSystem\Exception
     */
    public function stream_open($url, $mode, $options) {
        if ($options & STREAM_USE_PATH) {
            throw new FileSystem\Exception("STREAM_USE_PATH is not supported");
        }

        try {
            $this->stream = $this->_stream_open($url, $mode);
            return true;
        } catch (FileSystem\Exception $e) {
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
     * @return FileSystem\OpenFile
     */
    private function _stream_open($url, $mode) {
        $url  = self::parse($url);
        $fs   = $url->getFileSystem();
        $path = $url->getPath();
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
                throw new FileSystem\Exception("Invalid fopen() mode: '$mode");
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
        $url = self::parse($url);
        $url->getFileSystem()->delete($url->getPath());
        return true;
    }

    /**
     * @link http://php.net/manual/en/streamwrapper.url-stat.php
     * @param string $url
     * @param int    $flags
     * @return array
     */
    public function url_stat($url, $flags) {
        try {
            $url = self::parse($url);
            return $url->getFileSystem()->getAttributes(
                $url->getPath(),
                !($flags & STREAM_URL_STAT_LINK)
            )->toArray();
        } catch (FileSystem\Exception $e) {
            if ($flags & STREAM_URL_STAT_QUIET) {
                return false;
            } else {
                throw $e;
            }
        }
    }
}
