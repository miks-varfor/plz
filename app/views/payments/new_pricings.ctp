<h1>Kirjaa uusia jäsenyysjaksoja</h1>

<?php

echo $form->create('Payments',array('action' => 'createPricings'));

?>

<p>Seuraava jäsenmaksukausi alkaa <?php echo $nextSeasonStartDate?>.</p>

<table id="user_list_admin">
<tr>
	<th>Jakson pituus</th>
	<th>Jäsenhinta</th>
	<th>Ulkojäsenhinta</th>
</tr>
<?php

$n = 0;
foreach(array_keys($pricings) as $p) {
	$seasons = $p;
	$memberPrice = $pricings[$p]['jasen'];
	$nonMemberPrice = $pricings[$p]['ulkojasen'];
?>
<tr>
	<td><input type="text" name="seasons[<?php echo $n?>]" size="3" value="<?echo $seasons?>" /></td>
	<td><input type="text" name="member_prices[<?php echo $n?>]" size="3" value="<?echo $memberPrice?>" /> &euro;</td>
	<td><input type="text" name="nonmember_prices[<?php echo $n?>]" size="3" value="<?echo $nonMemberPrice?>" /> &euro;</td>
</tr>
<?php
	$n++;
}
?>
</table>

<div><input type="submit" value="Tallenna" /></div>

<?php

echo $form->end();


?>
