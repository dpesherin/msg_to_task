
<?
IncludeModuleLangFile( __FILE__);
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/include.php");
CModule::IncludeModule(
 "mail"
);
if(class_exists("mcart_taskfromemailce")) 
	return;

Class mcart_taskfromemailce extends CModule
{
	var $MODULE_ID = "mcart.taskfromemailce";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	
	
	function mcart_taskfromemailce() 
	{
		$arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)){
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }else{
            $this->MODULE_VERSION=TASKFROMEMAILCE_MODULE_VERSION;
            $this->MODULE_VERSION_DATE=TASKFROMEMAILCE_MODULE_VERSION_DATE;
        }

        $this->MODULE_NAME = GetMessage("TASKFROMEMAILCE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("TASKFROMEMAILCE_MODULE_DESCRIPTION");
        
        $this->PARTNER_NAME = GetMessage("PARTNER_NAME");
        $this->PARTNER_URI  = "http://mcart.ru/";
	}
	
	function InstallDB()
    {
		return true;
    }
	function DoInstall() 
	
	{$this->InstallDB();
	
	global $APPLICATION, $step;
	$mailbox_id = IntVal($_REQUEST["mailbox_id"]);
	if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("TASKFROMEMAILCE_INSTALL_QUESTION"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.taskfromemailce/install/step1.php");	
			}
				
	elseif ($step ==2)
			{
			if (isset($mailbox_id))
				{
						$php_text = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.taskfromemailce/text.txt");


							$arFields = array(
								"ACTIVE" => "Y",
								"MAILBOX_ID" => $mailbox_id,
								"PARENT_FILTER_ID" => "",
								"NAME" => GetMessage("TASKFROMEMAILCE_MODULE_NAME"),
								"SORT" => 500,
								"WHEN_MAIL_RECEIVED" => "Y",
								"WHEN_MANUALLY_RUN" => "",
								"SPAM_RATING" => 0.0000,
								"SPAM_RATING_TYPE" => ">",
								"MESSAGE_SIZE" => 0,
								"MESSAGE_SIZE_TYPE" => ">",
								"MESSAGE_SIZE_UNIT" => "b",
								"DESCRIPTION" => GetMessage("TASKFROMEMAILCE_MODULE_DESCRIPTION"),
								"ACTION_PHP" => $php_text
							);

							$ID = CMailFilter::Add($arFields);
									if ($res = ($ID>0))
										{$handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.taskfromemailce/prolog.php", "w");
										fwrite($handle, '<?define("TASKFROMEMAILCE_RULE_ID", '.$ID.');?>');
										fclose($handle);		
										RegisterModule("mcart.taskfromemailce");	
										}
				}
			}
	
	}
	

	function DoUninstall()
	{	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.taskfromemailce/prolog.php");
		CMailFilter::Delete(TASKFROMEMAILCE_RULE_ID);
		UnRegisterModule("mcart.taskfromemailce");
	}
	
	
} //end class
	?>	