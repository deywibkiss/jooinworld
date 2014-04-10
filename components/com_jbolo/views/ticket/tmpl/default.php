<?php
/**
 * @package		JBolo
 * @version		$versionID$
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 * @copyright	Copyright © 2009-2013 TechJoomla. All rights reserved.
 * @license		GNU General Public License version 2, or later
*/
//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
JRequest::setVar('tmpl','component');
if(JVERSION<'3.0'){
	//load techjoomla bootstrapper
	include_once JPATH_ROOT.DS.'media'.DS.'techjoomla_strapper'.DS.'strapper.php';
	TjAkeebaStrapper::bootstrap();
}
//load jbolo css
$document=JFactory::getDocument();
$document->addStyleSheet(JURI::root().DS.'components'.DS.'com_jbolo'.DS.'css'.DS.'jboloapi.css');
?>
<div class="techjoomla-bootstrap jbolo_pad_me">
	<?php
	if(! $this->success)
	{
		$js="function onloadFunction(){
			this.document.title='".JText::_('COM_JBOLO_ADD_ACTIVITY')."'
		}";

		$doc=JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		if(count($this->ticketids)>=1)
		{
			$ticket_list='<select name="ticketid" id="ticketid">';
			foreach($this->ticketids as $tid)
			{
				$tid=str_replace("{".JText::_('COM_JBOLO_TICKED_ID_NO_SPACE')."=","",$tid);
				$tid=str_replace("}","",$tid);
				$ticket_list.='<option>'.$tid.'</option>';
			}
			$ticket_list.='</select>';
		}else{
			$ticket_list='<input type="text" name="ticketid" id="ticketid" value="">';
		}
		?>

		<form action="" method="POST" name="ticket_activity_details" id="ticket_activity_details">
			<table class="table table-striped table-bordered">
				<tr>
					<td><?php echo JText::_('COM_JBOLO_TICKED_ID');?>:</td>
					<td><?php echo $ticket_list; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JBOLO_CHAT_LOG');?>:</td>
					<td valign="top"><textarea  name="chatlog" id="chatlog" rows="10" ><?php echo $this->chatlog; ?> </textarea> </td>
				</tr>
			</table>

			<div class="form-actions">
				<input class="btn btn-danger" type="button" value="<?php echo JText::_('COM_JBOLO_CLOSE_WINDOW');?>" onclick="self.close();">
				&nbsp;
				<input class="btn btn-success" type="button" value="<?php echo JText::_('COM_JBOLO_ADD_ACTIVITY');?>" onclick="this.form.submit();" />
			</div>

			<input type="hidden" name="option" value="com_jbolo" />
			<input type="hidden" name="controller" value="ticket" />
			<input type="hidden" name="action" value="addActivityToTicket" />
			<input type="hidden" name="nid" value="<?php echo $this->nid; ?>" />
		</form>
	<?php
	}
	else{
		$js="var timeout = 3000;
		t=null;
		function onloadFunction(){
		t = setTimeout(\"self.close()\",timeout);
		}";

		echo '<div class="well">
			<div class="text-info jbolo_pad_me">
				<strong>'.JText::_('COM_JBOLO_TIMEOUT_MSG').'<strong>
			</div>';
		echo '<div class="jbolo_pad_me">
				<input class="btn btn-danger" type="button" value="'.JText::_('COM_JBOLO_CLOSE_WINDOW').'" onclick="self.close();">
			<div>
		</div>';
	}

	//load javascript
	$js .= 'if(window.addEventListener){ // Mozilla, Netscape, Firefox' . "\n";
	$js .= '    window.addEventListener("load", function(){ onloadFunction();}, false);' . "\n";
	$js .= '} else { // IE' . "\n";
	$js .= '    window.attachEvent("onload", function(){ onloadFunction();});' . "\n";
	$js .= '}';

	$doc=JFactory::getDocument();
	$doc->addScriptDeclaration($js);
?>
</div>