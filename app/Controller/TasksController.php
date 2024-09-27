<?php

namespace App\Controller;

use App\Model\CRestBox;
use App\Model\CRestCloud;
use App\Model\Task;


class TasksController
{
    private Task $task;

    public function __construct()
    {
        $this->task = new Task;
    }

    public function store(int $taskId): void
    {
        $classFrom = CRestCloud::class;
        $classBefore = CRestBox::class;
        $columnFolderId = 502137;

        $this->task->setOnTask($classFrom, $classBefore, $columnFolderId, $taskId);
    }

}


function writeToLog($data)
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}