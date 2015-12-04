<?php

namespace JesseSchalken\FileSystem;

final class StreamWrapperFileSystem extends AbstractFileSystem {
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
            throw new Exception("$function() failed");
        }
        return $result;
    }

    private $sw;

    public function __construct(AbstractStreamWrapper $sw) {
        $this->sw = $sw;
    }

    public final function readDirectory($path) {
        $handle = self::check('opendir', function () use ($path) {
            return opendir($this->sw->getUrl($path), $this->sw->getContext($path));
        });
        return $handle === false ? null : new StreamWrapperOpenDir($handle);
    }

    public final function createDirectory($path, FilePermissions $mode, $recursive) {
        self::check('mkdir', function () use ($path, $mode, $recursive) {
            return mkdir($this->sw->getUrl($path), $mode->toInt(), $recursive, $this->sw->getContext($path));
        });
    }

    public final function rename($path1, $path2) {
        self::check('rename', function () use ($path1, $path2) {
            return rename($this->sw->getUrl($path1), $this->sw->getUrl($path2), $this->sw->getContext($path1));
        });
    }

    public final function removeDirectory($path) {
        self::check('rmdir', function () use ($path) {
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
        $handle = self::check('fopen', function () use ($path, $mode) {
            $url = $this->sw->getUrl($path);
            $ctx = $this->sw->getContext($path);
            return fopen($url, $mode . 'b', null, $ctx);
        });
        return new StreamWrapperOpenFile($handle);
    }

    public final function setLastModified($path, $lastModified, $lastAccessed) {
        self::check('touch', function () use ($path, $lastAccessed, $lastModified) {
            return touch($this->sw->getUrl($path), $lastModified, $lastAccessed);
        });
    }

    public final function setUserByID($path, $userID) {
        self::check('chown', function () use ($path, $userID) {
            return chown($this->sw->getUrl($path), (int)$userID);
        });
    }

    public final function setUserByName($path, $userName) {
        self::check('chown', function () use ($path, $userName) {
            return chown($this->sw->getUrl($path), (string)$userName);
        });
    }

    public final function setGroupByID($path, $groupID) {
        self::check('chgrp', function () use ($path, $groupID) {
            return chgrp($this->sw->getUrl($path), (int)$groupID);
        });
    }

    public final function setGroupByName($path, $groupName) {
        self::check('chgrp', function () use ($path, $groupName) {
            return chgrp($this->sw->getUrl($path), (string)$groupName);
        });
    }

    public final function setPermissions($path, FilePermissions $mode) {
        self::check('chmod', function () use ($path, $mode) {
            return chmod($this->sw->getUrl($path), $mode->toInt());
        });
    }

    public final function getAttributes($path, $followLinks) {
        $stat = self::check($followLinks ? 'stat' : 'lstat', function () use ($path, $followLinks) {
            $url = $this->sw->getUrl($path);
            return $followLinks ? stat($url) : lstat($url);
        });

        return $stat ? new StreamWrapperFileAttributes($stat) : null;
    }

    public final function delete($path) {
        self::check('unlink', function () use ($path) {
            return unlink($this->sw->getUrl($path));
        });
    }

    public function getStreamWrapper() {
        return $this->sw;
    }
}
