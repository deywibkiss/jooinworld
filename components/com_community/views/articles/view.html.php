<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');
jimport( 'joomla.utilities.arrayhelper');
jimport( 'joomla.html.html');

class CommunityViewArticles extends CommunityView
{

	public $article;

	public function _addSubmenu()
	{
		$mySQLVer	= 0;

		if(JFile::exists(JPATH_COMPONENT.'/libraries/advancesearch.php'))
		{
			require_once (JPATH_COMPONENT.'/libraries/advancesearch.php');
			$mySQLVer	= CAdvanceSearch::getMySQLVersion();
		}

		$this->addSubmenuItem('index.php?option=com_community&view=friends', JText::_('COM_COMMUNITY_FRIENDS_VIEW_ALL'));

		$tmpl = new CTemplate();
		$tmpl->set( 'url', CRoute::_('index.php?option=com_community&view=search') );
		$html = $tmpl->fetch( 'search.submenu' );
		$this->addSubmenuItem('index.php?option=com_community&view=search', JText::_('COM_COMMUNITY_SEARCH_FRIENDS'), 'joms.videos.toggleSearchSubmenu(this)', SUBMENU_LEFT, $html);

		if($mySQLVer >= 4.1 )
		{
			$this->addSubmenuItem('index.php?option=com_community&view=search&task=advancesearch', JText::_('COM_COMMUNITY_CUSTOM_SEARCH'));
		}
		$this->addSubmenuItem('index.php?option=com_community&view=friends&task=invite', JText::_('COM_COMMUNITY_INVITE_FRIENDS'));
		$this->addSubmenuItem('index.php?option=com_community&view=friends&task=sent', JText::_('COM_COMMUNITY_FRIENDS_REQUEST_SENT'));
		$this->addSubmenuItem('index.php?option=com_community&view=friends&task=pending', JText::_('COM_COMMUNITY_FRIENDS_PENDING_APPROVAL'));
	}

	public function showSubmenu()
	{
		//$this->_addSubmenu();
		parent::showSubmenu();
	}

	/**
	 * DIsplay list of friends
	 *
	 * if no $_GET['id'] is set, we're viewing our own friends
	 */
	public function friends($data = null)
	{
		// Load necessary window css / javascript headers.
		CWindow::load();

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$document = JFactory::getDocument();
		$my	= CFactory::getUser();
		$id = $jinput->get('userid',$my->id , 'INT');

		// Display mini header if user is viewing other user's friend
		if( $id != $my->id )
		{

			$this->attachMiniHeaderUser( $id );
		}

		$feedLink = CRoute::_('index.php?option=com_community&view=friends&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_FRIENDS_FEEDS') . '" href="'.$feedLink.'"/>';
		$document->addCustomTag( $feed );

		$user			= CFactory::getUser($id);
		$params			= $user->getParams();
		$people			= CFactory::getModel( 'search' );
		$userModel		= CFactory::getModel( 'user' );
		$avatar			= CFactory::getModel( 'avatar' );
		$friends		= CFactory::getModel( 'friends' );
		$sorted			= $jinput->get->get('sort' , 'latest', 'STRING');
		$filter			= JRequest::getWord( 'filter' , 'all' , 'GET' );

		$rows 		= $friends->getFriends( $id , $sorted , true , $filter );
		$isMine		= ( ($id == $my->id) && ($my->id != 0) );
		$document	= JFactory::getDocument();

		$this->addPathway(JText::_('COM_COMMUNITY_FRIENDS'), CRoute::_('index.php?option=com_community&view=friends'));

		if( $my->id == $id )
		{
			$this->addPathway(JText::_('COM_COMMUNITY_FRIENDS_MY_FRIENDS') );
		}
		else
		{
			$this->addPathway(JText::sprintf('COM_COMMUNITY_FRIENDS_ALL_FRIENDS', $user->getDisplayName()));
		}

		// Hide submenu if we are viewing other's friends
		if( $isMine )
		{
			$this->showSubmenu();
			//$document->setTitle(JText::_('COM_COMMUNITY_FRIENDS_MY_FRIENDS'));
		}
		else
		{
			$this->addSubmenuItem('index.php?option=com_community&view=profile&userid=' . $user->id , JText::_('COM_COMMUNITY_PROFILE_BACK_TO_PROFILE'));
			$this->addSubmenuItem('index.php?option=com_community&view=friends&userid=' . $user->id , JText::_('COM_COMMUNITY_FRIENDS_VIEW_ALL'));
			$this->addSubmenuItem('index.php?option=com_community&view=friends&task=mutualFriends&userid=' . $user->id . '&filter=mutual', JText::_('COM_COMMUNITY_MUTUAL_FRIENDS'));

			$tmpl = new CTemplate();
			$tmpl->set('view',"friends");
			$tmpl->set( 'url', CRoute::_('index.php?option=com_community&view=friends&task=viewfriends') );
			$html = $tmpl->fetch( 'friendsearch.submenu' );
			$this->addSubmenuItem('index.php?option=com_community&view=friends&task=viewfriends', JText::_('COM_COMMUNITY_SEARCH_FRIENDS'), 'joms.videos.toggleSearchSubmenu(this)', SUBMENU_LEFT, $html);

			parent::showSubmenu ();

			$document->setTitle(JText::sprintf('COM_COMMUNITY_FRIENDS_ALL_FRIENDS', $user->getDisplayName()));
		}


		$config	= CFactory::getConfig();

		$this->getArticle();

		$tmpl	=   new CTemplate();
		$html	=   $tmpl   ->set( 'isMine'	, $isMine )
				    ->setRef( 'my'	, $my )
				    ->setRef( 'view', $this )
				    ->set( 'config'	, CFactory::getConfig() )
				    ->fetch('articles.show');

		echo $html;
	}


	public function getArticle(){

		$model = CFactory::getModel( 'friends' );

		$article_id = JRequest::getVar( 'article_id' );

		$this->article = $model->getArticle( $article_id );
	}
}
