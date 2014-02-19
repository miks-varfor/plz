<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"> 
<html> 
<body> 
<?php

echo $form->create('null',array('type' => 'post','action' => 'newMail'));
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

/**
 * Viestiosa, jossa on 
 * From, To, CC ja Subject kentät
 * sekä viestiosa ja lähetä-nappi 
*/
  <fieldset style="margin-right:20%">
 
    <legend>Uusi viesti</legend>
    <table>
	<?= $form->input('From', options('From: ', $currentUser['name']);?>
    <?= $form->input('To', options('To: ',array('size' => '50'))) ?>
    <?= $form->input('CC', options('CC: ',array('size' => '50'))) ?>
	<?= $form->input('Subject: ', options('Subject: ',array('size' => '50'))) ?>
	<?= $form->input('Message', options($message, array('type'=>'fieldset')))?>
	<?= $form->submit('Lähetä')?>
    </table>
  </fieldset>
 <?= $form->end() ?>
 
</body> 
</html>
