<?php

namespace marcocesarato\amwscan\Interfaces;

interface VerifierInterface
{
    /**
     * Initialize path.
     *
     * @param $path
     *
     * @return mixed
     */
    public static function init($path);

    /**
     * Is verified file.
     *
     * @param $path
     *
     * @return mixed
     */
    public static function isVerified($path);
}