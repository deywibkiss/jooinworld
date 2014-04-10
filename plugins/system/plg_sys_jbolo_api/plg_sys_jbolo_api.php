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

jimport('joomla.filesystem.file');
jimport('joomla.html.parameter');
if (!JFile::exists(JPATH_ROOT.DS.'components'.DS.'com_jbolo'.DS.'jbolo.php')) {
	return;
}

class plgSystemplg_sys_jbolo_api extends JPlugin
{
	/*function onAfterInitialise()*/
	function onAfterRoute()
	{
		$mainframe=JFactory::getApplication();
		//do not run in admin
		if($mainframe->isAdmin()){
			return 1;
		}

		$tmpl=JRequest::getVar('tmpl');
		//do not run in tmpl=component
		if($tmpl=='component'){
			return 1;
		}

		$user=JFactory::getUser();
		//do not run if user is not logged in
		if(!$user->id){
			return 1;
		}

		//important to load jquery here
		//$doc->addScript(JURI::root().'components/com_jbolo/jbolo/model/jquery-1.8.3.min.js');
		//load techjoomla bootstrapper
		include_once JPATH_ROOT.DS.'media'.DS.'techjoomla_strapper'.DS.'strapper.php';
		TjAkeebaStrapper::bootstrap();
	}

	function onAfterRender()
	{
		$mainframe=JFactory::getApplication();
		//do not run in admin
		if($mainframe->isAdmin()){
			return 1;
		}

		$tmpl=JRequest::getVar('tmpl');
		//do not run in tmpl=component
		if($tmpl=='component'){
			return 1;
		}

		$user=JFactory::getUser();
		//do not run if user is not logged in
		if(!$user->id){
			return 1;
		}

		//JFactory::getLanguage()->load('com_jbolo',JPATH_SITE,'en-GB',true);
		$lang=JFactory::getLanguage();
		$lang->load('com_jbolo', JPATH_SITE);
		$document=JFactory::getDocument();
		$params=JComponentHelper::getParams('com_jbolo');

		//get current template from config
		$template=$params->get('template');

		//get current template from cookie if available
		if(isset($_COOKIE["jboloTheme"])){
			$template=$_COOKIE["jboloTheme"];
		}

		/*set ALL required javascript variables & array of language constants*/
		$sendfile=$params->get('sendfile');
		$groupchat=$params->get('groupchat');
		$chathistory=$params->get('chathistory');
		$maxChatUsers=$params->get('maxChatUsers');
		$show_activity=$params->get('show_activity');

		$jb_minChatHeartbeat=$polltime=$params->get('polltime')*1000;
		$jb_maxChatHeartbeat=$params->get('maxChatHeartbeat')*1000;

		$dynamic_js_code="
		<script type='text/javascript'>
			var site_link='".JURI::root()."';
			var user_id=".$user->id.";
			var template='".$template."';

			var sendfile=".$sendfile.";
			var groupchat=".$groupchat.";
			var chathistory=".$chathistory.";
			var is_su=".$this->is_support_user().";

			var show_activity=".$show_activity.";
			var maxChatUsers=".$maxChatUsers.";
			var me_avatar_url='".JURI::root()."components/com_jbolo/jbolo/view/'+template+'/images/me_avatar_default.png';
			var avatar_url='".JURI::root()."components/com_jbolo/jbolo/view/'+template+'/images/avatar_default.png';
			var jb_minChatHeartbeat=".$jb_minChatHeartbeat.";
			var jb_maxChatHeartbeat=".$jb_maxChatHeartbeat.";

			var jbolo_lang=new Array();
			jbolo_lang['COM_JBOLO_ME']='".JText::_('COM_JBOLO_ME')."';
			jbolo_lang['COM_JBOLO_GC_MAX_USERS']='".JText::_('COM_JBOLO_GC_MAX_USERS')."';
			jbolo_lang['COM_JBOLO_NO_USERS_ONLINE']='".JText::_('COM_JBOLO_NO_USERS_ONLINE')."';
			jbolo_lang['COM_JBOLO_SAYS']='".JText::_('COM_JBOLO_SAYS')."';
			jbolo_lang['COM_JBOLO_SET_STATUS']='".JText::_('COM_JBOLO_SET_STATUS')."';
			jbolo_lang['COM_JBOLO_CHAT_WINDOW_EMPTY']='".JText::_('COM_JBOLO_CHAT_WINDOW_EMPTY')."';
			jbolo_lang['COM_JBOLO_ADD_ACTIVITY_PROMPT_MSG']='".JText::_('COM_JBOLO_ADD_ACTIVITY_PROMPT_MSG')."';
			jbolo_lang['COM_JBOLO_TICKED_ID_NO_SPACE']='".JText::_('COM_JBOLO_TICKED_ID_NO_SPACE')."';
			jbolo_lang['COM_JBOLO_CHAT']='".JText::_('COM_JBOLO_CHAT')."';
			jbolo_lang['COM_JBOLO_SEARCH_PEOPLE']='".JText::_('COM_JBOLO_SEARCH_PEOPLE')."';
			jbolo_lang['COM_JBOLO_AVAILABLE']='".JText::_('COM_JBOLO_AVAILABLE')."';
			jbolo_lang['COM_JBOLO_AWAY']='".JText::_('COM_JBOLO_AWAY')."';
			jbolo_lang['COM_JBOLO_BUSY']='".JText::_('COM_JBOLO_BUSY')."';
			jbolo_lang['COM_JBOLO_CLEAR_CUSTOM_MSGS']='".JText::_('COM_JBOLO_CLEAR_CUSTOM_MSGS')."';
			jbolo_lang['COM_JBOLO_MINIMIZE']='".JText::_('COM_JBOLO_MINIMIZE')."';
			jbolo_lang['COM_JBOLO_CLOSE']='".JText::_('COM_JBOLO_CLOSE')."';
			jbolo_lang['COM_JBOLO_INVITE']='".JText::_('COM_JBOLO_INVITE')."';
			jbolo_lang['COM_JBOLO_VIEW_HISTORY']='".JText::_('COM_JBOLO_VIEW_HISTORY')."';
			jbolo_lang['COM_JBOLO_SEND_FILE']='".JText::_('COM_JBOLO_SEND_FILE')."';
			jbolo_lang['COM_JBOLO_CLEAR_CONVERSATION']='".JText::_('COM_JBOLO_CLEAR_CONVERSATION')."';
			jbolo_lang['COM_JBOLO_ADD_USERS']='".JText::_('COM_JBOLO_ADD_USERS')."';
			jbolo_lang['COM_JBOLO_ADD_ACTIVITY_TO_TICKET']='".JText::_('COM_JBOLO_ADD_ACTIVITY_TO_TICKET')."';
			jbolo_lang['COM_JBOLO_LEAVE_CHAT']='".JText::_('COM_JBOLO_LEAVE_CHAT')."';
			jbolo_lang['COM_JBOLO_LEAVE_CHAT_CONFIRM_MSG']='".JText::_('COM_JBOLO_LEAVE_CHAT_CONFIRM_MSG')."';
			jbolo_lang['COM_JBOLO_OFFLINE_MSG1']='".JText::_('COM_JBOLO_OFFLINE_MSG1')."';
			jbolo_lang['COM_JBOLO_OFFLINE_MSG2']='".JText::_('COM_JBOLO_OFFLINE_MSG2')."';

			techjoomla.jQuery(document).ready(function()
			{
				var cookie;
				var close_cookie;
				chat_window_function();
				outerlist_fun();
				list_opener();
				start_chat_session();
				setTimeout( 'poll_msg()',".$polltime.");
			});
		</script>";

		$sitepath=JPATH_SITE;

		//load all needed css in array
		//load css for jquery ui
		$cssfiles[]='components/com_jbolo/jbolo/assets/css/smoothness/jquery-ui-1.9.2.custom.min.css';
		//set template css
		$cssfiles[]='components/com_jbolo/jbolo/view/'.$template.'/style.css';

		//call css loader function
		$this->_getCSSscripts($scripts, $cssfiles);

		//force ie8 rendering?
		if($this->params->get('ie_render')){
			$scripts[]='<meta http-equiv="x-ua-compatible" content="IE=8">';//FOR IE - render as ID|E 8 browser
		}

		//load all other js files
		$jsfiles[]='components/com_jbolo/jbolo/model/jquery.quicksearch.js';
		//load jquery templating js
		$jsfiles[]='components/com_jbolo/jbolo/model/jquery.tmpl.min.js';

		//$jsfiles[]='components/com_jbolo/jbolo/model/wtooltip.min.js';//added by bhagyashree

		//jbolo chat js
		$jsfiles[]='components/com_jbolo/jbolo/model/jbolo_chat.js';
		//for audio
		$jsfiles[]='components/com_jbolo/jbolo/model/modernizr-latest.js';

		//for autocomplete jquery ui
		$jsfiles[]='components/com_jbolo/jbolo/assets/js/jquery-ui-1.9.2.custom.min.js';

		//call js loader function
		$this->_getJSscripts($scripts, $jsfiles);

		//to insert all scripts into head tag
		$includescripts=$dynamic_js_code.implode("\n",$scripts);//dynamic_js_code is imp

		$body=JResponse::getBody();

		if($this->params->get('headtag_position')){
			$body=str_replace('<head>','<head>'.$includescripts,$body);
		}
		else{
			$body=str_replace('</head>',$includescripts.'</head>',$body);
		}

		//now lets push our jbolo html into body
		if($user->id)
		{
			$html_code='
			<div  style="display: none;">
				<div id="HTML5Audio"  style="display: none;">
					<input id="audiofile" type="text" value="" style="display: none;"/>
				</div>
				<audio id="myaudio" src="'.JURI::base().'components/com_jbolo/jbolo/assets/sounds/sample.wav"  style="display: none;">
					<span id="OldSound"></span>
					<input type="button" value="Play Sound" onClick="LegacyPlaySound(\'LegacySound\')">
				</audio>
			</div>

			<button class="listopener" id="listopener">
				<div></div>
				<span id="onlineusers">'.JText::_('COM_JBOLO_CHAT').'</span>
			</button>

			<div id="jbolouserlist_container" class="jbolouserlist_container" >';
				if($show_activity)
				{
					if($template=='facebook')//load activity sream only for FB template
					{
						$html_code.='
						<div class="jboloactivity_container">
							<div id="jboloactivity" class="jboloactivity" ></div>
						</div>
						';
					}
				}
				$html_code.='
				<div id="jbolouserlist" class="jbolouserlist" ></div>
			</div>

			<!-- end of <div class="jbolouserlist"> -->

			<div class="jbolochatwin" id="jbolochatwin" style="display:none">
			</div>
			<!-- end of <div class="jbolochatwin"> -->

			<script id="cmessage" type="text/x-jquery-tmpl"></script><!-- Chat Message template-->
			<script id="tooltip_temp" type="text/x-jquery-tmpl"></script><!-- tool tip template-->';

			//load chat message template into jqury template script tag
			$html_code.='<script id="chatmessage" type="text/x-jquery-tmpl">';
			$file=JPATH_SITE.'/components/com_jbolo/jbolo/view/'.$template.'/chatmessage.htm';
			$html_code.=file_get_contents($file);
			$html_code.='</script>';

			//load outer list template into jqury template script tag
			$html_code.='<!-- shows outerlist template -->
			<script id="outerlist" type="text/x-jquery-tmpl">';
			$file = JPATH_SITE.'/components/com_jbolo/jbolo/view/'.$template.'/outerlist.htm';
			$html_code.= file_get_contents($file);
			$html_code.='</script>';

			//load userlist template into jqury template script tag
			$html_code.='<!-- shows list template -->
			<script id="listtemplate" type="text/x-jquery-tmpl">';
			$file = JPATH_SITE.'/components/com_jbolo/jbolo/view/'.$template.'/list.htm';
			$html_code.= file_get_contents($file);
			$html_code.='</script>';

			//load logged_user template into jqury template script tag
			$html_code.='<!-- logged user template-->
			<script id="logged_user" type="text/x-jquery-tmpl">';
			$file = JPATH_SITE.'/components/com_jbolo/jbolo/view/'.$template.'/logged_user.htm';
			$html_code.= file_get_contents($file);
			$html_code.='</script>';

			//load chat window template into jqury template script tag
			$html_code.='<!-- chatwindow template-->
			<script id="chatwindow" type="text/x-jquery-tmpl">';
			$file = JPATH_SITE.'/components/com_jbolo/jbolo/view/'.$template.'/chatwindow.htm';
			$html_code.= file_get_contents($file);
			$html_code.='</script>';

			//load chat window template into jqury template script tag
			$html_code.='<!-- participant template-->
			<script id="pdetails" type="text/x-jquery-tmpl">';
			$file = JPATH_SITE.'/components/com_jbolo/jbolo/view/'.$template.'/pdetails.htm';
			$html_code.= file_get_contents($file);
			$html_code.='</script>';

			if($show_activity)
			{
				if($template=='facebook')//load activity sream only for FB template
				{
					//load activitystream template into jqury template script tag
					$html_code.='<!-- shows activitystream template -->
					<script id="activitystream" type="text/x-jquery-tmpl">';
					$file = JPATH_SITE.'/components/com_jbolo/jbolo/view/'.$template.'/activitystream.htm';
					$html_code.= file_get_contents($file);
					$html_code.='</script>';
				}
			}
		}
		else
		{
			$html_code='<b>'.JText::_('COM_JBOLO_LOGIN_CHAT').'</b>';
		}

		//append jbolo template before closing body tag
		$body=str_replace('</body>',$html_code.'</body>',$body);
		JResponse::setBody($body);

		return;
	}

	function _getCSSscripts(&$scriptList, $filenames)
	{
		//clear file status cache
		clearstatcache();
		$cssfile_path = JPATH_SITE.DS."components".DS."com_jbolo".DS."css".DS."jbolocss.php";
		//combine and minify css

		//echo $this->params->get('comb_mini');
		//var_dump(is_writable($cssfile_path)); die;

		if($this->params->get('comb_mini') && is_writable($cssfile_path))
		{

			//$sitepath=JPATH_SITE;
			$sitepath=JPATH_SITE.'/';
			foreach($filenames as $file){
				//$css_script[]="include('".$sitepath."/components/com_jbolo/css/".$file."');";
				$css_script[]="include('".$sitepath.$file."');";
			}
			$css_script=implode("\n",$css_script);
			$cssfile_path=JPATH_SITE.DS."components".DS."com_jbolo".DS."css".DS."jbolocss.php";
			$cssgzip='header("Content-type: text/css");
				ob_start("compress");
				function compress($buffer){
					/* remove comments */
					$buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $buffer);
					/* remove tabs, spaces, newlines, etc. */
					$buffer = str_replace(array("\r\n", "\r", "\n", "\t", "  ", "    ", "    "), "", $buffer);
					return $buffer;
				}';

			$data="<?php ".$cssgzip ."\n".$css_script."\n ob_end_flush();?>";
			if(JFile::write($cssfile_path, $data)){
				$scriptList[]='<link rel="stylesheet" href="'.JURI::root().'components/com_jbolo/css/jbolocss.php" type="text/css" />';
			}
			else{
				foreach($filenames as $file){
					//$scriptList[]='<link rel="stylesheet" href="'.JURI::root().'components/com_jbolo/css/'.$file.'" type="text/css" />';
					$scriptList[]='<link rel="stylesheet" href="'.JURI::root().$file.'" type="text/css" />';
				}
			}
		}
		else{
			foreach($filenames as $file){
				//$scriptList[]='<link rel="stylesheet" href="'.JURI::root().'components/com_jbolo/css/'.$file.'" type="text/css" />';
				$scriptList[]='<link rel="stylesheet" href="'.JURI::root().$file.'" type="text/css" />';
			}
		}
		//die("hrr");
	}

	function _getJSscripts(&$scriptList, $filenames)
	{
		//clear file status cache
		clearstatcache();
		$jsfile_path = JPATH_SITE.DS."components".DS."com_jbolo".DS."js".DS."jbolojs.php";
		//combine and minify js
		if($this->params->get('comb_mini') && is_writable($jsfile_path))
		{
			//$sitepath= JPATH_SITE;
			$sitepath=JPATH_SITE.'/';
			foreach($filenames as $file){
				if($file[0] == '/'){
					//$js_script[] = "include('".$sitepath."/components/com_jbolo".$file."');";
					$js_script[] = "include('".$sitepath.$file."');";
				}
				else{
					//$js_script[] = "include('".$sitepath."/components/com_jbolo/js/".$file."');";
					$js_script[] = "include('".$sitepath.$file."');";
				}
			}//end foreach
			//$js_script[] = "include('".JRoute::_('index.php?option=com_jbolo&view=js&format=raw')."');";
			$js_script = implode("\n",$js_script);

			$jsgzip='header("Content-type: text/javascript;");
				ob_start("compress");
				function compress($buffer){
					/* remove comments */
					$buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $buffer);
					/* remove tabs, spaces, newlines, etc. */
					$buffer = str_replace(array("\r\n", "\r", "\n", "\t", "  ", "    ", "    "), "", $buffer);
					return $buffer;
				}';

			$data = "<?php ".$jsgzip ."\n".$js_script."\n ob_end_flush();?>";
			if(JFile::write($jsfile_path, $data)){
				$scriptList[]='<script type="text/javascript" src="'.JURI::root().'components/com_jbolo/js/jbolojs.php"> </script>';
			}
			else{
				foreach($filenames as $file){
					if($file[0] == '/'){
						//$scriptList[]='<script type="text/javascript" src="'.JURI::root().'components/com_jbolo'.$file.'"> </script>';
						$scriptList[]='<script type="text/javascript" src="'.JURI::root().$file.'"> </script>';
					}
					else{
						//$scriptList[]='<script type="text/javascript" src="'.JURI::root().'components/com_jbolo/js/'.$file.'"> </script>';
						$scriptList[]='<script type="text/javascript" src="'.JURI::root().$file.'"> </script>';
					}
				}//end foreach
			}
		}//end if
		else{
			foreach($filenames as $file){
				if($file[0] == '/'){
					//$scriptList[]='<script type="text/javascript" src="'.JURI::root().'components/com_jbolo'.$file.'"> </script>';
					$scriptList[]='<script type="text/javascript" src="'.JURI::root().$file.'"> </script>';
				}
				else{
					//$scriptList[]='<script type="text/javascript" src="'.JURI::root().'components/com_jbolo/js/'.$file.'"> </script>';
					$scriptList[]='<script type="text/javascript" src="'.JURI::root().$file.'"> </script>';
				}
			}
		}
	}//end function

	//@TODO move this function to jbolo helper file
	function is_support_user()
	{
		$is_su=0;
		//require(JPATH_COMPONENT.DS."config".DS."config.php");
		$params=JComponentHelper::getParams('com_jbolo');
		$jbolo_helpdesk=$params->get('jbolo_helpdesk');
		if($jbolo_helpdesk){
			$database = & JFactory::getDBO();
			$user = & JFactory::getUser();
			$sql = "SELECT `id` FROM `#__support_permission` WHERE `id_user`=".$user->id;
			$database->setQuery( $sql );
			if($database->loadResult()){
				$is_su=1;
			}
		}
		return $is_su;
	}
}
?>