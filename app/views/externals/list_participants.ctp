<?php
/**
 * list_participants.ctp
 * 
 * @author Samu Kytöjoki
 */
?>
	<array>
<?php 
	if ($status == 0) {
		foreach ($data as $row) { ?>
		<registration>
<?php
			foreach ($row['Registration'] as $key => $value ) { ?>
			<field>
				<name><?php echo $key; ?></name>
				<value><?php echo $value; ?></value>
			</field>
<?php
			} ?>
		</registration>
<?php
		}
	} ?>
	</array>
