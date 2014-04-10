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

/*load language file for plugin frontend*/
$lang=JFactory::getLanguage();
$lang->load('plg_community_plg_js_jbolo_online',JPATH_ADMINISTRATOR);

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php');

class plgCommunityplg_js_jbolo_online extends CApplications
{
	var $name="Chat Status";
	var $_name='plg_js_jbolo_online';
	var $_path='';
	var $_cache=null;

	function plgCommunityplg_js_jbolo_online(&$subject,$config)
	{
		parent::__construct($subject,$config);
	}

	function onProfileDisplay()
	{
		$model 	= CFactory::getModel('profile');
		$my		= CFactory::getUser();
		$user	= CFactory::getRequestUser();
		$this->loadUserParams();
		/*$user=CFactory::getActiveProfile();
		$my=JFactory::getUser();*/
		$db=JFactory::getDBO();

		$lang=JFactory::getLanguage();
		$lang->load('com_jbolo');
		$accepted=0;
		$content='';

		$jboloallowall=$this->userparams->get('jboloallowall',1);
		if(!$jboloallowall)
		{
			$db=JFactory::getDBO();
			$query="SELECT status
				FROM #__community_connection
				WHERE connect_from={$my->id} AND connect_to={$user->id}";
			$db->setQuery($query);
			$accepted=$db->loadResult();
		}
		else{
			$accepted=1;
		}

		if($accepted)
		{
			$db->setQuery("SELECT userid FROM #__session WHERE userid={$user->id}");
			if($my->id!=$user->id)
			{
				if($db->loadResult())
				{

					$content="<table width='100%'>
						<tr>
							<td class='fieldCell'>
								<a style='text-decoration:none;' href='javascript:void(0)' onclick=\"javascript:chatFromAnywhere(".$my->id.",".$user->id.")\">
									<div class='statusicon_1'></div>
										<span>".
											sprintf(JText::_('COM_JBOLO_COMMUNITY_PLG_ONLINE_MSG'),$user->username)."
										</span>
								</a>
							</td>
						</tr>
					</table>";
				}
				else
				{
					$content="<table width='100%'>
						<tr>
							<td class='fieldCell'>
								<div class='statusicon_4'></div>
									<span>".
										sprintf(JText::_('COM_JBOLO_COMMUNITY_PLG_OFFLINE_MSG'),$user->username)."
									</span>
							</td>
						</tr>
					</table>";
				}
			}
		}
		else if($user->id != $my->id)
		{
			$content="<table width='100%'>
				<tr>
					<td class='fieldCell'>".JText::_('COM_JBOLO_COMMUNITY_PLG_PRIVACY')."</td>
				</tr>
			</table>";
		}
		return $content;
	}
}
?>