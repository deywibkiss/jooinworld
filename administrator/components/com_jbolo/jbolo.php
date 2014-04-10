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
if(JVERSION<'3.0'){
	//load techjoomla bootstrapper
	include_once JPATH_ROOT.DS.'media'.DS.'techjoomla_strapper'.DS.'strapper.php';
	TjAkeebaStrapper::bootstrap();
}

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');
// Require specific controller if requested
$input=JFactory::getApplication()->input;
if($controller=$input->get('controller','','STRING')){
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path))
		require_once $path;
	else
		$controller='';
}
//Create the controller
$classname='JboloController'.ucfirst($controller);
$controller=new $classname( );
//Perform the Request task
$controller->execute($input->get('task','','STRING'));
//Redirect if set by the controller
$controller->redirect();
?>