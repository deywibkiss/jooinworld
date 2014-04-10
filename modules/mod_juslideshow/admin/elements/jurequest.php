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

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');

class JFormFieldJURequest extends JFormField {
    protected $type = 'JURequest';
	
	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getLabel() {
		return '<div id="'.$this->id.'" style="display: none;"></div>';
	}
	
    protected function getInput() {
		//Auto remove the field itself
		$document = JFactory::getDocument();
		$document->addScriptDeclaration("JU_jQuery(document).ready(function(\$){\$('#".$this->id."').parent().remove();});");
		$task = JRequest::getString('task', '');
		$jurequest = strtolower(JRequest::getString('jurequest'));
		
        if ($jurequest && $task) {
			//Load class file
			require_once( dirname(dirname(dirname(__FILE__))) . '/admin/elements/jurequest/' . $jurequest . '.php' );
            $obLevel = ob_get_level();
			if($obLevel){
				while ($obLevel > 0 ) {
					ob_end_clean();
					$obLevel --;
				}
			}else{
				ob_clean();
			}
			
			$params = new JRegistry($this->form->getValue('params'));
            $obj = new $jurequest($params);
			//Execute task
			$data = $obj->$task();
			echo json_encode($data);
            exit;
        }
    }    
    
}