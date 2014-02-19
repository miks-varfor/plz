<?php
/**
 * is_member.ctp
 *
 * @author aoforsel
 */
?>
<?php
	if ($status == 0) { ?>
	<user>
		<timestamp><?php echo $data['timestamp']; ?></timestamp>
		<username><?php echo $data['username']; ?></username>
		<ismember><?php echo $data['ismember']; ?></ismember>
	</user>
	<?php }
?>
