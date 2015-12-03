<?php

namespace JesseSchalken\FileSystem;

/**
 * @param int $int
 * @param int $bit
 * @return bool
 */
function get_bit($int, $bit) {
    return !!($int & (1 << $bit));
}

/**
 * @param int  $int
 * @param int  $bit
 * @param bool $bool
 */
function set_bit(&$int, $bit, $bool) {
    if ($bool)
        $int |= 1 << $bit;
    else
        $int &= ~(1 << $bit);
}

