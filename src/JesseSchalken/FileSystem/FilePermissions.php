<?php

namespace JesseSchalken\FileSystem;

/**
 * Mutable class representing a file's permissions
 */
final class FilePermissions {
    /** * @var int */
    private $perms = 0;
    /** @var FileUserPermissions */
    private $user;
    /** @var FileUserPermissions */
    private $group;
    /** @var FileUserPermissions */
    private $other;

    /** @param int $int */
    public function __construct($int = 00777) {
        $this->perms = ($int >> 9) & 07;
        $this->user  = new FileUserPermissions($int >> 6);
        $this->group = new FileUserPermissions($int >> 3);
        $this->other = new FileUserPermissions($int >> 0);
    }

    /** @return FileUserPermissions */
    public function user() { return $this->user; }
    /** @return FileUserPermissions */
    public function group() { return $this->group; }
    /** @return FileUserPermissions */
    public function other() { return $this->other; }

    /** @return bool */
    public function getSetUID() { return get_bit($this->perms, 2); }
    /** @return bool */
    public function getSetGID() { return get_bit($this->perms, 1); }
    /** @return bool */
    public function getSticky() { return get_bit($this->perms, 0); }

    /** @param bool $bool */
    public function setSetUID($bool) { set_bit($this->perms, 2, $bool); }
    /** @param bool $bool */
    public function setSetGID($bool) { set_bit($this->perms, 1, $bool); }
    /** @param bool $bool */
    public function setSticky($bool) { set_bit($this->perms, 0, $bool); }

    /** * @return int */
    public function toInt() {
        $int = $this->perms << 9;
        $int |= $this->user->toInt() << 6;
        $int |= $this->group->toInt() << 3;
        $int |= $this->other->toInt() << 0;
        return $int;
    }
}