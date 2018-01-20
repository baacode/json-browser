<?php

use Sami\Sami;

return new Sami('src', [
    'title'     => 'JSON Browser API',
    'build_dir' => 'docs/master',
    'cache_dir' => 'docs/.cache',
    'theme'     => 'default'
]);
