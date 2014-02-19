<?php
/**
 * browse_calendar.ctp
 * 
 * @author Samu Kytöjoki
 */
?>
	<array>
<?php 
	if ($status == 0) {
		foreach ($data as $row) { ?>
		<event>
<?php
			foreach ($row['CalendarEvent'] as $key => $value ) { ?>
			<field>
				<name><?php echo htmlspecialchars($key); ?></name>
				<value><?php echo htmlspecialchars($value); ?></value>
			</field>
<?php
			} ?>
		</event>
<?php
		}
	} ?>
	</array>
