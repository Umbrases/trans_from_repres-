<?php

namespace App\Model;

use App\Service\TaskService;
use App\Model\QueryHelper;

class Task
{
    private ?int $id;
    private ?string $title;
    private ?int $dealId;
    private $taskFile;
    private $description;
    private $deadline;
    private $startDatePlan;
    private ?int $changedBy;
    private ?int $createdBy;
    private $status;
    private $allowChangeDeadline;
    private ?int $responsibleId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id = null): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title = null): void
    {
        $this->title = $title;
    }

    public function getDealId(): ?int
    {
        return $this->dealId;
    }

    public function setDealId($dealId = null): void
    {
        if (empty($dealId)){
            $this->dealId = $dealId;
        } else {
            $this->dealId = trim($dealId, 'D_');
        }
    }

    /**
     * @return mixed
     */
    public function getTaskFile()
    {
        return $this->taskFile;
    }

    /**
     * @param mixed $taskFile
     */
    public function setTaskFile($taskFile): void
    {
        $this->taskFile = $taskFile;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * @param mixed $deadline
     */
    public function setDeadline($deadline): void
    {
        $this->deadline = $deadline;
    }

    /**
     * @return mixed
     */
    public function getStartDatePlan()
    {
        return $this->startDatePlan;
    }

    /**
     * @param mixed $startDatePlan
     */
    public function setStartDatePlan($startDatePlan): void
    {
        $this->startDatePlan = $startDatePlan;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy = null): void
    {
        $this->createdBy = $createdBy;
    }

    public function getChangedBy(): ?int
    {
        return $this->changedBy;
    }

    public function setChangedBy(?int $changedBy = null): void
    {
        $this->changedBy = $changedBy;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getAllowChangeDeadline()
    {
        return $this->allowChangeDeadline;
    }

    /**
     * @param mixed $allowChangeDeadline
     */
    public function setAllowChangeDeadline($allowChangeDeadline): void
    {
        $this->allowChangeDeadline = $allowChangeDeadline;
    }

    public function getResponsibleId(): ?int
    {
        return $this->responsibleId;
    }

    public function setResponsibleId($responsibleId = null): void
    {
        $this->responsibleId = $responsibleId;
    }

    public function setOnTask($classFrom, $classBefore, $folderId, $taskId): void
    {
        $safeMySQL = new SafeMySQL;
        $taskService = new TaskService;
        
        //Вывод задачи
        $task = $taskService->getTask($classFrom, $taskId);

        //Вывод сделки
        if (!empty($task->getDealId())) {
            $dealId = $task->getDealId();
        } else {
            $dealId = null;
        }

        //sql запросы
        $sqlBeforeId = $safeMySQL->getRow("SELECT `task_box` FROM det_task where task_cloud = ?i", (int)$taskId);
        $sqlDealBeforeId = $safeMySQL->getRow("SELECT `deal_box` FROM det_deal where deal_cloud = ?i", $dealId);
        $sqlTask = "INSERT INTO det_task SET deal_box = ?i , deal_cloud = ?i, task_cloud = ?i";
        $sqlUpdateTask = "UPDATE det_task SET task_box = ?i WHERE task_cloud = ?i";
        $sqlCount = "SELECT * FROM det_task where task_cloud = ?i";
        $sqlFile = "INSERT INTO `det_file` SET `file_cloud_id` = ?i, `file_box_id` = ?i";

        $fileTaskIds = [];
        //Проверка на файл в задаче
        if (!empty($task->getTaskFile())) {
            foreach ($task->getTaskFile() as $taskFile) {
                $sqlFileSearch = $safeMySQL
                    ->getRow("SELECT `file_box_id` FROM `det_file` WHERE `file_cloud_id` = ?i", $taskFile);

                //Вывод файла
                $fileTask = QueryHelper::getQuery($classFrom, 'disk.attachedObject.get', [
                    'id' => $taskFile,
                ]);
                //Считывание файла в строку
                $fileTaskContent = file_get_contents(str_replace(' ', '%20', $fileTask['result']['DOWNLOAD_URL']));
                if (empty($sqlFileSearch)) {
                    //Запись файла в битрикс
                    $fileUploadTask = $taskService->setFile(
                        $classBefore,
                        $folderId,
                        $fileTaskContent,
                        $fileTask
                    );

                    $safeMySQL->query($sqlFile, $taskFile, $fileUploadTask['result']['ID']);

                    //Добавить id в переменную
                    $fileTaskIds[] .= 'n' . $fileUploadTask['result']['ID'];
                } else {
                    //Добавить id в переменную
                    $fileTaskIds[] .= 'n' . $sqlFileSearch['file_box_id'];
                }
            }
        }

        if (empty($sqlDealBeforeId)) return;

        $columnResponsibleId = QueryHelper::getQuery($classBefore,
            'crm.deal.get', [
                'ID' => $sqlDealBeforeId['deal_box'],
            ])['result']['ASSIGNED_BY_ID'];

        $columnCreateBy = $safeMySQL->getRow("SELECT 'user_box' FROM det_user where 'user_cloud' = ?i", $task->getCreatedBy());
        $columnCreateBy = !empty($columnCreateBy) ? $columnCreateBy : 1;

        //Проверка на пустоту записи сделки в бд
        if (!empty($sqlBeforeId)) {
            $taskService->updateTask(
                $task,
                $classBefore,
                $sqlBeforeId['task_box'],
                $fileTaskIds
            );
        } else {
            $sqlCityCount = $safeMySQL->getAll($sqlCount, (int)$taskId);

            if (count($sqlCityCount) != 0) return;
            $safeMySQL->query($sqlTask, $sqlDealBeforeId['deal_box'], $dealId, (int)$taskId);

            $taskBox = $taskService->setTask(
                $task,
                $classBefore,
                $sqlDealBeforeId['deal_box'],
                $fileTaskIds,//Ошибка
                $taskId,
                $columnResponsibleId,
                $columnCreateBy,
                $sqlUpdateTask
            );
        }
    }

}

function writeToLog($data) {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}