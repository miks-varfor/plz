<?php
/**
 * is_exam_officer.ctp
 *
 * @author wox
 */
?>
<?php
	if ($status == 0) { ?>
	<user>
		<timestamp><?php echo $data['timestamp']; ?></timestamp>
		<username><?php echo $data['username']; ?></username>
		<isExamOfficer><?php echo $data['isExamOfficer']; ?></isExamOfficer>
	</user>
	<?php }
?>
