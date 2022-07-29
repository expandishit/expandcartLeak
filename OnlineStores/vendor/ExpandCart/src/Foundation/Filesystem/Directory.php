<?php

namespace ExpandCart\Foundation\Filesystem;

use SplFileInfo;
use ExpandCart\Foundation\Exceptions\FileException;

class Directory
{
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }
}
