<?php
/**
 * membership.ctp
 * Jäsentietojärjestelmän layout
 *
 * Layout updated 01/2011
 *
 * @author Samu
 * @author wox
 * @package Kurre
 * @version 2.0
 * @license GNU General Public License v2
 */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">

<head>
<?php
	echo $html->charset('utf-8') . "\n";
	echo $html->meta('icon') . "\n";
	echo $html->css('reset') . "\n";
	echo $html->css('kurre') . "\n";
	echo $scripts_for_layout . "\n";
	echo $html->meta('rss', '/calendar_events/rss', array('title' => 'Tapahtumakalenterin RSS-syöte')) . "\n";
?>
<link rel="stylesheet" href="/css/fonts.css" type="text/css" charset="utf-8" />

<title><?php echo $title_for_layout; ?> - MIKS</title>

<link rel="image_src" href="/img/miks.png" />

</head>

<body>
	<div id="wrapper">
		
		<?php
		$submenu = '';
		if ($currentUser != null)
			{
				$submenu = "<ul class='overlap'><li class='li_submenu'></li></ul>\n";
				$submenu .= "<ul id='sub'>\n";
				$submenu .= "<li class='border'><div class='submenu_link'>";
				$submenu .= '<a href="/users/edit">omat tiedot</a>';
				$submenu .= "</div></li>\n";
				$submenu .= "<li class='border'\n><div class='submenu_link'>";
				$submenu .= $html->link('jäsenmaksu','/payments/newInvoice');
				$submenu .= "</div></li>\n";
				$submenu .= "<li class='li_submenu overlap'></li>\n";
				$submenu .= "</ul>\n";
		    }

		echo $this->renderElement('main_menu', array('calendarsub' => false, 'submenu' => $submenu));
		?>



	<div id='content'> 

		<?php echo $this->renderElement('header'); ?>

		<?php echo $this->renderElement('login'); ?>

		<?php echo $this->renderElement('social'); ?>

		<h1 id='title'><?php echo $this->pageTitle; ?></h1>

<?php
	if ($currentUser != null && ($currentUser['role'] == 'virkailija' ||
			$currentUser['role'] == 'tenttiarkistovirkailija' ||
			$currentUser['role'] == 'jasenvirkailija' ||
			$currentUser['role'] == 'yllapitaja')) 
	{

		echo '<div id="users_menu">';

		printf("<p>%s</p>\n", $html->link('Lisää käyttäjä', '/users/add'));

		echo "<b>Etsi käyttäjä</b>\n";
		echo "<p style=font-size:11px;> Etsii kaikki käyttäjät, joiden käyttäjätunnuksessa, nimessä tai sähköpostiosoitteessa esiintyy annettu hakuehto.</p>\n";//Tia
		echo $form->create('User', array('type' => 'get', 'action' => 'find')) ;
		printf("<p>%s</p>", $form->text('query'));
		echo $form->submit('Hae') ;
		echo "</form>";

		echo "<p>Käyttäjälistaukset</p>\n";
		echo "<ul>\n";
		printf("<li>%s</li>\n", $html->link('Kaikki käyttäjät', '/users/listSelected'));
		printf("<li>%s</li>\n", $html->link('Kaikki jäsenet', '/users/listSelected/member'));
		printf("<li>%s</li>\n", $html->link('Jäsenmaksun maksaneet käyttäjät', '/users/listSelected/paid'));
		printf("<li>%s</li>\n", $html->link('Maksamattomat käyttäjät', '/users/listSelected/nonpaid'));
		printf("<li>%s</li>\n", $html->link('Maksamattomat jäsenet', '/users/listSelected/member/nonpaid'));
		printf("<li>%s</li>\n", $html->link('Jäseneksi hyväksymistä odottavat', '/users/listSelected/paid_or_new/nonmember'));
		printf("<li>%s</li>\n", $html->link('Erotetut käyttäjät', '/users/listSelected/revoked'));
		echo "</ul>\n";

		if ($currentUser['role'] == 'jasenvirkailija' |
			$currentUser['role'] == 'yllapitaja') {

			echo "<p>Maksulistaukset</p>\n";
			echo "<ul>\n";
			printf("<li>%s</li>\n", $html->link('Merkitse tilisiirtoja maksetuiksi', '/payments/listUnpaid'));
			printf("<li>%s</li>\n", $html->link('Tilisiirrolla maksetut jäsenmaksut', '/payments/listBankPaid'));
			printf("<li>%s</li>\n", $html->link('Käteisellä maksetut jäsenmaksut', '/payments/listCashPaid'));
			echo "</ul>\n";

			printf("<p>%s</p>\n", $html->link('Lähetä sähköpostia jäsenille', '/mailer/newMail'));
		}

		if ($currentUser['role'] == 'yllapitaja') {
			printf("<p>%s</p>\n", $html->link('Jäsenmaksukausien muokkaus', '/payments/newPricings'));
		}
		
		echo '</div>';
	}
?>

<div id="members_main">
	<?php
		if ($session->check('Message.flash')) {
			$session->flash();
		}
	?>

	<?php echo $content_for_layout; ?>
</div>

<?php if(Configure::read('show_sponsor')) echo $this->renderElement('sponsor'); ?>

<?php echo $this->renderElement('footer'); ?>

</div>

<div id='overlap'></div>

</div>

</body>
</html>
