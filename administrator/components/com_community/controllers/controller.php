<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Base Controller
 */
class CommunityController extends JControllerLegacy
{
	public function __construct()
	{
		parent::__construct();

		// Only process this if task != azrul_ajax
		$task	= JRequest::getCmd( 'task' , '' );

		// Add some javascript that may be needed
		$document	= JFactory::getDocument();

		if( $task != 'azrul_ajax' )
		{
                        $document->addScript( COMMUNITY_BASE_ASSETS_URL . '/jqueryui/datepicker/js/jquery-ui-1.9.2.custom.js' );
                        $document->addStyleSheet(COMMUNITY_BASE_ASSETS_URL . '/jqueryui/datepicker/css/ui-lightness/jquery-ui-1.9.2.custom.css');
			$document->addScript( COMMUNITY_BASE_ASSETS_URL . '/joms.jquery-1.8.1.min.js' );
			$document->addScript( COMMUNITY_BASE_ASSETS_URL . '/window-1.0.js' );
			$document->addScript( COMMUNITY_ASSETS_URL . '/admin.js' );

			//dont load in installer
			if(JRequest::getCmd('view','community') !== 'installer')
			{
				require_once JPATH_COMPONENT.'/helpers/community.php';
				CommunityHelper::addSubmenu(JRequest::getCmd( 'view' , 'community' ));
			}
		}
		// Attach the Front end Window CSS
		$css		= rtrim( JURI::root() , '/' ) . '/components/com_community/assets/window.css';
		$document->addStyleSheet( $css );

		$version = new JVersion();
		// Attach the back end css
		if($version->getHelpVersion() <='0.25')
		{
			$css		= COMMUNITY_ASSETS_URL . '/default.css';
		}
		else
		{
			$css		= COMMUNITY_ASSETS_URL . '/default.joomla3.css';
		}

		$document->addStyleSheet( $css );

	}

	/**
	 * Method to display the specific view
	 *
	 **/
	public function display($cachable = false, $urlparams = array())
	{
		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Set the default layout and view name
		$layout		= JRequest::getCmd( 'layout' , 'default' );

		// Get the document object
		$document	= JFactory::getDocument();

		// Get the view type
		$viewType	= $document->getType();

		// Get the view
		$view		= $this->getView( $viewName , $viewType );

		$model		= $this->getModel( $viewName );

		if( $model )
		{
			$view->setModel( $model , $viewName );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Display the view
		$view->display();

		// Display Toolbar. View must have setToolBar method
		if( method_exists( $view , 'setToolBar') )
		{
			$view->setToolBar();
		}
	}

	/**
	 * Save the publish status
	 *
	 * @access public
	 *
	 **/
	public function savePublish( $tableClass = 'CommunityTable' )
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		// Determine the view.
		$viewName	= JRequest::getCmd( 'view' , 'configuration' );

		// Determine whether to publish or unpublish
		$state	= ( JRequest::getWord( 'task' , '' ) == 'publish' ) ? 1 : 0;

		//$id		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$id			= $jinput->post->get( 'cid', array(), 'array' );

		$count	= count($id);

		$table	= JTable::getInstance( $viewName , $tableClass );
		$table->publish( $id , $state );

		switch ($state)
		{
			case 1:
				$message = JText::sprintf('Item(s) successfully Published', $count);
				break;
			case 0:
				$message = JText::sprintf('Item(s) successfully Unpublished', $count);
				break;
		}
		$mainframe->redirect( 'index.php?option=com_community&view=' . $viewName , $message );
	}

	/**
	 * AJAX method to toggle publish status
	 *
	 * @param	int	id	Current field id
	 * @param	string field	The field publish type
	 *
	 * @return	JAXResponse object	Azrul's AJAX Response object
	 **/
	public function ajaxTogglePublish( $id, $field , $viewName )
	{
		$user	= JFactory::getUser();

		// @rule: Disallow guests.
		if ( $user->get('guest'))
		{
			JError::raiseError( 403, JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN') );
			return;
		}

		$response	= new JAXResponse();

		// Load the JTable Object.
		$row	= JTable::getInstance( $viewName , 'CommunityTable' );
		$row->load( $id );

		if( $row->$field == 1)
		{
			$row->$field	= 0;
			$row->store();
			$image			= 'publish_x.png';
		}
		else
		{
			$row->$field	= 1;
			$row->store();
			$image			= 'tick.png';
		}
		// Get the view
		$view		= $this->getView( $viewName , 'html' );

		$html	= $view->getPublish( $row , $field , $viewName . ',ajaxTogglePublish' );

	   	$response->addAssign( $field . $id , 'innerHTML' , $html );

	   	return $response->sendResponse();
	}

	public function cacheClean($cacheId){
		$cache = CFactory::getFastCache();

		$cache->clean($cacheId);
	}
}
