<rss version="2.0" encoding="utf-8">
<channel>
<title>MIKSin tapahtumakalenteri</title>
<link>http://tapahtuu.domain.local</link>
<description>MIKSin tulevat tapahtumat</description>
<?php 
foreach ($Events as $event):
if ($event['CalendarEvent']['template'] == 0 && 
	$event['CalendarEvent']['deleted'] == 0 && 
	strtotime($event['CalendarEvent']['starts']) > strtotime('now')) {
	echo "<item>\n";
	echo "<title>";
	echo $event['CalendarEvent']['name'] . " ";
	echo FormatHelper::dateTime($event['CalendarEvent']['starts']);
	echo "</title>\n";
	echo "<link>";
	echo 'http://domain.local/event/' 
. $event['CalendarEvent']['id'];
	echo "</link>\n";
	echo "<description>";
	echo htmlspecialchars($event['CalendarEvent']['description']);
	echo "</description>\n";
	echo "</item>\n";
}
endforeach;
?>
</channel>
</rss> 
