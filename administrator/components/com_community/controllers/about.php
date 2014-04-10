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

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerAbout extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function ajaxCheckVersion()
	{
		$response		= new JAXResponse();

		$data			= $this->_getCurrentVersionData();
		ob_start();

		// Get the current version
		$version		= $this->_getLocalVersionNumber();

		$upgradeAction = '';

		// @TODO: TEMPORARY
		?>
		<style type="text/css">
		#cWindowAction {
		  font-size: 11px;
		}
		</style>
		<?

		if($data)
		{
			// Test versions
			if( version_compare($version , $data->version ,'<') )
			{
		?>
				<h5><?php echo JText::_('COM_COMMUNITY_UPDATE_SUMMARY');?></h5>
				<div style="color: red"><?php echo JText::_('COM_COMMUNITY_OLDER_VERSION_OF_JOM_SOCIAL');?></div><br />
				<div><?php echo JText::sprintf('Version installed: <span style="font-weight:700; color: red">%1$s</span>' , $this->_getLocalVersionString() );?></div>
				<div><?php echo JText::sprintf('Latest version available: <span style="font-weight:700;">%1$s</span>', $data->version ); ?></div>
		<?php

				if($this->_isUpdaterInstalled()) {
					$upgradeAction = '<input type="button" class="button saveButton" onclick="window.location=\'' . JRoute::_('index.php?option=com_ijoomlainstaller') . '\'" name="' . JText::_('Upgrade Now') . '" value="' . JText::_('Upgrade Now') . '" />';
				}
			} else {
	?>
			<div class="clearfix">
				<h5><?php echo JText::_('COM_COMMUNITY_UPDATE_SUMMARY');?></h5>
				<div><?php echo JText::_('COM_COMMUNITY_LATEST_VERSION_OF_JOM_SOCIAL'); ?></div>
				<div><?php echo JText::sprintf('Version installed: <span style="font-weight:700;">%1$s</span>' , $this->_getLocalVersionString() );?></div>
			</div>
	<?php
			}
		}
		else
		{
			?>
			<div style="color: red"><?php echo JText::_('Please enable "allow_url_fopen" to check version');?></div>
			<?php
		}
		$contents	= ob_get_contents();
		ob_end_clean();

		$response->addAssign( 'cWindowContent' , 'innerHTML' , $contents );

		$action = '<input type="button" class="button cancelButton" onclick="cWindowHide();" name="' . JText::_('COM_COMMUNITY_CLOSE') . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
		$action .= $upgradeAction;
		$response->addScriptCall('cWindowActions', $action);
		return $response->sendResponse();
	}

	public function _isUpdaterInstalled()
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT enabled FROM #__extensions WHERE name = 'com_ijoomlainstaller'");
		$is_enabled = $db->loadResult();

		return $is_enabled;
	}

	public function _getLocalBuildNumber()
	{
		$versionString	= $this->_getLocalVersionString();
		$tmpArray		= explode( '.' , $versionString );

		if( isset($tmpArray[2]) )
		{
			return $tmpArray[2];
		}

		// Unknown build number.
		return 0;
	}

	public function _getLocalVersionNumber()
	{
		 return $this->_getLocalVersionString();
	}

	public function _getCurrentVersionData()
	{
		$component_name = "com_community_std";
		$data = 'http://www.jomsocial.com/ijoomla_latest_version.txt';
		$installed_version = $this->_getLocalVersionNumber();
		
		$version = "";
		$ch = @curl_init($data);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_TIMEOUT, 10);					
		
		$version = @curl_exec($ch);
		if(isset($version) && trim($version) != ""){
			$pattern = "";
			if(version_compare(JVERSION, '3.0', 'ge')){
				$pattern = "/3.0_".$component_name."=(.*);/msU";
			} else {
				$pattern = "/1.6_com_community=(.*);/msU";
			}

			if($installed_version != 0 && $installed_version != ""){// on Joomla 2.5 and need to check available version, 2.8 or 3.0
				if(strpos($installed_version, "2.6") !== FALSE){
					$pattern = "/1.6_com_community=(.*);/msU";
				}
				elseif(strpos($installed_version, "2.8") !== FALSE){
					$pattern = "/3.0_com_community_std=(.*);/msU";
				}
				else{
					$pattern = "/3.0_".$component_name."=(.*);/msU";
				}
			} else {
				$pattern = "/3.0_".$component_name."=(.*);/msU";
			}
			
			preg_match($pattern, $version, $result);
			
			if(is_array($result) && count($result) > 0){
				$version = trim($result["1"]);
			} else {
				$version = "";
			}

			$data = new stdClass();
			$data->version = (string)$version;
			
			return $data;
		}

		return false;
	}

	public function _getLocalVersionString()
	{
		static $version		= '';

		if( empty( $version ) )
		{

			$xml		= JPATH_COMPONENT . '/community.xml';
			$parser		= new SimpleXMLElement( $xml , NULL , true );

			$version	= $parser->version;
		}
		return $version;
	}
}