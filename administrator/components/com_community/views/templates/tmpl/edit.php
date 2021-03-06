<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<script type="text/javascript">
function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  }
  else if (input.createTextRange) {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
}

function replaceSelection (input, replaceString) {
	if (input.setSelectionRange) {
		var selectionStart = input.selectionStart;
		var selectionEnd = input.selectionEnd;
		input.value = input.value.substring(0, selectionStart)+ replaceString + input.value.substring(selectionEnd);

		if (selectionStart != selectionEnd){
			setSelectionRange(input, selectionStart, selectionStart + 	replaceString.length);
		}else{
			setSelectionRange(input, selectionStart + replaceString.length, selectionStart + replaceString.length);
		}

	}else if (document.selection) {
		var range = document.selection.createRange();

		if (range.parentElement() == input) {
			var isCollapsed = range.text == '';
			range.text = replaceString;

			 if (!isCollapsed)  {
				range.moveStart('character', -replaceString.length);
				range.select();
			}
		}
	}
}


// We are going to catch the TAB key so that we can use it, Hooray!
function catchTab(item,e){
	if(navigator.userAgent.match("Gecko")){
		c=e.which;
	}else{
		c=e.keyCode;
	}
	if(c==9){
		var offset = joms.jQuery('#editFile').scrollTop();
		replaceSelection(item,String.fromCharCode(9));
		setTimeout("document.getElementById('"+item.id+"').focus();",0);

		joms.jQuery('#editFile').scrollTop(offset);
		offset = offset *-1 ;
		offset = '0 '+ offset + 'px';
		joms.jQuery(e).css('background-position', offset);

		return false;
	}

}

function saveTemplate(){
	var val = joms.jQuery('#editFile').val();
	var filename = joms.jQuery('#fileData').val();
	jax.call('community', 'cxSaveFile', filename, val);
}

function loadTempData(ext){
	//editFile.edit(document.getElementById('tempText').innerHTML, ext);
	//jQuery('#editFile').val(unescape(document.getElementById('tempText').innerHTML));
}

function scrollEditor(e){
	var offset = joms.jQuery(e).scrollTop();
	offset = offset *-1 ;
	offset = '0 '+ offset + 'px';
	joms.jQuery(e).css('background-position', offset);

}

function teHideMessage(){
	joms.jQuery('#msgDiv').fadeOut();
}

function teShowMessage(msg){
	var html = '<dl id="system-message">';
	html += '<dt class="message">Message</dt>';
	html += '<dd class="message message fade">';
	html += '<ul>';
	html += '<li>'+ msg +'</li>';
	html += '</ul>';
	html += '</dd>';
	html += '</dl>';

	joms.jQuery('#msgDiv').html(html).show();
	setTimeout('teHideMessage()', 2500);
}

</script>
<form id="adminForm" name="adminForm" method="post">
<table width="100%">
	<tr>
		<td>

		</td>
	</tr>
</table>
<table width="100%">
	<tr>
		<td valign="top" width="40%">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_COMMUNITY_TEMPLATE_INFO');?></legend>
				<table cellspacing="1" class="admintable">
				<tbody>
					<tr>
						<td valign="top" width="300" class="key"><?php echo JText::_('COM_COMMUNITY_NAME');?></td>
						<td valign="top"><b class="display-input"><?php echo $this->template->info['name'] ? $this->template->info['name'] : JText::_('N/A');?></b></td>
					</tr>
					<tr>
						<td valign="top" width="300" class="key"><?php echo JText::_('COM_COMMUNITY_DESCRIPTION');?></td>
						<td valign="top"><b class="display-input"><?php echo $this->template->info['description'] ? $this->template->info['description'] : JText::_('N/A');?></b></td>
					</tr>
				</tbody>
				</table>
			</fieldset>
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_COMMUNITY_PARAMETERS');?></legend>
				<?php
				$content	= $this->params->render();
				if( !empty( $content ) )
				{
					echo $content;
				}
				else
				{
					echo JText::_('No parameters');
				}
				?>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td valign="top" colspan="2">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_COMMUNITY_TEMPLATE_FILE');?></legend>
				<div id="status"></div>
				<table>
					<tr>
						<td valign="top" >
							<div>
								<div id="templates-list">
									<p><b><?php echo JText::_('COM_COMMUNITY_SELECT_FILE');?></b></p>
									<select name="file" style="float: none" onchange="azcommunity.editTemplate('<?php echo $this->template->element; ?>',this.value,'<?php echo $this->template->override ? 1 : 0;?>');">
										<option value="none" selected="true"><?php echo JText::_('COM_COMMUNITY_SELECT_FILE');?></option>
										<?php
											for( $i = 0; $i < count( $this->files ); $i++ )
											{
												echo '<option value="' . $this->files[$i] . '">' . $this->files[$i] . '</option>';
											}
										?>
									</select>
								</div>
							</div>
							<div>
								<table class="adminform">
									<tbody>
									<tr>
										<th style="vertical-align: middle">
											<span id="filePath"></span>
										</th>
										<th width="5%" align="right">
											<input type="button" onclick="azcommunity.saveTemplateFile('<?php echo $this->template->override ? 1 : 0;?>')" value="<?php echo JText::_('COM_COMMUNITY_SAVE');?>" />
										</th>
									</tr>
									<tr>
										<td colspan="2" style="border-top: 1px solid #ddd">
											<div id="msgDiv" style="display:none"></div>
											<div class="input-wrap">
												<textarea wrap="off" spellcheck="false" onscroll="scrollEditor(this);" onkeydown="return catchTab(this,event)" class="inputbox php template-editor" id="data" name="data" rows="300" cols="110"></textarea>
											</div>
										</td>
									</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
				</table>
			</fieldset>
			<input type="hidden" value="" id="fileName" />
			<input type="hidden" value="" id="templateName" />
		</td>
	</tr>
</table>
<input type="hidden" name="view" value="templates" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="override" value="<?php echo $this->override;?>" />
</form>