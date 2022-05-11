
<?
IncludeModuleLangFile(__FILE__);
CModule::IncludeModule(
 "mail"
);
?>
<form action="<?echo $APPLICATION->GetCurPage()?>" name="form1">
	<?=bitrix_sessid_post()?>
	<b><?echo GetMessage('SELECT_MAILBOX')?> </b><br>
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="hidden" name="id" value="mcart.taskfromemailce">
	<input type="hidden" name="install" value="Y">
	<?$mb = CMailBox::GetList(Array("NAME"=>"ASC", "ID"=>"ASC"));
		if (!($el_mb =$mb->GetNext() ))
		{
		echo GetMessage('NO_FOUND_MAILBOX');
		?>
		<input type="hidden" name="step" value="3">
		<?
		}
	else
		{
	?>
	
	
	
	<input type="hidden" name="step" value="2">
	
	
		<select name="mailbox_id">
		<?	$mb = CMailBox::GetList(Array("NAME"=>"ASC", "ID"=>"ASC"));
			while ($el_mb =$mb->GetNext() ):
			
				?><option value="<?echo $el_mb["ID"]?>"><?echo $el_mb["NAME"]?> [<?echo $el_mb["ID"]?>]</option><?
			endwhile;
		?>
		</select>
		<?}?>
	<br>
	<input type="submit" name="inst" value="<?echo GetMessage("CONTINUE")?>">
<form>