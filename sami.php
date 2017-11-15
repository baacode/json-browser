<?php

use Sami\Sami;
use Sami\Version\GitVersionCollection;

$versions = GitVersionCollection::create(__DIR__)
    ->addFromTags('v*')
    ->add('master', 'dev-master')
    ;

return new Sami('src', [
    'title'     => 'JSON Browser API',
    'versions'  => $versions,
    'build_dir' => 'docs/%version%',
    'cache_dir' => 'docs/.cache/%version%',
    'theme'     => 'default'
]);
