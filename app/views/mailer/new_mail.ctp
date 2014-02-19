<form create='Mailer' method = 'post' action = 'sendMail'>

<?php
function options($label, $extra = null) {
	if (!is_array($extra)){
		$extra = array();
	}
	$extra['before'] = "<tr>\n<td>";  
	$extra['between'] = "</td><td>"; 
	$extra['after'] = "</td>\n</tr>\n";
	$extra['label'] = $label;
	return $extra;
}

//virheilmo
if (isset($errorMessage)){
	echo "<p><strong>$errorMessage</strong></p>\n";
} 
?>

  <fieldset style="margin-right:20%">
 
    <legend>Uusi viesti</legend>
    <table>
	<?= $form->input('MIKS', options('From: ',array('size' => '30')))?>
    <?= $form->input('$user', options('To: ',array('size' => '50'))) ?>
    <?= $form->input('$user', options('CC: ',array('size' => '50'))) ?>
	<?= $form->input('$subject ', options('Aihe: ',array('size' => '50'))) ?>
	<?= $form->input('$message', options('Viesti: ', array('type'=>'fieldset')))?>
    </table>
  </fieldset>
  <p><input type="submit" value="Lähetä"></p>
 <?= $form->end() ?>


