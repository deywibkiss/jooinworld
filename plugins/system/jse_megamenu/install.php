<?php
 /*
 * @version		$Id$
 * @author		Joomseller!
 * @package		Joomla!
 * @subpackage	MegaMenu
 * @copyright	Copyright (C) 2008 - 2013 by Joomseller Solutions. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html
 */

// No direct access
defined('_JEXEC') or die;


class plgSystemJSE_MegamenuInstallerScript {
    function postflight($type, $parent)
    {
        $db = JFactory::getDBO();
        //Get this plugin groupn, element
        $group = 'system';
        $element = 'jse_megamenu';
        //enable plugin and update ordering to 1000 (great enough to be last ordering)
        $query = 'update `#__extensions`'
               . ' set `enabled`=1'
               . ' WHERE folder = ' . $db->Quote($group)
               . ' AND element = ' . $db->Quote($element);
        $db->setQuery($query);
        try {
            $db->Query();
        } catch ( JException $e ) {
            // Return warning message that cannot update order
            echo JText::_('MEGAMENU_INSTALL_FAIL_UPDATE_ORDER');
        }
    }
}