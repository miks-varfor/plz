<?php
/**
 * calendar.ctp
 * Tapahtumakalenterin layout
 * 
 * Layout updated 01/2011
 *
 * @author Tia
 * @author wox
 * @package Kurre
 * @version 2.0
 * @license GNU General Public License v2
 */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">

<head>
<?php
	echo $html->charset('utf-8') . "\n";
	echo $html->meta('icon') . "\n";
	echo $html->css('reset') . "\n";
	echo $html->css('kurre') . "\n";
	echo $scripts_for_layout . "\n";
	echo $html->meta('rss', '/calendar_events/rss', array('title' => 'Tapahtumakalenterin RSS-syöte')) . "\n";
?>
<link rel="stylesheet" href="/css/fonts.css" type="text/css" charset="utf-8" />

<title><?php echo $title_for_layout; ?> - MIKS</title>
<script type="text/javascript" src="/js/setDate.js"></script>

<link rel="image_src" href="/img/miks.png" />

</head>

<body>
<div id="wrapper">
	
	<?php
	$submenu = '';
	if ($currentUser != null && 
		($currentUser['role'] == 'virkailija' ||
		$currentUser['role'] == 'tenttiarkistovirkailija' || 
		$currentUser['role'] == 'jasenvirkailija' ||
		$currentUser['role'] == 'yllapitaja'))
		{
			$submenu = "<ul class='overlap'><li class='li_submenu'></li></ul>\n";
			$submenu .= "<ul id='sub'>\n";
			$submenu .= "<li class='border'><div class='submenu_tworow'>";
			$submenu .= '<a href="/calendar_events/create">lisää uusi<br/>tapahtuma</a>';
			$submenu .= "</div></li>\n";
			$submenu .= "<li class='border'\n><div class='submenu_link'>";
			$submenu .= $html->link('hallinta','/calendar_events/manage');
			$submenu .= "</div></li>\n";
			$submenu .= "<li class='li_submenu overlap'></li>\n";
			$submenu .= "</ul>\n";
	    }
	
	echo $this->renderElement('main_menu', array('calendarsub' => true, 'submenu' => $submenu));
	?>
	

<div id='content'> 
	
	<?php echo $this->renderElement('header'); ?>
	
	<?php echo $this->renderElement('login'); ?>
	
	<?php echo $this->renderElement('social'); ?>
	
	<h1 id='title'>Tapahtumakalenteri</h1>


<div id="events_menu" class="inner">
<?php

	if (isset($eventStatus)) {
		$formSelection = $eventStatus;
	} else {
		$formSelection = "new";
	}

	echo $form->create('CalendarEvents', array('type' => 'post', 'action' => 'index'))  . "\n";
	echo $form->select('type', array('new' => 'Tulevat tapahtumat', 'old' => 'Menneet tapahtumat'),
		$formSelection, array('onchange' => 'this.form.submit();'), false) . "<br/>\n";
	echo "</form>\n";

	$copy_events = $Events;
	foreach ($Events as $event): // alustetaan taulukko
        	if($event['CalendarEvent']['deleted'] == 1 || $event['CalendarEvent']['template'] == 1) { // poistetut ja templatet pois
                	unset($copy_events[array_search($event, $copy_events)]);
	                continue;
	        }
	endforeach;
	if(isset($eventStatus) && $eventStatus =='old')
		$copy_events = array_reverse($copy_events);

	$Events = $copy_events;
	$i = 0;
	foreach ($Events as $ev) {
		$registrableNow = false;
		$regPanic = false;
		$isCurrentEvent = false;
		if(isset($ev['CalendarEvent']['registration_starts'])
                        && strtotime($ev['CalendarEvent']['registration_starts']) <= strtotime('now')
                        && strtotime($ev['CalendarEvent']['registration_ends']) >= strtotime('now')) { // tapahtumaan ilmoittautuminen on käynnissä
			$registrableNow = true;
			if((strtotime($ev['CalendarEvent']['registration_ends']) - strtotime('now')) < 24*60*60) { // ilmon sulkeutumiseen alle 24 tuntia
				$regPanic = true;
			}
		}
		
		if (isset($selectedEventId) && $selectedEventId == $ev['CalendarEvent']['id']) {
			// Highlight current event
			$isCurrentEvent = true;
		}
		
		$datetime = FormatHelper::date($ev['CalendarEvent']['starts'], true) . " " . FormatHelper::time($ev['CalendarEvent']['starts']);
		echo '<div onclick="window.location=\'/calendar_events/view/'.$ev['CalendarEvent']['id'].'\'" class="event';
		if($isCurrentEvent) echo ' current_event';
		else if($regPanic) echo ' panic';
		echo "\">\n";
		echo "<h2>" . $html->link($ev['CalendarEvent']['name'], "/calendar_events/view/".$ev['CalendarEvent']['id']) . "</h2>\n";
		echo "<div class=\"date\">" . $datetime . "</div>\n";
		if($registrableNow) {
			echo "<div class=\"date\">Ilmo dl: " . FormatHelper::date($ev['CalendarEvent']['registration_ends'], false) . " "
			     . FormatHelper::time($ev['CalendarEvent']['registration_ends']). "</div>";
		} elseif(isset($ev['CalendarEvent']['registration_starts']) // tapahtumaan voi ilmota mutta ilmo on päättynyt
			&& strtotime($ev['CalendarEvent']['registration_ends']) < strtotime('now')) {
			echo "<div class=\"date\">Ilmoittautuminen päättynyt</div>";
		}
		echo "</div>\n";
		
		if($isCurrentEvent) echo '<div class="current_event_overlap"></div>';
		else if($regPanic) echo '<div class="panic_overlap"></div>';
		else echo '<div class="overlap"></div>';
	}

?>

<div id="events_menu_bottom_spacer"></div>

</div>

<div id="members_main">
	<?php
		if ($session->check('Message.flash')) {
			$session->flash();
		}
	?>

	<?php echo $content_for_layout; ?>
</div>


<?php if(Configure::read('show_sponsor')) echo $this->renderElement('sponsor'); ?>

<?php echo $this->renderElement('footer'); ?>

</div>

<div id='overlap'></div>

</div>

</body>
</html>
