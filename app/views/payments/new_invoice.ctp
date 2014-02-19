<h1>Jäsenmaksu</h1>

<p>Valitse jäsenyyskausi, jonka haluat maksaa tilisiirrolla.
	Mikäli haluat maksaa käteisellä, älä luo laskua tässä,
	vaan ota yhteyttä MIKSin virkailijaan.</p>

<p>Yksi vuosi tarkoittaa vuotuista jäsenmaksukautta 1.1.-31.12.</p>

<?php

echo $form->create(null,array('type' => 'post','action' => 'createInvoice'));

?>
<table id="create_invoice">
<tr>
<th>&nbsp;</th>
<th>Vuodet</th>
<th>Hinta</th>
</tr>
<?php

foreach($pricings as $p) {

?>
<tr>
	<td><input type="radio" name="seasons" id="seasons_<?php echo $p['Pricing']['seasons']?>" value="<?php echo $p['Pricing']['seasons']?>" /></td>
	<td><label for="seasons_<?php echo $p['Pricing']['seasons']?>"><?php echo $p['Pricing']['seasons']?></label></td>
	<td><?php echo $p['Pricing']['price']?> <?php echo ($p['Pricing']['price']==1?'euro':'euroa')?></td>
</tr>
<?

}

?>
</table>
<?php

echo $form->submit('Luo lasku');

echo $form->end();

?>
