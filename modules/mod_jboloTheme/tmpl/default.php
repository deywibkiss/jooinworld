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

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
//load techjoomla bootstrapper
include_once JPATH_ROOT.DS.'media'.DS.'techjoomla_strapper'.DS.'strapper.php';
TjAkeebaStrapper::bootstrap();
?>

<script>
	function changeTheme(value){
		var options=new Array();
		options['path']='/';
		gsCookie("jboloTheme",value,options);
		window.location.reload();
	}

	techjoomla.jQuery(document).ready(function()
	{
		var jboloTheme=gsCookie('jboloTheme');
		if(jboloTheme==='gmail')
		{
			document.getElementById("jboloThemeGmail").checked=true;
			document.getElementById("jboloThemeFacebook").checked=false;
		}
		else if(jboloTheme==='facebook')
		{
			document.getElementById("jboloThemeGmail").checked=false;
			document.getElementById("jboloThemeFacebook").checked=true;
		}
	});
</script>

<style type="text/css">
	.jbtable_td{vertical-align:middle !important; border:0px  !important; cursor:pointer;}
	.jbtable{border:0px  !important;}
</style>

<div class="techjoomla-bootstrap <?php echo $params->get('moduleclass_sfx'); ?>">
	<strong>Select Chat Theme</strong>
	</tr>
	<table class="table table-condensed jbtable" style="border:0px;">
		<tr  onClick="javascript:changeTheme('gmail');">
			<td class="jbtable_td">
				<input type="radio" name="theme" id="jboloThemeGmail" style="float:left; top:-2px;"/>
			</td>
			<td class="jbtable_td">
				<img src="<?php echo JURI::root().'modules/mod_jboloTheme/tmpl/gmail.png'; ?>" />
			</td>
		</tr>
		<tr onClick="javascript:changeTheme('facebook');" >
			<td class="jbtable_td">
				<input type="radio" name="theme" id="jboloThemeFacebook" style="float:left;"/>
			</td>
			<td class="jbtable_td">
				<img src="<?php echo JURI::root().'modules/mod_jboloTheme/tmpl/facebook.png'; ?>" />
			</td>
		</tr>
	</table>
</div>