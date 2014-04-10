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
jimport('joomla.application.component.controller');

class JboloControllerSendfile extends JboloController
{
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	/* uploadFile */
	function uploadFile()
	{
		//get sendfile model
		$sendfileModel=$this->getModel('sendfile');
		//call model function - uploadFile
		$sendfileModel->uploadFile();
		//json reponse is outputed by model function itself
	}

	/* downloadFile */
	function downloadFile()
	{
		//get sendfile model
		$sendfileModel=$this->getModel('sendfile');
		//call model function - downloadFile
		$sendfileModel->downloadFile();
	}
}
?>