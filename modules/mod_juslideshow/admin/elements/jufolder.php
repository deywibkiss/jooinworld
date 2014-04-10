<?php
/**
 * ------------------------------------------------------------------------
 * JU Backend Toolkit for Joomla 2.5/3.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2010-2013 JoomUltra. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: JoomUltra Co., Ltd
 * Websites: http://www.joomultra.com
 * ------------------------------------------------------------------------
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access');

JImport('joomla.form.formfield');
JImport('joomla.filesystem.folder');

class JFormFieldJUFolder extends JFormField {

    protected $type = 'JUFolder';

    public function getInput() {        
        $juFolder = array();
		$juFolder[0] = new stdClass();
        $juFolder[0]->name = 'images';
        $juFolder[0]->text = 'images';
        $juFolder[0]->value = 'images/';
        //Read image folder to build folder tree
        $this->buildTree('images', 0, '', $juFolder);

        // Initialize field attributes.
        $class = $this->element['class'] ? (string) $this->element['class'] : '';
        $attr = '';
        $attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
        $attr .= ' class="inputbox ' . $class . '"'; 
		
		//Remove first character: "/"
        if(substr($this->value, 0, 1) =='/'){
        	$this->value = substr($this->value, 1);
        }
		
		//Add "/" to the end
		if(substr($this->value, -1) != '/'){
			$this->value = $this->value . '/';
		}
		
		$html = JHTML::_('select.genericlist', $juFolder, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		
	   return $html;        
    }

    public function buildTree($folder, $depth, $path, &$juFolder) {
        if($path){
			$folder = $path . '/' . $folder;
		}
        $subs = JFolder::folders(JPATH_ROOT . '/' . $folder);
		if(count($subs)){
			foreach ($subs as $sub) {
				$obj = new stdClass();
				$obj->name = $sub;
				$obj->text = str_repeat('- - ', $depth + 1) . $sub;
				$obj->value = $folder . '/' . $sub . '/';
				$juFolder[] = $obj;
				$this->buildTree($sub, $depth + 1, $folder, $juFolder);
			}
		}
    }

}