<?php

// Bootstrap for unit tests.

$rootLoader = require "vendor/autoload.php";
require "site/vendor/autoload.php";
require "site/custom/Espo/Modules/GuardCore/vendor/autoload.php";

// EspoCRM's own dev dependencies include PHPUnit, in a version that depends on the EspoCRM release. PHPUnit and its
// libraries must keep coming from the root vendor, so its autoloader goes first again.
$rootLoader->unregister();
$rootLoader->register(true);
