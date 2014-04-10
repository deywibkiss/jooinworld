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

JHTML::_('behavior.tooltip');
JHTML::_('behavior.framework');
JHTML::_('behavior.modal');
//load style sheet
$document=JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components/com_jbolo/css/jbolo.css');
?>
<!--load google chart js-api-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<script type="text/javascript">
	function vercheck(){
		callXML('<?php echo $this->version; ?>');
	}
	function callXML(currversion)
	{
		if (window.XMLHttpRequest){
			xhttp=new XMLHttpRequest();
		}
		else/*Internet Explorer 5/6*/
		{
			xhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xhttp.open("GET","<?php echo JURI::base(); ?>index.php?option=com_jbolo&task=getVersion",false);
		xhttp.send("");
		latestver=xhttp.responseText;
		if(latestver!='')
		{
			if(currversion === latestver){
				jQuery('#newVersionChild').html('<span class="label label-success">'+'<?php echo JText::_("COM_JBOLO_HAVE_LATEST_VER");?>: '+latestver+'<span>');
			}
			else{
				jQuery('#newVersionChild').html('<span class="label label-important">'+'<?php echo JText::_("COM_JBOLO_NEW_VER_AVAIL");?>: '+latestver+'<span>');
			}
		}else{
			jQuery('#newVersionChild').html('<span class="label label-important">'+'<?php echo JText::_("COM_JBOLO_ERROR_NEW_VERSION");?><span>');
		}
	}
</script>

<div class="techjoomla-bootstrap"><!--START techjoomla-bootstrap-->
	<div class="row-fluid">
		<div class="span8">
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<?php
						//draw chart
						if($this->nodeTypesArray['one2oneChatsCount'] || $this->nodeTypesArray['groupChatsCount'])
						{
							?>
							<script type="text/javascript">
								google.load("visualization", "1", {packages:["corechart"]});
								google.setOnLoadCallback(drawChart);
								function drawChart() {
									var data = google.visualization.arrayToDataTable([
										['<?php echo JText::_("COM_JBOLO_NODES");?>', '<?php echo JText::_("COM_JBOLO_NUMBER_OF_NODES");?>'],
										['<?php echo JText::_("COM_JBOLO_1TO1_NODES");?>',<?php echo $this->nodeTypesArray['one2oneChatsCount'];?>],
										['<?php echo JText::_("COM_JBOLO_GROUPCHAT_NODES");?>',<?php echo $this->nodeTypesArray['groupChatsCount'];?>]
									]);
									var options = {
										title:'<?php echo JText::_("COM_JBOLO_NODES_DIVISION");?>',
										slices: {
											0: {color:'#FABB3D'},
											1: {color:'#78CD51'}
										},
										backgroundColor:'transparent'
									};
									var chart = new google.visualization.PieChart(document.getElementById('chart_div2'));
									chart.draw(data, options);
								}
							</script>
							<div id="chart_div2" style="width:100%;height:150px;"></div>
							<?php
						}
						else{
							echo '<div><strong>'.JText::_('COM_JBOLO_NODES_DIVISION').'</strong></div>';
							echo '<div class="alert alert-warning">'.JText::_('COM_JBOLO_NO_DATA_FOUND').'</div>';
						}
						?>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<?php
						//draw chart
						if($this->messageTypesArray['txtMsgs'] || $this->messageTypesArray['fileMsgs'])
						{
							?>
							<script type="text/javascript">
								google.load("visualization", "1", {packages:["corechart"]});
								google.setOnLoadCallback(drawChart);
								function drawChart() {
									var data = google.visualization.arrayToDataTable([
										['<?php echo JText::_("COM_JBOLO_MSG_TYPE");?>','<?php echo JText::_("COM_JBOLO_NUMBER_OF_MSGS");?>'],
										['<?php echo JText::_("COM_JBOLO_TXT_MSGS");?>',<?php echo $this->messageTypesArray['txtMsgs'];?>],
										['<?php echo JText::_("COM_JBOLO_FILE_MSGS");?>',<?php echo $this->messageTypesArray['fileMsgs'];?>]
									]);
									var options = {
										title:'<?php echo JText::_("COM_JBOLO_MSGS_DIVISION");?>',
										slices: {
											0: {color:'#67C2EF'},
											1: {color:'#78CD51'}
										},
									backgroundColor:'transparent'
									};
									var chart = new google.visualization.PieChart(document.getElementById('chart_div3'));
									chart.draw(data, options);
								}
							</script>
							<div id="chart_div3" style="width:100%;height:150px;"></div>
						<?php
						}
						else{
							echo '<div><strong>'.JText::_('COM_JBOLO_MSGS_DIVISION').'</strong></div>';
							echo '<div class="alert alert-warning">'.JText::_('COM_JBOLO_NO_DATA_FOUND').'</div>';
						}
						?>
					</div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span12" style="width:99%">
					<div class="well">
						<?php
						//draw chart
						if($this->messagesPerDayArray)
						{
						?>
							<script type="text/javascript">
								google.load("visualization", "1", {packages:["corechart"]});
								google.setOnLoadCallback(drawChart);
								function drawChart() {
									var data = google.visualization.arrayToDataTable([
										['<?php echo JText::_("COM_JBOLO_DATE");?>', '<?php echo JText::_("COM_JBOLO_CHAT_MSGS_COUNT");?>'],
										<?php
										foreach($this->messagesPerDayArray as $mpd){
											echo "['".$mpd->date."',".$mpd->count."],";
										}
										?>
									]);
									var options = {
										title: '<?php echo JText::_("COM_JBOLO_CHAT_MSGS_EXCHANGED");?>',
										vAxis: {title:'<?php echo JText::_("COM_JBOLO_CHAT_MSGS_COUNT");?>'},
										hAxis: {title:'<?php echo JText::_("COM_JBOLO_DATE");?>'},
										backgroundColor:'transparent',
									};
									/*var chart = new google.visualization.LineChart(document.getElementById('chart_div4'));
									chart.draw(data, options);*/
									var chart = new google.visualization.AreaChart(document.getElementById('chart_div4'));
									chart.draw(data, options);
								}
							</script>
							<div id="chart_div4" style="width:auto;height:350px;"></div>
						<?php
						}
						else{
							echo '<div><strong>'.JText::_('COM_JBOLO_CHAT_MSGS_EXCHANGED').'</strong></div>';
							echo '<div class="alert alert-warning">'.JText::_('COM_JBOLO_NO_DATA_FOUND').'</div>';
						}
						?>
					</div>
				</div>
			</div>
		</div>

		<div class="span4">
			<div class="well well-small">
				<div class="module-title nav-header">
					<?php
					if(JVERSION >= '3.0')
						echo '<i class="icon-comments-2"></i>';
					else
						echo '<i class="icon-comment"></i>';
					?> <strong><?php echo JText::_('COM_JBOLO'); ?></strong>
				</div>
				<hr class="hr-condensed"/>

				<div class="row-fluid">
					<div class="span12 alert alert-success"><?php echo JText::_('COM_JBOLO_INTRO'); ?></div>
				</div>

				<div class="row-fluid">
					<div class="span12">
						<p class="pull-right"><span class="label label-info"><?php echo JText::_('COM_JBOLO_LINKS'); ?></span></p>
					</div>
				</div>

				<div class="row-striped">
					<div class="row-fluid">
						<div class="span12">
							<a href="http://techjoomla.com/table/documentation-for-jbolo/" target="_blank"><i class="icon-file"></i> <?php echo JText::_('COM_JBOLO_DOCS');?></a>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<a href="http://techjoomla.com/documentation-for-jbolo/jbolo-faqs.html" target="_blank">
								<?php
								if(JVERSION >= '3.0')
									echo '<i class="icon-help"></i>';
								else
									echo '<i class="icon-question-sign"></i>';
								?>
								<?php echo JText::_('COM_JBOLO_FAQS');?>
							</a>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<a href="http://techjoomla.com/jbolo.-chat-for-cb-jomsocial-joomla/feed/rss.html" target="_blank">
								<?php
								if(JVERSION >= '3.0')
									echo '<i class="icon-feed"></i>';
								else
									echo '<i class="icon-bell"></i>';
								?> <?php echo JText::_('COM_JBOLO_RSS');?></a>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<a href="http://techjoomla.com/index.php?option=com_billets&view=tickets&layout=form&Itemid=18" target="_blank">
								<?php
								if(JVERSION >= '3.0')
									echo '<i class="icon-support"></i>';
								else
									echo '<i class="icon-user"></i>';
								?> <?php echo JText::_('COM_JBOLO_TECHJOOMLA_SUPPORT_CENTER'); ?></a>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<a href="http://extensions.joomla.org/extensions/communication/instant-messaging/9344" target="_blank">
								<?php
								if(JVERSION >= '3.0')
									echo '<i class="icon-quote"></i>';
								else
									echo '<i class="icon-bullhorn"></i>';
								?> <?php echo JText::_('COM_JBOLO_LEAVE_JED_FEEDBACK'); ?></a>
						</div>
					</div>
				</div>

				<br/>
				<div class="row-fluid">
					<div class="span12">
						<p class="pull-right">
							<span class="label label-warning"><?php echo JText::_('COM_JBOLO_CHECK_LATEST_VERSION'); ?></span>
						</p>
					</div>
				</div>

				<div class="row-striped">
					<div class="row-fluid">
						<div class="span6"><?php echo JText::_('COM_JBOLO_HAVE_INSTALLED_VER'); ?></div>
						<div class="span6"><?php echo $this->version; ?></div>
					</div>

					<div class="row-fluid">
						<div class="span6">
							<button class="btn btn-small" type="button" onclick="vercheck();"><?php echo JText::_('COM_JBOLO_CHECK_LATEST_VERSION');?></button>
						</div>
						<div class="span6" id='newVersionChild'></div>
					</div>
				</div>

				<br/>
				<div class="row-fluid">
					<div class="span12">
						<p class="pull-right">
							<span class="label label-info"><?php echo JText::_('COM_JBOLO_STAY_TUNNED'); ?></span>
						</p>
					</div>
				</div>

				<div class="row-striped">
					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_JBOLO_FACEBOOK'); ?></div>
						<div class="span8">
							<!-- facebook button code -->
							<div id="fb-root"></div>
							<script>(function(d, s, id) {
							  var js, fjs = d.getElementsByTagName(s)[0];
							  if (d.getElementById(id)) return;
							  js = d.createElement(s); js.id = id;
							  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
							  fjs.parentNode.insertBefore(js, fjs);
							}(document, 'script', 'facebook-jssdk'));</script>
							<div class="fb-like" data-href="https://www.facebook.com/techjoomla" data-send="true" data-layout="button_count" data-width="250" data-show-faces="false" data-font="verdana"></div>
						</div>
					</div>

					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_JBOLO_TWITTER'); ?></div>
						<div class="span8">
							<!-- twitter button code -->
							<a href="https://twitter.com/techjoomla" class="twitter-follow-button" data-show-count="false">Follow @techjoomla</a>
							<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
						</div>
					</div>

					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_JBOLO_GPLUS'); ?></div>
						<div class="span8">
							<!-- Place this tag where you want the +1 button to render. -->
							<div class="g-plusone" data-annotation="inline" data-width="300" data-href="https://plus.google.com/102908017252609853905"></div>
							<!-- Place this tag after the last +1 button tag. -->
							<script type="text/javascript">
							(function() {
							var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
							po.src = 'https://apis.google.com/js/plusone.js';
							var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
							})();
							</script>
						</div>
					</div>
				</div>

				<br/>
				<div class="row-fluid">
					<div class="span12 center">
						<?php
						$logo_path='<img src="'.JURI::base().'components/com_jbolo/images/techjoomla.png" alt="TechJoomla" class="jbolo_vertical_align_top"/>';
						?>
						<a href='http://techjoomla.com/' taget='_blank'>
							<?php echo $logo_path;?>
						</a>
						<p><?php echo JText::_('COM_JBOLO_COPYRIGHT'); ?></p>
					</div>
				</div>
			</div>
		</div><!--END span4 -->
	</div><!--END outermost row-fluid -->
</div><!--END techjoomla-bootstrap-->