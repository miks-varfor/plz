<?php
/**
 * get_membership_pricings.ctp
 * 
 * @author Samu Kytöjoki
 */
?>
	<array>
<?php 
	if ($status == 0) {
		foreach ($data as $row) { ?>
		<pricing>
<?php
			foreach ($row['Pricing'] as $key => $value ) { ?>
			<field>
				<name><?php echo $key; ?></name>
				<value><?php echo $value; ?></value>
			</field>
<?php
			} ?>
		</pricing>
<?php
		}
	} ?>
	</array>
