<script type="text/javascript">
<!--
function confirmation(ID) {
	var answer = confirm("Haluatko varmasti poistaa tapahtumapohjan?")
	if (answer){
		window.location = "../calendar_events/delete/" + ID;
	}
}
//-->
</script>


<h1>Tapahtumakalenterin hallinta</h1>

<?php

$fixedTemplates = array();

foreach($templates as $template) {
	$fixedTemplates[$template['CalendarEvent']['id']] = $template['CalendarEvent']['id'];
}

$fixedLocations = array('' => 'Valitse paikka');

foreach($locations as $location) {
	$fixedLocations[$location['Location']['id']] = $location['Location']['name'];
}

$fixedEventTypes = array('' => 'Valitse tapahtumatyyppi');

foreach($eventTypes as $eventType) {
	$fixedEventTypes[$eventType['EventType']['id']] = $eventType['EventType']['name'];
}

?>

<h2>Muokkaa tapahtumapohjia</h2>

<div id="edit_templates">

<?php
$empty = True;
foreach($templates as $template):
	if($template['CalendarEvent']['deleted'] == 0)	{
		$empty = False;
	}
endforeach;
if (!$empty) {
	echo "<table>\n";
	echo "<tr>\n";
	echo "<th>Nimi</th>\n";
	echo "<th>Ajankohta</th>\n";
	echo "<th>Muokkaus</th>\n";
	echo "<th>Poisto</th>\n";
	echo "</tr>\n";

	foreach ($templates as $template):
		if ($template['CalendarEvent']['deleted'] == 0) {
			echo "<tr>\n";
			echo "<td>" . $template['CalendarEvent']['name'] . "</td>\n";
			echo "<td>";
			echo FormatHelper::date($template['CalendarEvent']['starts']) . " " . FormatHelper::time($template['CalendarEvent']['starts']);
			echo "</td>";
			echo "<td>" . $html->link('muokkaa', "/calendar_events/modify/".$template['CalendarEvent']['id']) . "</td>\n";
			echo "<td>" . $html->link('poista', "javascript:confirmation(" . $template['CalendarEvent']['id'] . ")") . "</td>";
			echo "</tr>\n";
		}
	endforeach;
	echo "</table>\n";
}
else {
	echo "<p>Tapahtumapohjia ei löytynyt.</p>\n";
}
?>

<fieldset>
<legend>Muokkaa vakiopaikkoja</legend>

<?php
echo $form->create('Location', array('type' => 'post',
				     'action' => 'update'));

echo $form->input('id', array('options' => $fixedLocations,
			      'label' => false));
?>
<input type="text" name="data[value]" /><br />
<input type="submit" name="data[rename]" value="Nimeä paikka uudestaan" /><br />
<input type="submit" name="data[map]" value="Aseta uusi karttalinkki" /><br />
<input type="submit" name="data[delete]" value="Poista paikka" />
<?= $form->end(); ?>

</fieldset>
<br/>

<fieldset>
<legend>Muokkaa tapahtumatyyppejä</legend>

<?php
echo $form->create('EventType', array('type' => 'post',
				      'action' => 'update'));

echo $form->input('id', array('options' => $fixedEventTypes,
			      'label' => false));
?>
<input type="text" name="data[value]" /><br />
<input type="submit" name="data[rename]" value="Nimeä tyyppi uudestaan" /><br />
<input type="submit" name="data[delete]" value="Poista tyyppi" />
<?= $form->end(); ?>
</fieldset>

</div>
