-- Table structure for table `#__jbolo_chat_msgs`
CREATE TABLE IF NOT EXISTS `#__jbolo_chat_msgs` (
  `msg_id` int(11) NOT NULL auto_increment COMMENT 'Msg id. Primary key',
  `from` int(11) NOT NULL COMMENT 'who sent this msg?  Primary key of table #__jbolo_users',
  `to_node_id` int(11) NOT NULL COMMENT 'to which node this message was sent? Primary key of table #__jbolo_nodes',
  `msg` text NOT NULL COMMENT 'message text',
  `msg_type` varchar(20) NOT NULL COMMENT 'message type - text/file etc',
  `time` datetime NOT NULL COMMENT 'timestamp when message was stored',
  `sent` tinyint(1) NOT NULL COMMENT 'message sent or not?',
  PRIMARY KEY  (`msg_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Table structure for table `#__jbolo_chat_msgs_xref`
CREATE TABLE IF NOT EXISTS `#__jbolo_chat_msgs_xref` (
  `msg_id` int(11) NOT NULL COMMENT 'Msg id. Primary key of table #__jbolo_chat_msgs',
  `node_id` int(11) NOT NULL COMMENT 'To which node this message was sent? Primary key of table #__jbolo_nodes',
  `to_user_id` int(11) NOT NULL COMMENT 'Particpant id of this chat. Primary key of table #__jbolo_users',
  `delivered` tinyint(1) NOT NULL COMMENT 'Was message sent from server to node user?',
  `read` tinyint(1) NOT NULL COMMENT 'Was message sent from server received by node user/'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Table structure for table `#__jbolo_nodes`
CREATE TABLE IF NOT EXISTS `#__jbolo_nodes` (
  `node_id` int(11) NOT NULL auto_increment COMMENT 'Node id. Primary key',
  `title` varchar(255) default NULL COMMENT 'Title of chat node',
  `type` tinyint(1) NOT NULL COMMENT 'Chat type. 1-One to one, 2-Group chat',
  `owner` int(11) NOT NULL COMMENT 'Who created this node?  Primary key of table #__jbolo_users',
  `time` datetime NOT NULL COMMENT 'Time when chat node created',
  PRIMARY KEY  (`node_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Table structure for table `#__jbolo_node_users`
CREATE TABLE IF NOT EXISTS `#__jbolo_node_users` (
  `node_id` int(11) NOT NULL COMMENT 'Node id. Primary key of table #__jbolo_nodes',
  `user_id` int(11) NOT NULL COMMENT 'Chat node participant. Primary key of table #__jbolo_users',
  `status` tinyint(1) NOT NULL COMMENT 'Status. Indicates if user has left chat node or not.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Table structure for table `#__jbolo_privacy`
CREATE TABLE IF NOT EXISTS `#__jbolo_privacy` (
  `id` int(11) NOT NULL auto_increment,
  `blocked_by_user_id` int(11) NOT NULL,
  `blocked_user_id` int(11) NOT NULL,
  `blocked_in_node_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Table structure for table `#__jbolo_users`
CREATE TABLE IF NOT EXISTS `#__jbolo_users` (
  `user_id` int(11) NOT NULL COMMENT 'joomla user id',
  `chat_status` tinyint(1) NOT NULL COMMENT 'Chat status - tiny integer value',
  `status_msg` varchar(255) NOT NULL COMMENT 'status message e.g. Hola Chica'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;