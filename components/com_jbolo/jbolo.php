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

//define directory separator
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

//load nodes helper file
$nodesHelperPath=dirname(__FILE__).DS.'helpers'.DS.'nodes.php';
if(!class_exists('nodesHelper'))
{
	JLoader::register('nodesHelper',$nodesHelperPath );
	JLoader::load('nodesHelper');
}

//load users helper file
$usersHelperPath=dirname(__FILE__).DS.'helpers'.DS.'users.php';
if(!class_exists('usersHelper'))
{
	JLoader::register('usersHelper',$usersHelperPath );
	JLoader::load('usersHelper');
}

//load integrations helper file
$integrationsHelperPath=dirname(__FILE__).DS.'helpers'.DS.'integrations.php';
if(!class_exists('integrationsHelper'))
{
	JLoader::register('integrationsHelper',$integrationsHelperPath );
	JLoader::load('integrationsHelper');
}

//load jbolo helper file
$jboloHelperPath=dirname(__FILE__).DS.'helpers'.DS.'jbolo.php';
if(!class_exists('jboloHelper'))
{
	JLoader::register('jboloHelper',$jboloHelperPath );
	JLoader::load('jboloHelper');
}

//load jbolo helper file
$chatBroadcastHelperPath=dirname(__FILE__).DS.'helpers'.DS.'chatBroadcast.php';
if(!class_exists('chatBroadcastHelper'))
{
	JLoader::register('chatBroadcastHelper',$chatBroadcastHelperPath );
	JLoader::load('chatBroadcastHelper');
}

//Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');
//Require specific controller if requested
if($controller=JFactory::getApplication()->input->get('controller','','STRING')) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	}
	else {
		$controller = '';
		require_once (JPATH_COMPONENT.DS.'controller.php');
	}
}
//Create the controller
$classname='JboloController'.ucfirst($controller);
$controller=new $classname( );
//Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('action','','STRING'));
// Redirect if set by the controller
$controller->redirect();
?>