<?php

function print_len($text, $len) {
	if (mb_strlen($text) <= $len) {
		echo $text;
		for ($i = mb_strlen($text) ; $i < $len ; $i++) {
			echo ' ';
		}
	}
	else {
		echo mb_substr($text, 0, $len - 3);
		echo '...';
	}
	echo ' ';
}

if($text) {
	header('Content-Type: text/plain');
	print_len("Nimi", 35);
	print_len("Luontipäivä", 12);
	print_len("Viitenumero", 12);
	print_len("Summa", 8);
	echo "\n";
	foreach($results as $r) {
		print_len($r['0']['payer_name'], 35);
		print_len($r['0']['date_created'], 12);
		print_len($r['p']['reference_number'], 12);
		print_len($r['p']['amount'], 8);
		echo "\n";
	}
	exit();
}
else {

?>

<h1>Kirjaa jäsenmaksuja</h1>

<form action="listUnpaid" method="get">

<p>Näytä tiedot ajalta</p>
<p style=font-size:11px;>Anna päivämäärä muodosssa vvvv-kk-pp</p>
<div>
<input type="text" name="startDate" value="<?php echo $startDate?>" />
-
<input type="text" name="endDate" value="<?php echo $endDate?>" />
</div>

<div style="margin-top: 5px;"><input type="submit" value="Toteuta" /></div>

</form>
<?php

	if(count($results) == 0) {

?>
<p>Hakuehtoja vastaavia jäsenmaksulaskuja ei löytynyt</p>
<?php

	}
	else {

		$queryParts['format'] = 'text';

?>
<p><a href="listUnpaid?<?php echo http_build_query($queryParts)?>">Listaus tekstimuodossa</a></p>

<?php

		echo $form->create('Payments',array('action' => 'payByBank'));

?>
<table id="user_list_admin">
<tr>
	<th><input type="checkbox" onclick="selectOrUnselectAll();" /></th>
	<th>Nimi</th>
	<th>Luontipäivä</th>
	<th>Viitenumero</th>
	<th>Summa</th>
</tr>
<?php

		$paymentIds = array();

		foreach($results as $r) {
?>
<tr>
	<td><input type="checkbox" name="selected_payments[<?php echo $r['p']['id']?>]" id="checkbox_<?php echo $r['p']['id']?>" value="1" /></td>
	<td><a href="../users/edit/<?php echo $r['pu']['payer_id']?>"><?php echo htmlspecialchars($r['0']['payer_name'])?></a></td>
	<td><?php echo htmlspecialchars($r['0']['date_created'])?></td>
	<td><?php echo htmlspecialchars($r['p']['reference_number'])?></td>
	<td><?php echo htmlspecialchars($r['p']['amount'])?></td>
</tr>
<?php
			array_push($paymentIds,$r['p']['id']);
		}
	
?>
</table>

<script type="text/javascript">

var paymentIds = [<?php echo implode(',',$paymentIds)?>];
var boxStatus = false;

function selectOrUnselectAll() {
	boxStatus = !boxStatus;
	for(var i=0; i < paymentIds.length; i++) {
		document.getElementById('checkbox_'+paymentIds[i]).checked=boxStatus;
	}
}

</script>

<div><input type="submit" name="confirm" value="Hyväksy valitut maksut" />
&nbsp;<input type="submit" name="delete" value="Poista valitut maksut" /></div>
<?php

	echo $form->end();

	}

}

?>
