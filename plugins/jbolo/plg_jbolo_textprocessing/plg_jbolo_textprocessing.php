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

jimport('joomla.registry.registry');
jimport('joomla.filesystem.file');
jimport('joomla.html.parameter');

if (!JFile::exists(JPATH_ROOT.DS.'components'.DS.'com_jbolo'.DS.'jbolo.php')) {
	return false;
}

/*load language file for plugin frontend*/
$lang=JFactory::getLanguage();
$lang->load('com_jbolo',JPATH_SITE);

class plgJboloplg_jbolo_textprocessing extends JPlugin
{
	function processUrls($text)
	{
		$text = preg_replace("#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie","'<a href=\"$1\" target=\"_blank\">$1</a>$4'",$text);
		return $text;
	}

	function processDownloadLink($text,$particularUID)
	{
		/*$text = preg_replace("#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie","'<a href=\"$1\" target=\"_blank\">".JText::_('COM_JBOLO_CLICK_TO_DOWNLOAD')."</a>$4'",$text);*/
		if($particularUID){
			$msg=JText::_('COM_JBOLO_YOU_SENT_FILE').' ';
			$downloadLink=JURI::base().'index.php?option=com_jbolo&controller=sendfile&action=downloadFile&f='.$text;
			$text=$msg.'<a href="'.$downloadLink.'" target="_blank">'.JText::_('COM_JBOLO_CLICK_TO_DOWNLOAD').'</a>';
		}else{
			$msg=JText::_('COM_JBOLO_I_SENT_FILE').' ';
			$downloadLink=JURI::base().'index.php?option=com_jbolo&controller=sendfile&action=downloadFile&f='.$text;
			$text=$msg.'<a href="'.$downloadLink.'" target="_blank">'.JText::_('COM_JBOLO_CLICK_TO_DOWNLOAD').'</a>';
		}
		return $text;
	}

	function processSmilies($text)
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$template=$params->get('template');
		$smiliesfile=JFile::read(JPATH_COMPONENT.DS.'jbolo'.DS.'assets'.DS.'smileys.txt');
		$smilies=explode("\n",$smiliesfile);
		foreach($smilies as $smiley)
		{
			if(trim($smiley)==''){
				continue;
			}
			$pcs=explode('=',$smiley);
			$img=JURI::base().'components/com_jbolo/jbolo/view/'.$template.'/images/smileys/default/'.$pcs[1];
			$imgsrc = "<img src=\"{$img}\" border=\"0\" />";
			$text=str_replace($pcs[0],$imgsrc,$text);
		}
		return $text;
	}

	function processBadWords($text)
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$badwords=$params->get('badwords');
		$badwords = str_replace(' ', '', $badwords);
		if($badwords!=null)
		{
			$badwords = explode(",",$badwords);
			for($i = 0; $i<sizeof($badwords); $i++)
			{
				//$badwords[$i] = '/'.$badwords[$i].'/i'; //replace all ouccrances
				$badwords[$i] = '/\b'.$badwords[$i].'\b/i'; // replace only full words
			}
			$replacement = '***';
			$text = preg_replace($badwords, $replacement, $text);
			return $text;
		}
		return $text;
	}
}
?>