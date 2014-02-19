<?php
/**
 * get_mailing_lists.ctp
 * 
 * @author Samu Kytöjoki
 */
?>
	<array>
<?php 
	if ($status == 0) {
		foreach ($data as $row) { ?>
		<list>
<?php
			foreach ($row['Group'] as $key => $value ) { ?>
			<field>
				<name><?php echo $key; ?></name>
				<value><?php echo $value; ?></value>
			</field>
<?php
			} ?>
		</list>
<?php
		}
	} ?>
	</array>
