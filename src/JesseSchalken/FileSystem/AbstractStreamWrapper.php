<?php

namespace JesseSchalken\FileSystem;

/**
 * Abstract class for PHP stream wrappers
 */
abstract class AbstractStreamWrapper {
    /**
     * @param string $path
     * @return string
     */
    abstract function getUrl($path);

    /**
     * @param string $path
     * @return resource|null
     */
    function getContext($path) { return null; }
}