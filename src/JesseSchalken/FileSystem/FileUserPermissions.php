<?php

namespace JesseSchalken\FileSystem;

/**
 * Mutable class for the permissions of a particular class of user
 */
final class FileUserPermissions {
    /** @var int */
    private $perms = 07;

    /** @param int $perms */
    public function __construct($perms = 07) { $this->perms = $perms; }

    /** @param bool $bool */
    public function setRead($bool) { set_bit($this->perms, 2, $bool); }
    /** @param bool $bool */
    public function setWrite($bool) { set_bit($this->perms, 1, $bool); }
    /** @param bool $bool */
    public function setExecute($bool) { set_bit($this->perms, 0, $bool); }

    /** @return bool */
    public function getRead() { get_bit($this->perms, 2); }
    /** @return bool */
    public function getWrite() { get_bit($this->perms, 1); }
    /** @return bool */
    public function getExecute() { get_bit($this->perms, 0); }

    /** @return int */
    public function toInt() { return $this->perms; }
}