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
	print_len("Summa", 8);
	print_len("Maksupäivä", 12);
	print_len("Hyväksyjä", 30);
	echo "\n";
	foreach($results as $r) {
		print_len($r['0']['payer_name'], 35);
		print_len($r['p']['amount'], 8);
		print_len($r['0']['date_paid'], 12);
		print_len($r['cu']['confirmer_name'], 30);
		echo "\n";
	}
	exit();
}
else {
?>
<h1>Käteisellä suoritetut jäsenmaksut</h1>
<form action="listCashPaid" method="get">

<p>Näytä tiedot ajalta</p>
<p style=font-size:11px;>Anna päivämäärä muodosssa vvvv-kk-pp</p>
<div>
<input type="text" name="startDate" value="<?php echo $startDate?>" />
-
<input type="text" name="endDate" value="<?php echo $endDate?>" />
</div>

<div><input type="submit" value="Hae" /></div>
</form>
<?php
	
	if(count($results) == 0) {

?>
<p>Hakuehtoja vastaavia jäsenmaksuja ei löytynyt</p>
<?php

	}
	else {
		$queryParts['format'] = 'text';
?>
<p><a href="listCashPaid?<?php echo http_build_query($queryParts)?>">Listaus tekstimuodossa</a></p>

<table id="user_list_admin">
<tr>
	<th>Nimi</th>
	<th>Summa</th>
	<th>Maksupäivä</th>
	<th>Hyväksyjä</th>
</tr>
<?php
		$i = 0;
		foreach ($results as $r){
			$i++;
?>
<tr>
	<td><a href="../users/edit/<?php echo $r['pu']['payer_id']?>"><?php echo htmlspecialchars($r['0']['payer_name'])?></a></td>
	<td><?php echo htmlspecialchars($r['p']['amount'])?></td>
	<td><?php echo htmlspecialchars($r['0']['date_paid'])?></td>
	<td><a href="../users/edit/<?php echo $r['cu']['confirmer_id']?>"><?php echo htmlspecialchars($r['cu']['confirmer_name'])?></a></td>
</tr>
<?php
		}
?>
</table>
<p>Yhteensä <?php echo $i; ?> riviä.</p>

<?php

	}

}

?>
