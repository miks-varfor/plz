<?php
/**
 * get_participation_info.ctp
 * 
 * @author Samu Kytöjoki
 */
?>
	<array>
<?php 
	if ($status == 0) {
		foreach ($data as $row) { ?>
		<extra>
<?php
			foreach ($row['CustomFieldAnswer'] as $key => $value ) { ?>
			<field>
				<name><?php echo $key; ?></name>
				<value><?php echo $value; ?></value>
			</field>
<?php
			} ?>
		</extra>
<?php
		}
	} ?>
	</array>
