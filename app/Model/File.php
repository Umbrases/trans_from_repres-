<?php
namespace App\Model;

use App\Service\FileService;


class File
{
    private  FileService $fileService;

    public function __construct()
    {
        $this->fileService = new FileService;
    }
    public function saveFile($fileId, $classFrom, $classBefore)
    {
        $safeMySQL = new SafeMySQL;

        $file = $this->fileService->getFile($classFrom, $fileId);
        $fileContent = file_get_contents(str_replace(' ', '%20', $file['result']['DOWNLOAD_URL']));

        $detailUrl = explode('Contact', $file['result']['DETAIL_URL']);
        $contactId = explode('(', $detailUrl[1])[0];

        $contactBoxId = $safeMySQL->getRow(
            "SELECT contact_box FROM det_contact where contact_cloud = ?i",
            $contactId
        )['contact_box'];

        $contact = $this->fileService->getContact($contactBoxId, $classBefore);
        $folderName = $this->fileService->getFolderName($contact);
        $folder = $this->fileService->getFolder($classBefore, $folderName);

        if (empty($folder)) $folder = $this->fileService->setFolder($classBefore, $folderName);

        $this->fileService->setFile($classBefore, $folder['ID'], $fileContent, $file);
    }
}
