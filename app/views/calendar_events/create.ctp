<h1>Lisää uusi tapahtuma</h1>

<?php
$fixedLocations = array();

foreach($locations as $location) {
	$fixedLocations[$location['Location']['name']] = $location['Location']['name'];
}

$fixedEventTypes = array();

foreach($eventTypes as $eventType) {
	$fixedEventTypes[$eventType['EventType']['name']] = $eventType['EventType']['name'];
}

$fixedTemplates = array();

foreach($templates as $template) {
	if($template['CalendarEvent']['deleted'] != 1)
		$fixedTemplates[$template['CalendarEvent']['id']] = $template['CalendarEvent']['name'];
}

$eventType = '';
if (isset($event)) {
	$eventType = $event['CalendarEvent']['category'];
	$givenDate = FormatHelper::date($event['CalendarEvent']['starts']); 
	$givenTime = FormatHelper::time($event['CalendarEvent']['starts']);
	$userName = $event['CalendarEvent']['responsible'];

	$registrationStarts = (isset($event['CalendarEvent']['registration_starts'])?FormatHelper::dateTime($event['CalendarEvent']['registration_starts']):'');
	$registrationEnds = (isset($event['CalendarEvent']['registration_ends'])?FormatHelper::dateTime($event['CalendarEvent']['registration_ends']):'');
	$cancellationStarts = (isset($event['CalendarEvent']['cancellation_starts'])?FormatHelper::dateTime($event['CalendarEvent']['cancellation_starts']):'');
	$cancellationEnds = (isset($event['CalendarEvent']['cancellation_ends'])?FormatHelper::dateTime($event['CalendarEvent']['cancellation_ends']):'');

	$event['CalendarEvent']['template'] = 0;
}
else {
	$registrationStarts = null;
	$registrationEnds = null;
	$cancellationStarts = null;
	$cancellationEnds = null;
}

?>

<?php echo $form->create('CalendarEvent', array('type' => 'post', 'action' => 'create')); ?>

<div id="fetch_template">

<?php echo $form->label('templateId', "Tapahtumapohjat:"); ?>
&nbsp;	
<?php echo $form->select('templateId', array($fixedTemplates) ); ?>

<?php echo $form->submit('Hae tiedot', array('div' => false)); ?>

<?php echo $form->end();?>

</div>

<?php 
echo $form->create('CalendarEvent', array('type' => 'post', 'action' => 'create')); 
echo $form->hidden('CalendarEvent.user_id', aa('value', $userId)); 
echo $form->hidden('CalendarEvent.deleted', aa('value', 0)); 
?>

<div id="modify_event">

<fieldset class="mandatory_event_info">
<legend>Pakolliset tiedot</legend>

<table>
<tr>
<td>
<?php echo $form->label('name', "Tapahtuman nimi:"); ?>
</td>
<td>
<?php echo $form->text('name', array('size' => '40') ); ?>
<?php echo $form->error('name'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('date', "Päivämäärä: (pp.kk.vvvv)"); ?>
</td>
<td>
<?php echo $form->text('date', array('size' => '40', 'value' => $givenDate) ); ?>
<?php echo $form->error('date'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('time', "Aika: (tt:mm)"); ?>
</td>
<td>
<?php echo $form->text('time', array('size' => '40', 'value' => $givenTime) ); ?>
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
<?php echo $form->label('location', "tai kirjoita tapahtumapaikka:"); ?>
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
<?php echo $form->text('responsible', array('size' => '40', 'value' => $userName) ); ?>
<?php echo $form->error('responsible'); ?>
</td>
</tr>

<tr>
<td>
</td>
<td>
<?php echo $form->checkbox('show_responsible', array('value' => '0')); ?> Vastuuhenkilön tiedot näytetään
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
<?php echo $form->checkbox('can_participate', array('value' => '0', 'onchange' => 'setDate()')); ?> 
Tapahtumaan voi ilmoittautua
<?php echo $form->error('can_participate'); ?>
</td>
</tr>

<tr>
<td>
</td>
<td>
<?php echo $form->checkbox('membership_required', array('value' => '0')); ?> Ilmoittautujien oltava jäseniä
<?php echo $form->error('membership_required'); ?>
</td>
</tr>

<tr>
<td>
</td>
<td>
<?php echo $form->checkbox('avec', array('value' => '0')); ?> Tapahtumaan voi ilmoittaa seuralaisen
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
<?php echo $form->label('registration_starts', 'Ilmoittautumisen alku: (pp.kk.vvvv tt:mm)'); ?>
</td>
<td>
<?php echo $form->text('registration_starts', array('value' => $registrationStarts, 'size' => '40') ); ?>
<?php echo $form->error('registration_starts'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('registration_ends', 'Ilmoittautumisen loppu: (pp.kk.vvvv tt:mm)'); ?>
</td>
<td>
<?php echo $form->text('registration_ends', array('value' => $registrationEnds, 'size' => '40') ); ?>
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
<?php echo $form->label('cancellation_starts', 'Ilmoittautumisen perumisen alku: (pp.kk.vvvv tt:mm)'); ?>
</td>
<td>
<?php echo $form->text('cancellation_starts', array('value' => $cancellationStarts, 'size' => '40') ); ?>
<?php echo $form->error('cancellation_starts'); ?>
</td>
</tr>

<tr>
<td>
<?php echo $form->label('cancellation_ends', 'Ilmoittautumisen perumisen loppu: (pp.kk.vvvv tt:mm)'); ?>
</td>
<td>
<?php echo $form->text('cancellation_ends', array('value' => $cancellationEnds, 'size' => '40') ); ?>
<?php echo $form->error('cancellation_ends'); ?>
</td>
</tr>

<tr>
<td>
</td>
<td>
<?php echo $form->checkbox('template', array('value' => '0')); ?> Tallenna tapahtumapohjaksi
<?php echo $form->error('template'); ?>
</td>
</tr>

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
}
?>


</table>
</fieldset>

</div>

<?php echo $form->submit('Luo uusi tapahtuma'); ?>

<br>

<?php echo $form->end();?>



