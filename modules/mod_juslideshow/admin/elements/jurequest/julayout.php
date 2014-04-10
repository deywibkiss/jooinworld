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

//Disable all error report that may break JSON result
error_reporting(0);

require_once( dirname(__FILE__) . '/juutilities.php' );

class julayout extends juutilities {
	
	/**
	* Return layout setting html 
	*/
	public function generateLayoutSettings($layout = ''){
		if (!$layout){
			$layout = JRequest::getVar('julayout');
		}
		
		$template = $this->activetemplate; 
		$modulename = $this->modulename;
		
		//Get layout params from profile(if select a profile file_name)
		if(JRequest::getVar('julayoutparams','dbparams')=='profile' && JRequest::getVar('file_name','')!='') {
			$ini_file_in_module = self::getIniFileModule(); 
			$ini_file_in_template = self::getIniFileTemplate(); 
			
			//Get data saved in profile file, profiles in module have higher priority
			if (JFILE::exists($ini_file_in_module)){
				$layoutsettings_json = file_get_contents ($ini_file_in_module);
			} elseif (JFILE::exists($ini_file_in_template)) {
				$layoutsettings_json = file_get_contents ($ini_file_in_template);
			//File does not exist
			} else {
				$layoutsettings_json = "{}";
			}
		} else {
		//Get layout params from database(Default)
			$layoutsettings_json = $this->params->get('layoutsettings','{}');
		}
		$layoutsettings = json_decode($layoutsettings_json);
		
		$theme_path_in_template = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'html' . DS . $modulename. DS . $layout;
		$theme_path_in_module = JPATH_SITE . DS . 'modules' . DS . $modulename . DS . 'tmpl'. DS . $layout;
		
		//Params in template have higher priority
		$xml_params = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'html' . DS . $modulename. DS . $layout . DS . 'params.xml';
		if (!JFile::exists($xml_params)) {
			$xml_params = JPATH_SITE . DS . 'modules' . DS . $modulename . DS . 'tmpl' . DS . $layout . DS . 'params.xml';
		}
		
		//Get all theme folders of selected layout in module
		$folders = glob($theme_path_in_module.DS.'themes'.DS.'*', GLOB_ONLYDIR);
		foreach ($folders AS $key=>$folder){
			$theme =  basename($folder);
			$themes[$theme] = $theme;
		}
		
		//Get all theme folders of selected layout in template, if they have the same theme, theme in template(higher priority) will overwrite theme in module
		$folders = glob($theme_path_in_template.DS.'themes'.DS.'*', GLOB_ONLYDIR);
		foreach ($folders AS $key=>$folder){
			$theme =  basename($folder);
			$themes[$theme] = $theme.' ['.JText::_('TEMPLATE').']';
		}
		
		//Sort themes by alphabet
		asort($themes);
		
		//Generate HTML
		//Load choosen/radio-group css/js to make new html has modal style
		$html = '
				<head>
					<script src="'.JURI::Root().'modules/'.$modulename.'/admin/js/chosen/jquery.chosen.js" type="text/javascript"></script>
					<script src="'.JURI::Root().'modules/'.$modulename.'/admin/js/radio-group.min.js" type="text/javascript"></script>
					
					<script src="'.JURI::Root().'modules/'.$modulename.'/admin/js/jufilter.min.js" type="text/javascript"></script>
					<script src="'.JURI::Root().'modules/'.$modulename.'/admin/js/jugroup.min.js" type="text/javascript"></script>
					<script type="text/javascript">
						try {
							JU_jQuery(document).ready(function (){
													JU_jQuery(\'#module-form select\').chosen({
														disable_search_threshold : 10,
														allow_single_deselect : true
													}).change(function(){
														if(typeof(validate) == \'function\') {
															validate();
														}
													});
												});
						} catch(e) {}
						window.addEvent(\'domready\', function() {
									$$(\'.hasTip\').each(function(el) {
										var title = el.get(\'title\');
										if (title) {
											var parts = title.split(\'::\', 2);
											el.store(\'tip:title\', parts[0]);
											el.store(\'tip:text\', parts[1]);
										}
									});
									var JTooltips = new Tips($$(\'.hasTip\'), { maxTitleChars: 50, fixed: false});
								});
						
						JU_jQuery("#"+layoutsettings_holder+" .jugroup").jugroup();
						
						JU_jQuery("#"+layoutsettings_holder+" .jufilter").jufilter();
					</script>
				</head>
				<body>';
		
		$html .= "<ul class='adminformlist'>";
		//Theme select list
		if (count($themes)) {
			$html .= "<li>";
			$html .= "<label id=\"jform_params_layout_theme-lbl\" for=\"jform_params_layout_theme\" class=\"hasTip\" title=\"".JText::_("LAYOUT_THEME")."::".JText::_("LAYOUT_THEME_DESC")."\">".JText::_("LAYOUT_THEME")."</label>";
			$html .= JHTML::_('select.genericlist', $themes, 'jform[params][layout_theme]', 'class="inputbox" size="1";"', '', '',(!empty($layoutsettings)) ? $layoutsettings->layout_theme : '', 'jform_params_layout_theme' );
			$html .= "</li>";
		}
		
		if (JFile::exists($xml_params)) {
			jimport( 'joomla.form.form' );
			$options = array("control" => "jform");
			$paramsForm = JForm::getInstance('jform', $xml_params, $options);
			// bind ini data
			if (!empty($layoutsettings)){
				$paramsForm->bind(array('params'=>$layoutsettings));
			}
			
			//Only load filesets named 'params' from params xml file
			$fieldSets = $paramsForm->getFieldsets('params');
			
			foreach ($fieldSets as $name => $fieldSet) {
				if (isset($fieldSet->description) && trim($fieldSet->description)){
					$html .= '<p class="tip">'.JText::_($fieldSet->description).'</p>';
				}
					
				$hidden_fields = '';
				foreach ($paramsForm->getFieldset($name) as $field){
					if (!$field->hidden){ 
						$html .= "<li>";
						$html .= $field->label;
						$html .= $field->input;
						$html .= "</li>";
					}else { 
						$hidden_fields.= $field->input;
					}
					
				}
				$html .= $hidden_fields; 
			}
		}
		$html .= "</ul>
				</body>";
		
		if(!count($themes) && !count($fieldSets)) {
			$html = '';
		}
		
		$data['success'] = 1;
		$data['layoutsetting'] = $html;
		return $data;
	}
}
?>