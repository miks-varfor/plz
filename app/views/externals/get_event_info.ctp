<?php
/**
 * get_event_info.ctp
 * 
 * @author Samu Kytöjoki
 */
?>
	<event>
<?php 
	if ($status == 0) {
		foreach ($data['CalendarEvent'] as $key => $value) { ?>
		<field>
			<name><?php echo $key; ?></name>
			<value><?php echo $value; ?></value>
		</field>
<?php 	}
	} ?>
	</event>
