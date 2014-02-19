<?php
/**
 * get_event_info_fields.ctp
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
			foreach ($row['CustomField'] as $key => $value ) { ?>
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
