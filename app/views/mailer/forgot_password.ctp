
<form method="post" action="<?= $url ?>"> 
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
?>

<? if (isset($errorMessage)): ?>
	<p>
		<strong><?= $errorMessage ?></strong>
	</p>
<? endif ?> 

  <fieldset style="margin-right:20%">
 
		<legend>Uuden salasanan tilaus</legend>
<p>Voit tilata uuden salasanan tietoihisi tallennettuun osoitteeseen.</p>
		<table>
			<?= $form->input('User.email', options('Sähköpostiosoite: ',array('size' => '50'))) ?>
		</table>
		<p>Ongelmatilanteessa voit tilata uuden salasanan
		järjestelmän ylläpitäjiltä sähköpostitse: admin ät domain.local </p>
</fieldset>
  <p><?= $form->submit('Tilaa'); ?></p>
</form>


