<?php

namespace Solar\MicroFramework\Cache;

use Psr\SimpleCache\InvalidArgumentException;
use InvalidArgumentException as InvalidArgException;

class InvalidCacheKeyException extends InvalidARgException implements InvalidArgumentException
{
}