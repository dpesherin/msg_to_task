<?
class McartTaskOperations
{


    function process($arMessageFields)
    {
        //Include module task from Bitrix
        CModule::includeModule('tasks');

        $res = CTasks::GetList(
            Array("TITLE" => "ASC"),
            Array(
                "TITLE" => $arMessageFields['SUBJECT'],
                "DESCRIPTION" => TxtToHTML($arMessageFields['BODY'])
                )
        );

        if($res->GetNext()){
            return false;
        }


        //Get sender address from message headers
        $from = CMailUtil::ExtractMailAddress($arMessageFields['FIELD_FROM']);
        //Get user id from Bitrix with email == sender email
        $rsUser = CUser::GetList(($by = "ID"), ($order = "desc"), array("email" => $from));
        //Get data from CDBResult
        if ($oUser = $rsUser->Fetch()) {
            $id_created = $oUser["ID"];
        }else {
            return false;
        }

        //Get responsible from message headers
        $responsible = CMailUtil::ExtractMailAddress($arMessageFields["FIELD_TO"]);
        $rsUser = CUser::GetList(($by = "ID"), ($order = "desc"), array("email" => $responsible));
        if ($oUser = $rsUser->Fetch()) {
            $responsibleID = $oUser["ID"];
        }else {
            return false;
        }

        $arr_accomplices = [];
        $accomplices = CMailUtil::ExtractAllMailAddresses($arMessageFields["FIELD_CC"]);
        if($accomplices != [""]){
            foreach($accomplices as $el){
                $rsUser = CUser::GetList(($by = "ID"), ($order = "desc"), array("email" => $el));
                if ($oUser = $rsUser->Fetch()) {
                    $accompliceID = $oUser["ID"];
                    $arr_accomplices[] = $accompliceID;
                }
            }
        }


        //Get title for task from message header and switch groups
        if(strpos($arMessageFields['SUBJECT'], "1С") !== false || strpos($arMessageFields['SUBJECT'], "1с") !== false || strpos($arMessageFields['SUBJECT'], "1c") !== false || strpos($arMessageFields['SUBJECT'], "1C") !== false){
            $groupID = 3;
        } else {
            $groupID = 2;
        }

        //Check that responsible array isn't empty
        if (count($responsibleID) > 0) {
            //Get attachments from message
            $attachList = CMailAttachment::GetList(["NAME" => "ASC", "ID" => "ASC"], ["MESSAGE_ID" => $arMessageFields['ID']]);
            //Download each element to upload folder /home/bitrix/www/upload
            while ($attachElement = $attachList->GetNext()) {
                $attachElementID = $attachElement["ID"];
                $attachElementList = CMailAttachment::GetByID($attachElementID);
                if ($attachElementInfo = $attachElementList->Fetch()) {
                    $fname = $_SERVER['DOCUMENT_ROOT'] . "/upload/" . time() . $attachElementInfo["FILE_NAME"];

                    $handle = fopen($fname, 'wb');
                    fwrite($handle, $attachElementInfo["FILE_DATA"]);
                    fclose($handle);
                }
                //Include module disk from Bitrix
                if (\Bitrix\Main\Loader::includeModule('disk')) {
                    //Take instance
                    $driver = \Bitrix\Disk\Driver::getInstance();
                    //Set storage for Intranet group from Bitrix
                    $storage = $driver->getStorageByGroupId($groupID);
                    //Download files to intranet group disk
                    if ($storage) {
                        $folder = $storage->getRootObject();
                        $fileArray = \CFile::MakeFileArray($fname);
                        $file = $folder->uploadFile($fileArray, array(
                            'CREATED_BY' => 1
                        ));
                        //Get id from downloaded file
                        $diskFileID[] = $file->getId();
                    }
                }
            }
            $files = [];
            foreach($diskFileID as $el){
                array_push($files, 'n'.$el);
            }
            $body = explode('С уважением', $arMessageFields['BODY']);
            $arFields = array(
                "TITLE" => $arMessageFields['SUBJECT'],
                "DESCRIPTION" => $body[0],
                "RESPONSIBLE_ID" => $responsibleID,
                "STATUS" => 2,
                "GROUP_ID" => $groupID,
                "CREATED_BY" => $id_created,
                "UF_TASK_WEBDAV_FILES"   => $files,
                "ACCOMPLICES" => $arr_accomplices
            );

            if(strpos($arMessageFields['SUBJECT'], "RE:") !== false || strpos($arMessageFields['SUBJECT'], "Re:") !== false){
                $subject = substr($arMessageFields['SUBJECT'], 4);
                $res = CTasks::GetList(
                    Array("TITLE" => "ASC"),
                    Array(
                        "TITLE" => $subject,
                    )
                );
                if($item = $res->GetNext()){
                    $taskID = $item["ID"];
                    $oTaskItem = \CTaskItem::getInstance($taskID, $id_created);
                    $fields = array(
                        'AUTHOR_ID' => $id_created,
                        'USE_SMILES' => 'N',
                        'POST_MESSAGE' => $body[0],
                        'FILES' => $files,
                        'AUX' => 'Y',
                    );
                    \CTaskCommentItem::add($oTaskItem, $fields);
                }else{
                    return false;
                }
            }else{
                $task = new \Bitrix\Tasks\Item\Task($arFields, $responsibleID);
                $ID = $task->save();
            }


        }
    }

}

?>