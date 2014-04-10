<?php
/**
 * @version		$Id$
 * @author		Joomseller!
 * @package		Joomla.Site
 * @subpackage	mod_jse_megamenu
 * @copyright	Copyright (C) 2008 - 2013 by Joomseller. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL version 3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (dirname(__FILE__).'/helper.php');

if(!defined('DEMO_MODE')) {
	define('DEMO_MODE', 0);
}
if(DEMO_MODE) {
	$input = JFactory::getApplication()->input;
	$data = $input->post->get('megamenu_demo_form', array(), 'array');
	
	$properties = $params->toArray();
	foreach($properties as $key => $value) {
		$params->set($key, isset($data[$key]) ? $data[$key] : $value);
	}
}

$menutype 	= $params->get('menutype', 'mainmenu');

//Main navigation
$params->def('menutype',            $menutype);
$params->def('menu_images',			$params->get('menu_images', 1));
$params->def('menu_images_align',	'left');
$params->def('menupath',			'modules/mod_jse_megamenu');
$params->def('menu_title',			0);

$responsive	= $params->get('responsive_menu',	'1');
$layout		= $params->get('layout', 'default');

$hozorver	= $params->get('hozorver', 'horizontal');
$menuStyle	= 'megamenu';
if($hozorver == 'horizontal') {
	$menuStyle	.= ' horizontal ';
    $menuStyle  .= $params->get('horizontal_menustyle', 'left');
} else {
    $menuStyle	.= ' vertical ';
    if($params->get('vertical_submenu_direction', 'lefttoright') == 'lefttoright') {
        $menuStyle	.= 'left';
    } else {
		$menuStyle	.= 'right';
	}
}

$menuStyle	.= " $layout";

$document = &JFactory::getDocument();

$document->addStyleSheet('modules/mod_jse_megamenu/assets/css/style.css');
$document->addStyleSheet('modules/mod_jse_megamenu/assets/css/style/'.$layout.'.css');

if($responsive){
    $document->addStyleSheet('modules/mod_jse_megamenu/assets/css/style_responsive.css');
}

// if use CSS3 only, disable mootools and mega menu script
if ($params->get('css3_noJS', 0)) {
	$menuStyle	.= ' noJS';
} else { // Use mootools libraries and enable mega menu script
	JHTML::_('behavior.framework', true);
	JHTML::_('behavior.tooltip', true);
	$document->addScript('modules/mod_jse_megamenu/assets/js/HoverIntent.js');
	$document->addScript('modules/mod_jse_megamenu/assets/js/script.js');
}

$dropdownmenu    = new Mod_JSE_MegaMenu($params);
require(JModuleHelper::getLayoutPath('mod_jse_megamenu'));