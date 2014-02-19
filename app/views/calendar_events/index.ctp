<!-- Tulostetaan tulevan tapahtuman nimi linkkinä, ajankohta, maksullisuus -->
<?php
$copy_events = $Events;
foreach ($Events as $event): // alustetaan taulukko
	if($event['CalendarEvent']['deleted'] == 1 || $event['CalendarEvent']['template'] == 1) { // poistetut ja templatet pois
		unset($copy_events[array_search($event, $copy_events)]);
		continue;
	}
//	if (isset($eventStatus) && $eventStatus == 'old') { // halutaan vanhoja
//		 if (strtotime($event['CalendarEvent']['starts']) >= strtotime('now')-60*60*12) { // tulevat pois
//			unset($copy_events[array_search($event, $copy_events)]);
//			continue;
//		}
//	} elseif (strtotime($event['CalendarEvent']['starts']) <= strtotime('now')-60*60*12) { // halutaan uusia
//			unset($copy_events[array_search($event, $copy_events)]); // vanhat pois
//			continue;
//	}
endforeach;
if(isset($eventStatus) && $eventStatus == 'old')
	$copy_events = array_reverse($copy_events);
$events = $copy_events;
foreach ($events as $event):
	echo "<fieldset>";
	echo "<legend>" . $html->link($event['CalendarEvent']['name'], "/calendar_events/view/".$event['CalendarEvent']['id']) . "</legend>\n";
	echo "<table>\n";
	echo "	<tr><td>Ajankohta</td>\n	<td>";
	echo FormatHelper::date($event['CalendarEvent']['starts'], true) . " " . FormatHelper::time($event['CalendarEvent']['starts']);
	echo "	</td>\n</tr>";
	echo "  <tr><td>Hinta</td>\n     ";
	if ($event['CalendarEvent']['price'] != NULL) {
		echo "<td>" . nl2br($event['CalendarEvent']['price']) . "</td>\n";
	}	
	else {	
		echo "<td>Ei maksua</td>\n";
	}
	echo "	</tr>";

	if(isset($event['CalendarEvent']['registration_starts'])) {
		echo "  <tr><td>Ilmoittautumisaika</td>\n        <td>";
                echo FormatHelper::date($event['CalendarEvent']['registration_starts']) . " ";
 		echo FormatHelper::time($event['CalendarEvent']['registration_starts']) . " - ";
		echo FormatHelper::date($event['CalendarEvent']['registration_ends']) . " ";
	        echo FormatHelper::time($event['CalendarEvent']['registration_ends']);
        	echo "  </td>\n</tr>";
	}
	if(isset($event['CalendarEvent']['location'])) {
		echo "	<tr><td>Paikka</td>\n";
		echo "	<td>". $event['CalendarEvent']['location'] ."</td>\n</tr>\n";	
	}

	echo "</table>\n";

	$text = $event['CalendarEvent']['description'];
	if (ereg("[\"|'][[:alpha:]]+://",$text) == false)
	{
        	$text = ereg_replace('([[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/])', '<a href="\\1">\\1</a>', $text);
	}
	echo '<div class="event_text">' . nl2br(str_replace('&', '&amp;', $text), true) . "</div>\n";
	echo "</fieldset><br/>";


endforeach;
?>


