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

class plgUserPlg_user_jbolo_user extends JPlugin
{
	public function onUserLogin($user, $options = array())
	{
		$this->clearCookies();
		return true;
	}

	public function onUserLogout($user, $options = array())
	{
		$this->clearCookies();
		return true;
	}

	function clearCookies()
	{
		setcookie ("open_list", "", time() - 3600,"/");
		setcookie ("jbolo_chat_history", "", time() - 3600,"/");
		setcookie ("jbolo_mini", "", time() - 3600,"/");
		setcookie ("jbolo_close", "", time() - 3600,"/");
		setcookie ("jbolo_open", "", time() - 3600,"/");
		setcookie ("open_list","0",-1,"/");
		return true;
	}
}
?>