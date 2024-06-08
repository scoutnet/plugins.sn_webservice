<?php
/**
 * Copyright (c) 2016-2024 Stefan (Mütze) Horst
 *
 * I don't have the time to read through all the licences to find out
 * what they exactly say. But it's simple. It's free for non-commercial
 * projects, but as soon as you make money with it, I want my share :-)
 * (License: Free for non-commercial use)
 *
 * Authors: Stefan (Mütze) Horst <muetze@scoutnet.de>
 */

namespace ScoutNet\Api\Exceptions;

class ScoutNetExceptionMissingConfVar extends ScoutNetException
{
    public function __construct(string $var = '', int $code = 0)
    {
        parent::__construct("Missing '$var'. Please Contact your Admin to enter a valid credentials for ScoutNet Connect. You can request them via <a href=\"mailto:scoutnetconnect@scoutnet.de\">scoutnetConnect@ScoutNet.de</a>.", $code);
    }
}
