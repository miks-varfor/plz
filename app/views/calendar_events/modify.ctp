<?php 

if ($event['CalendarEvent']['template'] == 1) {
	echo "<h1>Muokkaa tapahtumapohjaa</h1>\n";
}
else {
	echo "<h1>Muokkaa tapahtumaa</h1>\n";
}

$fixedLocations = array();

// Haetaan vakiopaikat
foreach($locations as $location) {
	$fixedLocations[$location['Location']['name']] = $location['Location']['name'];
}

$fixedEventTypes = array();

// Haetaan tapahtumatyypit
foreach($eventTypes as $eventType) {
	$fixedEventTypes[$eventType['EventType']['name']] = $eventType['EventType']['name'];
}

$isTemplate = 0;

if ($event['CalendarEvent']['template'] == 1) {
	$isTemplate = 1;
}
?>

<?php echo $form->create('CalendarEvent', array('type' => 'post', 'action' => 'modify')); ?>
<?php echo $form->hidden('CalendarEvent.starts', aa('value', $event['CalendarEvent']['starts'])); ?>
<?php echo $form->hidden('CalendarEvent.id', aa('value', $event['CalendarEvent']['id'])); ?>

<div id="modify_event">

<fieldset class="mandatory_event_info">
<legend>Pakolliset tiedot</legend>

<table>
<tr>
<td>
<?php echo $form->label('name', 'Tapahtuman nimi:'); ?>
</td>
<td>
<?php echo $form->text('name', array('size' => '40') ); ?>
<?php echo $form->error('name'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('date', "Päivämäärä: (PP.KK.VVVV)"); ?>
</td>
<td>
<?php echo $form->text('date', array('size' => '40') ); ?>
<?php echo $form->error('date'); ?>
</td>
</tr>


<tr>
<td>
<?php echo $form->label('time', "Aika: (TT:MM)"); ?>
</td>
<td>
<?php echo $form->text('time', array('size' => '40') ); ?>
<?php echo $form->error('time'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('fixedLocation', "Valitse tapahtumapaikka listasta:"); ?>
</td>
<td>
<?php echo $form->select('fixedLocation', array($fixedLocations) ); ?>
<?php echo $form->error('fixedLocation'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('location', "Anna tapahtuman paikka:"); ?>
</td>
<td>
<?php echo $form->text('location', array('size' => '40') ); ?>
<?php echo $form->error('location'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('fixedEventType', "Valitse tapahtuman tyyppi listasta:"); ?>
</td>
<td>
<?php echo $form->select('fixedEventType', array($fixedEventTypes) ); ?>
<?php echo $form->error('fixedEventType'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('category', "tai kirjoita tapahtuman tyyppi:"); ?>
</td>
<td>
<?php echo $form->text('category', array('size' => '40') ); ?>
<?php echo $form->error('category'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('description', "Kuvaus:"); ?>
</td>
<td>
<?php echo $form->textarea('description', array('rows' => '12', 'cols' => '46') ); ?>
<?php echo $form->error('description'); ?>
</td>
</tr>

</table>

</fieldset>

<fieldset class="mandatory_event_info">

<legend>Valinnaiset tiedot</legend>

<table>

<tr>
<td>
<?php echo $form->label('responsible', 'Vastuuhenkilön nimi:'); ?>
</td>
<td>
<?php echo $form->text('responsible', array('size' => '40', array('size' => '80')) ); ?>
<?php echo $form->error('responsible'); ?>
</td>
</tr>

<tr>
<td>
</td>
<td>
<?php echo $form->checkbox('show_responsible'); ?> Vastuuhenkilön tiedot näytetään
<?php echo $form->error('show_responsible'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('price', 'Hinta:'); ?>
</td>
<td>
<?php echo $form->textarea('price', array('rows' => '3', 'cols' => '40') ); ?>
<?php echo $form->error('price'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('map', 'Karttalinkki:'); ?>
</td>
<td>
<?php echo $form->text('map', array('size' => '40') ); ?>
<?php echo $form->error('map'); ?>
</td>
</tr>

<tr>
<td>
</td>
<td>
<?php echo $form->checkbox('can_participate'); ?> Tapahtumaan voi ilmoittautua
<?php echo $form->error('can_participate'); ?>
</td>
</tr>

<tr>
<td>
</td>
<td>
<?php echo $form->checkbox('membership_required'); ?> Ilmoittautujien oltava jäseniä
<?php echo $form->error('membership_required'); ?>
</td>
</tr>

<tr>
<td>
</td>
<td>
<?php echo $form->checkbox('avec'); ?> Tapahtumaan voi ilmoittaa seuralaisen
<?php echo $form->error('avec'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('max_participants', 'Suurin osallistujamäärä:'); ?>
</td>
<td>
<?php echo $form->text('max_participants', array('size' => '5') ); ?>
<?php echo $form->error('max_participants'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('registration_starts', 'Ilmoittautumisen alku:<br/>(pp.kk.vvvv tt:mm)'); ?>
</td>
<td>
<?php echo $form->text('registration_starts', array('size' => '40') ); ?>
<?php echo $form->error('registration_starts'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('registration_ends', 'Ilmoittautumisen loppu:<br/>(pp.kk.vvvv tt:mm)'); ?>
</td>
<td>
<?php echo $form->text('registration_ends', array('size' => '40') ); ?>
<?php echo $form->error('registration_ends'); ?>
</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>Näillä voit asettaa tyypillisimmät perumistavat:<br />
<div id="canCancel_buttons">
	<input type="button" onclick="setCanCancel(true)" value="Ilmoittautumisaikana voi perua" />
	<input type="button" onclick="setCanCancel(false)" value="Sitova ilmoittautuminen" />
</div>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('cancellation_starts', 'Ilmoittautumisen perumisen alku:<br/>(pp.kk.vvvv tt:mm)'); ?>
</td>
<td>
<?php echo $form->text('cancellation_starts', array('size' => '40') ); ?>
<?php echo $form->error('cancellation_starts'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('cancellation_ends', 'Ilmoittautumisen perumisen loppu:<br/>(pp.kk.vvvv tt:mm)'); ?>
</td>
<td>
<?php echo $form->text('cancellation_ends', array('size' => '40') ); ?>
<?php echo $form->error('cancellation_ends'); ?>
</td>
</tr>

<?php
if ($isTemplate == 0) {
	echo "<tr>\n";
	echo "<td>\n";
	echo "</td>\n";
	echo "<td>\n";
	echo $form->checkbox('template') . "Tallenna tapahtumapohjaksi\n";
	echo $form->error('template');
	echo "</td>\n";
	echo "</tr>\n";
}
else {
	echo $form->hidden('CalendarEvent.template', aa('value', 1));
}
?>

</table>
</fieldset>

<fieldset>
<legend>Lisätietokentät</legend>

<p>Monivalinnan vaihtoehdot syötetään puolipisteellä eroteltuina.
Esimerkiksi sitsien alkoholitoive voisi olla
&quot;Ei kiitos;Punaviini;Valkoviini&quot;. Jos tarvitset lisää kenttiä,
talleta lomake ja palaa muokkaamaan talletettua tapahtumaa.</p>

<p><b>Laita valintaruudullekin aina vastausvaihtoehto!</b></p>

<table>
<tr>
<th>Tyyppi</th><th>Kysymys</th><th>Monivalinnan vaihtoehdot</th>
</tr>
<?php
$empty_field = array('type' => '', 'name' => '', 'options' => '');
if (isset($event) && isset($event['CustomField'])) {
	$custom_fields = $event['CustomField'];
}
else {
	$custom_fields = array($empty_field);
}
$custom_fields[] = $empty_field;
$custom_fields[] = $empty_field;
$custom_fields[] = $empty_field;

$type_options = array('text' => 'Yksirivinen teksti',
					  'textarea' => 'Monirivinen teksti',
					  'radio' => 'Monivalinta',
					  'checkbox' => 'Valintaruutu');

foreach ($custom_fields as $id => $data) {
	echo "<tr>\n<td>";
	echo $form->select('CustomField.'.$id.'.type', $type_options);
	echo "</td>\n<td>";
	echo $form->text('CustomField.'.$id.'.name');
	echo "</td>\n<td>";
	echo $form->text('CustomField.'.$id.'.options', array('class' => 'event_custom_field'));
	echo "</td>\n</tr>";
	if (isset($data['id'])) {
		echo $form->hidden('CustomField.'.$id.'.id');
	}
}
?>

</table>
</fieldset>

</div>

<?php echo $form->submit('Tallenna muutokset'); ?>

<br>

<?php echo $form->end();?>
