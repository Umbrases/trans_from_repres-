<?php

namespace App\Controller;

use App\Model\File;
use App\Model\CRestBox;
use App\Model\CRestCloud;

class FilesController
{
    private File $file;

    public function __construct()
    {
        $this->file = new File;
    }

    public function store($fileId)
    {
        $classFrom = CRestCloud::class;
        $classBefore = CRestBox::class;

        $this->file->saveFile($fileId, $classFrom, $classBefore);
    }
}
