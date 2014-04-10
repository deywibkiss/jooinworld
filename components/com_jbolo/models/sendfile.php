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
jimport('joomla.application.component.model');

class JboloModelSendFile extends JModelLegacy
{
	function uploadFile()
	{
		//load uploadHelper file
		$uploadHelperPath=JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'upload.php';
		//require_once ($uploadHelperPath);
		if(!class_exists('uploadHelper'))
		{
		   JLoader::register('uploadHelper',$uploadHelperPath );
		   JLoader::load('uploadHelper');
		}
		$upload_handler=new uploadHelper();//doing this echoes json response after file is uploded
	}

	function downloadFile()
	{
		// Allow direct file download (hotlinking)?
		// Empty - allow hotlinking
		// If set to nonempty value (Example: example.com) will only allow downloads when referrer contains this text
		define('ALLOWED_REFERRER', '');

		// Download folder, i.e. folder where you keep all files for download.
		// MUST end with slash (i.e. "/" )
		define('BASE_DIR',JPATH_SITE.'/components/com_jbolo/uploads/');

		// log downloads?  true/false
		define('LOG_DOWNLOADS',true);

		// log file name
		define('LOG_FILE',JPATH_SITE.'/components/com_jbolo/downloads_log.php');


		// Allowed extensions list in format 'extension' => 'mime type'
		// If myme type is set to empty string then script will try to detect mime type
		// itself, which would only work if you have Mimetype or Fileinfo extensions
		// installed on server.
		$allowed_ext = array (
			// archives
			'zip' => 'application/zip',
			// documents
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			//added in jbolo 2.9.3 version
			'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
			'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xps' => 'application/vnd.ms-xpsdocument',
			// executables
			/*'exe' => 'application/octet-stream',*/
			// images
			//bmp added in 2.9.6
			'bmp' => 'image/x-ms-bmp',
			'bmp' => 'image/x-bmp',
			'gif' => 'image/gif',
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpe' => 'image/pjpeg',
			'jpeg' => 'image/jpeg',
			'jpeg' => 'image/pjpeg',
			'jpg' => 'image/jpeg',
			'jpg' => 'image/pjpeg',
			// audio
			'mp3' => 'audio/mpeg',
			'wav' => 'audio/x-wav',
			// video
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mov' => 'video/quicktime',
			'avi' => 'video/x-msvideo'
		);

		####################################################################
		###  DO NOT CHANGE BELOW
		####################################################################

		// If hotlinking not allowed then make hackers think there are some server problems
		if (ALLOWED_REFERRER !== ''
		&& (!isset($_SERVER['HTTP_REFERER']) || strpos(strtoupper($_SERVER['HTTP_REFERER']),strtoupper(ALLOWED_REFERRER)) === false)
		) {
			die("Internal server error. Please contact system administrator.");
		}

		// Make sure program execution doesn't time out
		// Set maximum script execution time in seconds (0 means no limit)
		set_time_limit(0);

		if (!isset($_GET['f']) || empty($_GET['f'])) {
			die("Please specify file name for download.");
		}
		//echo $_GET['f'];

		// Get real file name.
		// Remove any path info to avoid hacking by adding relative path, etc.
		$fname = basename($_GET['f']);

		// get full file path (including subfolders)
		$file_path = '';
		$this->find_file(BASE_DIR, $fname, $file_path);
		//echo $file_path;

		if (!is_file($file_path)) {
			die("File does not exist. Make sure you specified correct file name.");
		}

		// file size in bytes
		$fsize = filesize($file_path);
		//echo $fsize;die;
		// file extension
		$fext = strtolower(substr(strrchr($fname,"."),1));

		// check if allowed extension
		if (!array_key_exists($fext, $allowed_ext)) {
			die("Not allowed file type.");
		}

		// get mime type
		if ($allowed_ext[$fext] == '') {
			$mtype = '';
			// mime type is not set, get from server settings
			if (function_exists('mime_content_type')) {
				$mtype = mime_content_type($file_path);
			}
			else if (function_exists('finfo_file')) {
				$finfo = finfo_open(FILEINFO_MIME); // return mime type
				$mtype = finfo_file($finfo, $file_path);
				finfo_close($finfo);
			}
			if ($mtype == '') {
			$mtype = "application/force-download";
			}
		}
		else {
			// get mime type defined by admin
			$mtype = $allowed_ext[$fext];
		}

		// Browser will try to save file with this filename, regardless original filename.
		// You can override it if needed.
		if (!isset($_GET['fc']) || empty($_GET['fc'])) {
			$asfname = $fname;
		}
		else {
			// remove some bad chars
			$asfname = str_replace(array('"',"'",'\\','/'), '', $_GET['fc']);
			if ($asfname === '')
				$asfname = 'NoName';
		}

		/*
		ob_end_clean();
		ob_start();
		// set headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: $mtype");
		header("Content-Disposition: attachment; filename=\"$asfname\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . $fsize);
		// download
		// @readfile($file_path);
		$file = @fopen($file_path,"rb");
		if ($file) {
			while(!feof($file)) {
				print(fread($file, 1024*8));
				flush();
				if (connection_status()!=0) {
					@fclose($file);
					die();
				}
			}
			@fclose($file);
		}
		*/
		ob_end_clean();
		header("Cache-Control: public, must-revalidate");
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		//header("Pragma: no-cache");  // Problems with MS IE
		header("Expires: 0");
		header("Content-Description: File Transfer");
		/*header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");*/
		header("Content-Type: " . $mtype);
		header("Content-Length: ".(string)$fsize);
		header('Content-Disposition: attachment; filename="'.$asfname.'"');
		@readfile($file_path);

		// log downloads
		if (!LOG_DOWNLOADS)
			die();
		$filename='downloads_log.php';
		$path=JPATH_SITE.'/components/com_jbolo';
		$options = "{DATE}\t{USER}\t{IP}\t{FILE}";
		jimport('joomla.error.log');
		JLog::addLogger(array('text_file' => $filename,'text_entry_format'=>$options,'text_file_path' =>$path
		),JLog::INFO,'com_jbolo');
		$logEntry = new JLogEntry('File downloaded',JLog::INFO,'com_jbolo');
		$logEntry->user=JFactory::getUser()->id;
		$logEntry->downloadTimestamp=date("m.d.Y g:ia");
		$logEntry->ip=$_SERVER['REMOTE_ADDR'];
		$logEntry->file=$fname;
		JLog::add($logEntry);

		exit;

	}//function ends

	//Check if the file exists, Check in subfolders too
	function find_file ($dirname, $fname, &$file_path) {
		$dir = opendir($dirname);
		while ($file = readdir($dir)) {
			if (empty($file_path) && $file != '.' && $file != '..') {
				if (is_dir($dirname.'/'.$file)) {
					//recursion
					$this->find_file($dirname.'/'.$file, $fname, $file_path);
				}
				else {
					if (file_exists($dirname.'/'.$fname)) {
						$file_path = $dirname.'/'.$fname;
						return;
					}
				}
			}
		}
	}// find_file
}
?>