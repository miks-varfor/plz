<?php
/**
 * get_user_info.ctp
 * 
 * @author Samu Kytöjoki
 */
?>
	<user>
<?php 
	if ($status == 0) {
		foreach ($data['User'] as $key => $value) { ?>
		<field>
			<name><?php echo $key; ?></name>
			<value><?php echo $value; ?></value>
		</field>
<?php 	}
	} ?>
	</user>
