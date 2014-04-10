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
if(JVERSION<'3.0'){
	//load techjoomla bootstrapper
	include_once JPATH_ROOT.DS.'media'.DS.'techjoomla_strapper'.DS.'strapper.php';
	TjAkeebaStrapper::bootstrap();
}
//load jbolo css
$document=JFactory::getDocument();
$document->addStyleSheet(JURI::root().DS.'components'.DS.'com_jbolo'.DS.'css'.DS.'jboloapi.css');
$document->addStyleSheet(JURI::root().DS.'components'.DS.'com_jbolo'.DS.'css'.DS.'pure-css-speech-bubbles.css');
?>
<div class="techjoomla-bootstrap jbolo_word_break_wrap jbolo_pad_me">
	<?php
		if($this->isParticipant !=1 && $this->isParticipant !=2)
		{
			echo '<div class="well">
					<div class="alert alert-error">';
			if(!$this->isParticipant)//not participant
				echo JText::_('COM_JBOLO_NON_MEMBER_MSG');
			elseif($this->isParticipant==3)//not logged in
				echo JText::_('COM_JBOLO_LOGIN_HISTORY');
			echo '</div>
			</p>
			</div> <!--End bootsrap div-->';
		}
		else
		{
		?>
			<form action="" method="post" name="adminForm" id="adminForm">
				<input type="hidden" name="option" value="com_jbolo" />
				<input type="hidden" name="view" value="history" />
				<input type="hidden" name="nid" value="<?php echo $this->nid; ?>" />

				<div class="well">
					<?php
					if(empty($this->history))
					{
						?>
						<p class="text-error" >
							<?php echo JText::_('COM_JBOLO_NO_HISTORY_MSG');?>
						</p>
						<?php
					}
					else
					{
						if(JVERSION >= 3.0 )
						{
							?>
								<div class="btn-group pull-right">
									<?php echo $this->pagination->getLimitBox(); ?>
								</div>
							<?php
						}
						$date_temp=false;
						foreach($this->history as $h)
						{
							$date=JFactory::getDate($h->ts);
							//j shows day in number from 1 to 31
							if(!$date_temp || $date_temp!=$date->format('j'))
							{
								$date_temp=$date->format('j');
								?>
								<strong>
									<?php echo $date->format(JText::_('COM_JBOLO_HISTORY_HEADER_DATE_FORM'));?>
								</strong>
								<hr/>
								<?php
							}
							$user=JFactory::getUser();
							if($h->fid == $user->id)
							{
								?>
								<div class="triangle-border right">
									<div style="float:right;" >
										<span style="font-weight:bold;"><?php echo JText::_('COM_JBOLO_ME'); ?></span>
										<span style="font-style:italic; font-size:9px;"><?php echo JFactory::getDate($h->ts)->Format(JText::_('COM_JBOLO_HISTORY_SENT_DATE_FORM')); ?></span>
									</div>
									<div class="clearfix"></div>
									<hr class="hr-condensed"/>
									<div><?php echo $h->msg; ?></div>
									<div class="clearfix"></div>
								</div>
								<?php
							}
							else if($h->fid !=0)
							{
								?>
								<div class="triangle-border left" >
									<span style="font-weight:bold;"><?php echo $h->uname; ?></span>
									<span style="font-style:italic; font-size:9px;"><?php echo JFactory::getDate($h->ts)->Format(JText::_('COM_JBOLO_HISTORY_SENT_DATE_FORM')); ?></span>
									<div class="clearfix"></div>
									<hr class="hr-condensed"/>
									<div style="float:right;"><?php echo $h->msg; ?></div>
									<div class="clearfix"></div>
								</div>
								<?php
							}
							else
							{
								?>
								<p class="text-warning" style="text-align:center;">
									<?php echo $h->msg; ?>
								</p>
								<?php
							}
							?>
							<div class="clearfix"></div>
							<?php
						}
							?>
							<!-- Display Pagination -->
							<div class="pagination" style="text-align:center;">
								<p class="counter pull-right">
									<?php echo $this->pagination->getPagesCounter(); ?>
								</p>
								<?php echo $this->pagination->getPagesLinks(); ?>
							</div>
						<?php
					}//end else
						?>
				</div><!--End well div-->
			</form>
		</div> <!--End bootsrap div-->
		<?php
		}
	?>