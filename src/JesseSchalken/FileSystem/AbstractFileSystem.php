<?php

namespace JesseSchalken\FileSystem;

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
     * @return AbstractOpenFile
     */
    public abstract function openFile($path, $writable);

    /**
     * x/x+ mode
     * @param string $path
     * @param bool   $readable
     * @return AbstractOpenFile
     */
    public abstract function createFile($path, $readable);

    /**
     * c/c+ mode
     * @param string $path
     * @param bool   $readable
     * @return AbstractOpenFile
     */
    public abstract function createOrOpenFile($path, $readable);

    /**
     * a/a+ mode
     * @param string $path
     * @param bool   $readable
     * @return AbstractOpenFile
     */
    public abstract function createOrAppendFile($path, $readable);

    /**
     * w/w+ mode
     * @param string $path
     * @param bool   $readable
     * @return AbstractOpenFile
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
     * @return AbstractFileAttributes
     */
    public abstract function getAttributes($path, $followLinks);

    /**
     * @param string $path
     * @return void
     */
    public abstract function delete($path);

    public function getStreamWrapper() {
        return new AbstractFileSystemStreamWrapper($this);
    }
}

