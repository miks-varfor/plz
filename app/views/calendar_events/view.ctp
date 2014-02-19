<script type="text/javascript">
<!--
function confirmation(ID) {
	var answer = confirm("Haluatko varmasti poistaa tapahtuman?")
	if (answer){
		window.location = "../delete/" + ID;
	}
}
//-->
</script>


<?php 
$this->set('title', $event['CalendarEvent']['name']);
echo "<h1>" . $event['CalendarEvent']['name'] . "</h1>\n";
if ($adminMode == 1) {
	echo "<p>" . $html->link('muokkaa tapahtumaa', "/calendar_events/modify/".$event['CalendarEvent']['id']) . "<br/>\n";
	echo $html->link('poista tapahtuma', "javascript:confirmation(" . $event['CalendarEvent']['id'] . ")");
	echo "</p>";
}
?>

<fieldset>
<table>
<tr>
<td>Päivämäärä:</td>
<td><?php echo FormatHelper::date($event['CalendarEvent']['starts'], true); ?></td>
</tr>
<tr>
<td>Aika:</td>
<td><?php echo FormatHelper::time($event['CalendarEvent']['starts']); ?></td>
</tr>

<tr>
<td>Paikka:</td>
<td><?php

echo h($event['CalendarEvent']['location']);

if(strlen($event['CalendarEvent']['map']) > 0) {
?>
 (<a href="<?php echo h($event['CalendarEvent']['map'])?>">kartta</a>)
<?php
}

?></td>
</tr>
<tr>
<td>Tyyppi:</td>
<td><?php echo h($event['CalendarEvent']['category']); ?></td>
</tr>

<!-- Näytetään tapahtuman hinta jos se on annettu -->
<?php 
if ($event['CalendarEvent']['price']) {
	echo "<tr>\n";
	echo "	<td>Hinta:</td>\n";
	echo "	<td>" . nl2br(h($event['CalendarEvent']['price'])) . "</td>\n";
	echo "</tr>\n";
}
?>

<!-- Tarkastetaan pystyykö tilaisuuteen ilmoittautumaan tapahtumakalenterin kautta -->
<?php 
if ($event['CalendarEvent']['registration_starts'] != NULL) {
	echo "<tr>\n";
	echo "	<td>Ilmoittautumisaika:</td>\n";
	echo "	<td>";
	echo FormatHelper::dateTime($event['CalendarEvent']['registration_starts']) . " - ";
	echo FormatHelper::dateTime($event['CalendarEvent']['registration_ends']);
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	if (!empty($event['CalendarEvent']['cancellation_starts']) &&
		!empty($event['CalendarEvent']['cancellation_ends']) &&
		$event['CalendarEvent']['cancellation_starts'] !=
		$event['CalendarEvent']['cancellation_ends']) {
		echo "	<td>Ilmoittautumisen voi perua:</td>\n";
		echo "	<td>";
		echo FormatHelper::dateTime($event['CalendarEvent']['cancellation_starts']) . " - ";
		echo FormatHelper::dateTime($event['CalendarEvent']['cancellation_ends']);
		echo "</td>\n";
	}
	else {
		echo "  <td colspan='2'>Ilmoittautuminen on sitova.</td>\n";
	}
	echo "</tr>\n";
}
?>

<!-- Näytetäänkö vastuuhenkilö? -->
<?php 
if ($event['CalendarEvent']['show_responsible'] == "1") {
	echo "<tr>\n";
	echo "	<td>Vastuuhenkilö:</td>\n";
	echo "	<td>" . nl2br($event['CalendarEvent']['responsible']) . "</td>\n";
	echo "</tr>\n";
}
?>

</table>

<div class="event_text">
<?php 
//nl2br(h($event['CalendarEvent']['description'])) 
//Ei escapeta html:ää
//nl2br($event['CalendarEvent']['description'])

$text = $event['CalendarEvent']['description'];
if (ereg("[\"|'][[:alpha:]]+://",$text) == false)
    {
        $text = ereg_replace('([[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/])', '<a href="\\1">\\1</a>', $text);
    } 
echo nl2br(str_replace('&', '&amp;', $text), true);
?>
<?php 
if (!$isRegistrable) {
	echo "<p><b>Tapahtumaan ei voi ilmoittautua tapahtumakalenterin kautta</b></p>\n";
}
elseif ($outsidersAllowed) {
	echo "<p><b>Ulkopuoliset voivat ilmoittautua tapahtumaan</b></p>\n";
}
?>
</div>

</fieldset>

<div id="event_status">

<p>Tapahtuman suora linkki on: 
<b><a href="http://domain.local/event/<?php echo $event['CalendarEvent']['id']; ?>">
	 http://domain.local/event/<?php echo $event['CalendarEvent']['id']; ?>
</a>
</b></p>
<?php if($isRegistrable): ?>
	<p>
		Ilmoittautuneita:
		<?php echo $html->link(
			count($event['Registration']) . ($event['CalendarEvent']['max_participants'] > 0 ? ' / '.$event['CalendarEvent']['max_participants'] : ''), 
			'/registrations/listParticipants/'.$event['CalendarEvent']['id']); 
		?>
	</p>
<?php endif ?>

<!-- Ilmon tilaviestit -->
<?php if($isRegistrable): ?>
	<?php if(!$isRegistrationOn): ?>
		<p>Tapahtumaan ilmoittautuminen ei ole tällä hetkellä käynnissä. <p>
	<?php elseif(!$outsidersAllowed && !$currentUser): ?>
		<p><strong>Tapahtuma on tarkoitettu vain MIKSin jäsenille. Ilmoittautuminen vaatii sisäänkirjautumisen.</strong></p>
	<?php elseif($membersOnly && !$isMember): ?>
		<p><strong>Vain jäsenet voivat ilmoittautua tapahtumaan.</strong></p>
		<p>Mikäli olet maksanut jäsenmaksusi tilille muutaman päivän sisällä, sitä ei ehkä ole vielä
			kirjattu järjestelmään. Muussa tapauksessa voit <a href="/payments/newInvoice">luoda tai tarkastaa</a> laskun tiedot ja maksaa tilille.</p>
	<?php endif ?>
<?php endif ?>
<!-- // Ilmon tilaviestit -->


<?php if($showPublicReg || $showMemberReg) { ?>
	<div id="tapahtumaIlmo">
		<strong>Tapahtumaan ilmoittautuminen:</strong><br />
                <?php if($registered): ?>
	                <p>Olet jo ilmoittautunut tapahtumaan.</p>
				<?php elseif( $event['CalendarEvent']['max_participants'] > 0 && (count($event['Registration']) >= $event['CalendarEvent']['max_participants']) ): ?>
					<p>Voit ilmoittautua jonottamaan peruutus- tai lisäpaikkoja.</p>
                <?php endif ?>
		
		<?php if($showPublicReg || $event['CalendarEvent']['avec']): ?>
			<p><small>
				* merkityt tiedot ovat pakollisia.
			<?php if($event['CalendarEvent']['avec']): ?>
				Jos et halua ilmoittaa avecia, jätä kaikki sen kentät tyhjiksi.
			<?php endif ?>
			</small></p>
		<?php endif ?>
		
		<?php echo $form->create('Registration', array('type' => 'post', 'action' => $action));
		    $tdStyle = 'noCustomFields';
		    if(count($event['CustomField']) > 0){
			$tdStyle = 'customFields';
		    }
		?>
			<table id="regForms"><tr>
				<td id="<?= $tdStyle ?>"><?= $this->renderElement('registration_form', array('avec' => false)); ?></td>
				<? if($event['CalendarEvent']['avec']): ?>
					<td id="<?= $tdStyle . "Avec" ?>"><?= $this->renderElement('registration_form', array('avec' => true)); ?></td>
				<? endif ?>
			</tr></table>
			<br />
			<?php if(!$registered || $showPublicReg): ?>
				<input value="Ilmoittaudun!" type="submit" class="button">
			<?php endif ?>
		</form>
<?php if($showCancel) { ?>
		<?php $formy->buttonTo('Peru ilmoittautuminen', '/registrations/cancel/'.$event['CalendarEvent']['id'], 'post', 'button'); ?>
<?php } ?>
		<br /><br />
	</div>
<?php } ?>
</div>

