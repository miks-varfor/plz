<?php
/**
 * show_invoice.ctp
 * 
 * @author Samu Kytöjoki
 */
?>
	<invoice>
<?php 
	if ($status == 0) {
		foreach ($data['Payment'] as $key => $value) { ?>
		<field>
			<name><?php echo $key; ?></name>
			<value><?php echo $value; ?></value>
		</field>
<?php 	}
	} ?>
	</invoice>
