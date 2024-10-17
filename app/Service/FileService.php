<?php

namespace App\Service;

use App\Model\QueryHelper;
use App\Model\SafeMySQL;

class FileService
{
    public function getFile($classFrom, $fileId){
        return QueryHelper::getQuery($classFrom, 'disk.file.get', [
            'id' => $fileId,
        ]);
    }

    public function getFolder($classBefore, $folderName)
    {
        return QueryHelper::getQuery($classBefore, 'disk.folder.getchildren', [
            'id' => 403,
            'filter' =>[
                'NAME' => $folderName,
            ]])['result'][0];
    }

    public function setFolder($classBefore, $folderName)
    {
        return QueryHelper::getQuery($classBefore, 'disk.folder.addsubfolder', [
            'id' => 403,
            'data' =>[
                'NAME' => $folderName,
            ]])['result'];
    }

    public function getContact($contactBoxId, $classBefore)
    {
        return QueryHelper::getQuery($classBefore,
            'crm.contact.get', [
                'ID' => $contactBoxId,
            ])['result'];
    }

    public function getFolderName($contact)
    {
        if (empty($contact['NAME'])) {
            if (empty($contact['LAST_NAME'])){
                $folderName  = 'Contact ' . $contact['ID']; //Название папки контакта
            } else {
                $folderName = 'Contact ' . $contact['ID'] . ' (' . $contact['LAST_NAME'] . ')'; //Название папки контакта
            }
        } else {
            if (empty($sqlContactBeforeId['LAST_NAME'])){
                $folderName = 'Contact ' . $contact['ID'] . ' (' . $contact['NAME'] . ')'; //Название папки контакта
            } else {
                $folderName = 'Contact ' . $contact['ID'] . ' (' . $contact['NAME'] . ' ' . $contact['LAST_NAME'] . ')'; //Название папки контакта
            }
        }

        return $folderName;
    }

    public function setFile($classBefore, $folderId, $fileContent, $file)
    {
        QueryHelper::getQuery($classBefore, 'disk.folder.uploadfile', [
            'id' => $folderId,
            'data' => [
                'NAME' => $file['result']['NAME']
            ],
            'fileContent' => [$file['result']['NAME'], base64_encode($fileContent)],
            'generateUniqueName' => true,
        ]);
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