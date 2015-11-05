<?php

if (!class_exists('streamWrapper')) {
    /**
     * @link http://php.net/manual/en/class.streamwrapper.php
     */
    abstract class streamWrapper {
        /**
         * @link http://php.net/manual/en/class.streamwrapper.php#streamwrapper.props.context
         * @var resource|null
         */
        public $context;

        /**
         * @link http://php.net/manual/en/streamwrapper.construct.php
         */
        public abstract function __construct();

        /**
         * @link http://php.net/manual/en/streamwrapper.destruct.php
         */
        public abstract function __destruct();

        /**
         * @link http://php.net/manual/en/streamwrapper.dir-closedir.php
         * @return bool
         */
        public abstract function dir_closedir();

        /**
         * @link http://php.net/manual/en/streamwrapper.dir-opendir.php
         * @param string $path
         * @param int $options
         * @return bool
         */
        public abstract function dir_opendir($path, $options);

        /**
         * @link http://php.net/manual/en/streamwrapper.dir-readdir.php
         * @return string
         */
        public abstract function dir_readdir();

        /**
         * @link http://php.net/manual/en/streamwrapper.dir-rewinddir.php
         * @return bool
         */
        public abstract function dir_rewinddir();

        /**
         * @link http://php.net/manual/en/streamwrapper.mkdir.php
         * @param string $path
         * @param int $mode
         * @param int $options
         * @return bool
         */
        public abstract function mkdir($path, $mode, $options);

        /**
         * @link http://php.net/manual/en/streamwrapper.rename.php
         * @param string $path_from
         * @param string $path_to
         * @return bool
         */
        public abstract function rename($path_from, $path_to);

        /**
         * @link http://php.net/manual/en/streamwrapper.rmdir.php
         * @param string $path
         * @param int $options
         * @return bool
         */
        public abstract function rmdir($path, $options);

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-cast.php
         * @param int $cast_as
         * @return resource
         */
        public abstract function stream_cast($cast_as);

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-close.php
         * @return void
         */
        public abstract function stream_close();

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-eof.php
         * @return bool
         */
        public abstract function stream_eof();

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-flush.php
         * @return bool
         */
        public abstract function stream_flush();

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-lock.php
         * @param int $operation
         * @return bool
         */
        public abstract function stream_lock($operation);

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-metadata.php
         * @param string $path
         * @param int $option
         * @param mixed $value
         * @return bool
         */
        public abstract function stream_metadata($path, $option, $value);

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-open.php
         * @param string $path
         * @param string $mode
         * @param int $options
         * @param string $opened_path
         * @return bool
         */
        public abstract function stream_open($path, $mode, $options, &$opened_path);

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-read.php
         * @param int $count
         * @return string
         */
        public abstract function stream_read($count);

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-seek.php
         * @param int $offset
         * @param int $whence
         * @return bool
         */
        public abstract function stream_seek($offset, $whence = SEEK_SET);

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-set-option.php
         * @param int $option
         * @param int $arg1
         * @param int $arg2
         * @return bool
         */
        public abstract function stream_set_option($option, $arg1, $arg2);

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-stat.php
         * @return array
         */
        public abstract function stream_stat();

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-tell.php
         * @return int
         */
        public abstract function stream_tell();

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-truncate.php
         * @param int $new_size
         * @return bool
         */
        public abstract function stream_truncate($new_size);

        /**
         * @link http://php.net/manual/en/streamwrapper.stream-write.php
         * @param string $data
         * @return int
         */
        public abstract function stream_write($data);

        /**
         * @link http://php.net/manual/en/streamwrapper.unlink.php
         * @param string $path
         * @return bool
         */
        public abstract function unlink($path);

        /**
         * @link http://php.net/manual/en/streamwrapper.url-stat.php
         * @param string $path
         * @param int $flags
         * @return array
         */
        public abstract function url_stat($path, $flags);
    }
}
