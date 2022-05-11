<?
class McartTaskOperations{


	function process($arMessageFields)
	{
		$USE_LOG = COption::GetOptionString("mcart.taskfromemailce", "USE_LOG", "");
		if ($USE_LOG=='checked')
			$bLog = true;
		else
			$bLog = false;
			
		if (CModule::IncludeModule("tasks"))
		{	
			$arr_responsible = array();
			$from = CMailUtil::ExtractMailAddress($arMessageFields['FIELD_FROM']);
			$rsUser = CUser::GetList(($by="ID"), ($order="desc"), array("email"=>$from));
			if ($oUser = $rsUser->Fetch())
				{
				$id_created = $oUser["ID"];
				
				}
				if (intval($id_created)>0)
				{
					if ($bLog)
						CEventLog::Add(array(
					 "SEVERITY" => "SECURITY",
					 "AUDIT_TYPE_ID" => "CREATED_BY_FOUND",
					 "MODULE_ID" => "mcart.taskfromemailce",
					 "ITEM_ID" => $id_created,
					 "DESCRIPTION" => "found created by from email".$from
						));
			  
					$arr_to = CMailUtil::ExtractAllMailAddresses($arMessageFields["FIELD_TO"].",".$arMessageFields["FIELD_CC"].",".$arMessageFields["FIELD_BCC"]);
					foreach ($arr_to as $to)
					{
					if (empty($to))
						continue;
					$rsUser = CUser::GetList(($by="ID"), ($order="desc"), array("email"=>$to));
					if ($oUser = $rsUser->Fetch())
						{
						$arr_responsible[] = $oUser["ID"];
						
						if ($bLog)
							CEventLog::Add(array(
							 "SEVERITY" => "SECURITY",
							 "AUDIT_TYPE_ID" => "RESPONSIBLE_FOUND",
							 "MODULE_ID" => "mcart.taskfromemailce",
							 "ITEM_ID" => $oUser["ID"],
							 "DESCRIPTION" => "found responsible from email".$to
								));
						}
					else
						{
							/*if ($bLog)
								CEventLog::Add(array(
								 "SEVERITY" => "SECURITY",
								 "AUDIT_TYPE_ID" => "ERROR: RESPONSIBLE NOT FOUND",
								 "MODULE_ID" => "mcart.taskfromemailce",
								 "ITEM_ID" => 1,
								 "DESCRIPTION" => "NOT found responsible from email".$to
									));
							*/		
						}
					
					}
					
					if (count($arr_responsible)>0)
					{
						$arFields = Array(
							"TITLE" => $arMessageFields['SUBJECT'],
							"DESCRIPTION" => TxtToHTML($arMessageFields['BODY']),
							"RESPONSIBLE_ID" => $arr_responsible[0],
							"STATUS"=>2,
							"CREATED_BY" => $id_created
							
						);
						
						$obTask = new CTasks;
						$ID = $obTask->Add($arFields);
						
						if (intval($ID)>0)
						{
							if ($bLog)
								CEventLog::Add(array(
							 "SEVERITY" => "SECURITY",
							 "AUDIT_TYPE_ID" => "CREATE new task",
							 "MODULE_ID" => "mcart.taskfromemailce",
							 "ITEM_ID" => $ID,
							 "DESCRIPTION" => "Create new task"
								));
							
							$arr_accomp = array_splice($arr_responsible, 1, (count($arr_responsible)-1));
							if (count($arr_accomp)>0)
								CTasks::AddAccomplices($ID, $arr_accomp);
						}
						else	
						{
							global $APPLICATION;
							if($e = $APPLICATION->GetException())
										$errString = $e->GetString(); 
							else
								$errString = 'task not created with fields '.print_r($arFields);
							if ($bLog)
								CEventLog::Add(array(
								 "SEVERITY" => "SECURITY",
								 "AUDIT_TYPE_ID" => "ERROR: task not created",
								 "MODULE_ID" => "mcart.taskfromemailce",
								 "ITEM_ID" => 1,
								 "DESCRIPTION" => "  error ".$errString
									));
						}	

						
					}
					else	
						{
							if ($bLog)
								CEventLog::Add(array(
								 "SEVERITY" => "SECURITY",
								 "AUDIT_TYPE_ID" => "ERROR: RESPONSIBLE array is empty",
								 "MODULE_ID" => "mcart.taskfromemailce",
								 "ITEM_ID" => 1,
								 "DESCRIPTION" => "NOT found responsible from email".print_r($arr_to,1)
									));
						}
				}
				else
					{
						if ($bLog)
							CEventLog::Add(array(
						 "SEVERITY" => "SECURITY",
						 "AUDIT_TYPE_ID" => "ERROR: created by not found",
						 "MODULE_ID" => "mcart.taskfromemailce",
						 "ITEM_ID" => 1,
						 "DESCRIPTION" => "NOT found created by from email".$from
							));
					}
			

		}
	}
}
?>