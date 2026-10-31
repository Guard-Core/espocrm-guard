<?php

// The smallest setup: the guard is on with the default policy. Passive mode is part of the default policy, so
// attacks are logged as "Guard would block by ..." and nothing is blocked.
return [
    'guardCore' => [
        'enabled' => true,
    ],
];
