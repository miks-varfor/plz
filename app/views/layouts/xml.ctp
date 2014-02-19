<?php
/**
 * xml.ctp
 * Ulkoisen rajapinnan layout
 *
 * @author Samu
 * @package Kurre
 * @version 0.1
 * @license GNU General Public License v2
 */

header('content-type: text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>
<response>
	<status>
		<code><?php echo $status; ?></code>
		<message><?php echo $message; ?></message>
	</status>
<?php echo $content_for_layout; ?>
</response>
