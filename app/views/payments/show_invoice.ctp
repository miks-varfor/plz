<?php

if(isset($error_message)) {
	echo "<p><strong>$errorMessage</strong></p>\n";
}
else {
?>

<h1>Jäsenmaksun tiedot</h1>

<table>
<tr>
	<td>Maksaja:</td>
	<td><?php echo $payer_name?></td>
</tr>
<tr>
	<td>Saaja:</td>
	<td>Tuomas Junno</td>
</tr>
<tr>
	<td>Pankki:</td>
	<td>Nordea</td>
</tr>
<tr>
	<td>IBAN:</td>
	<td>FIXX XXXX XXXX XXXX</td>
</tr>
<tr>
	<td>BIC:</td>
	<td>NDEAFIHH</td>
</tr>
<tr>
	<td>Laskun summa:</td>
	<td><?php echo $amount?> euroa</td>
</tr>
<tr>
	<td>Viitenumero:</td>
	<td><?php echo $reference_number?></td>
</tr>
<tr>
	<td>Laskun luontipäivä:</td>
	<td><?php echo $create_date?></td>
</tr>
<tr>
	<td>Laskun eräpäivä:</td>
	<td><?php echo $due_date?></td>
</tr>
<tr>
	<td>Maksun tila:</td>
	<td><?php
	if($is_paid) {
		echo "Maksettu";
	} else {
		echo "Maksamatta";
	}?></td>
</tr>
<tr>
	<td>Jäsenkausi päättyy:</td>
	<td><?php echo $last_valid_date?></td>
</tr>
</table>
<?php

}

?>
