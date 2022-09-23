<?php

include 'vendor\autoload.php';

$uri = new \Solar\MicroFramework\Http\Uri('https://google.com?x=1#farts');
$new = $uri->withScheme('http');
echo $new;