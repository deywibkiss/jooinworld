<?php // No direct accessdefined( '_JEXEC' ) or die( 'Restricted access' );require_once( JPATH_COMPONENT . DS . 'helpers' . DS . 'referidos.php');require_once( JPATH_COMPONENT . DS . 'controller.php' );//incluir clase para cargar recursos de administradorrequire_once( JPATH_COMPONENT . DS . 'helpers' . DS . 'style.php' );require_once( JPATH_COMPONENT . DS . 'helpers' . DS .  'misc.php' );require_once( JPATH_COMPONENT . DS . 'helpers' . DS .  'paginador.php' );//incluir el modelo base donde extienden los demas modelosJModelLegacy::addIncludePath( JPATH_COMPONENT_SITE . DS . 'models' , 'ReferidosModel');$model_ref_default = JModelLegacy::getInstance('Default', 'ReferidosModel');//incluir el archivo de la clase pagorequire_once( JPATH_COMPONENT_SITE . DS . 'helpers/pagos.php' ); //cargo los archivos vinculados de administradorReferidosHelperAdmin::load();// Access check.if (!JFactory::getUser()->authorise('core.manage', 'com_referidos')) {	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));} $controller = JControllerLegacy::getInstance('Referidos');$controller->execute(JRequest::getCmd('task'));$controller->redirect();