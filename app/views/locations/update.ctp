<?php
/**
 * views/locations/update.ctp
 * Näyttää tiedot vakiopaikan muokkauksesta
 *
 * Parametrit:
 * @param string $action tehty muokkaus, joukosta 'rename', 'map', 'delete'.
 * Jos toimintoa ei ole asetettu tai se ei ole joku näistä, paikan
 * uusia tietoja ei näytetä.
 * @param array $location paikan uudet tiedot, CakePHP:n find-funktion
 * antamassa muodossa
 * @param string $errorMessage mahdollinen virheilmoitus
 *
 * @author Niko Kiirala
 * @package kurre
 * @license GNU General Public License v2
 */

/**
 * Tulostaa paikan tiedot.
 * @param array $location paikan tiedot
 * @author Niko Kiirala
 */
function print_location($location) {
	echo "<dl>\n";
	echo "    <dt>Nimi</dt>\n";
	echo '    <dd>'.htmlspecialchars($location['Location']['name'])."</dd>\n";
	echo "    <dt>Karttalinkki</dt>\n";
	if (!empty($location['Location']['map'])) {
		echo '    <dd><a href="';
		echo htmlspecialchars($location['Location']['map']);
		echo '">';
		echo htmlspecialchars($location['Location']['map']);
		echo "</a></dd>\n";
	}
	else {
		echo "    <dd>(ei määritelty)</dd>\n";
	}
	echo "</dl>\n";
}

if (isset($action)) {
	switch($action) {
	case 'rename':
		echo "<h1>Nimeä vakiopaikka uudelleen</h1>\n";
		if (isset($location)) {
			echo "<p>Uudet tiedot:</p>\n";
			print_location($location);
		}
		break;
	case 'map':
		echo "<h1>Aseta vakiopaikan karttalinkki</h1>\n";
		if (isset($location)) {
			echo "<p>Uudet tiedot:</p>\n";
			print_location($location);
		}
		break;
	case 'delete':
		echo "<h1>Poista vakiopaikka</h1>\n";
		if (isset($location)) {
			echo "<p>Poistettu paikka:</p>\n";
			print_location($location);
		}
		break;
	}
}

if (isset($errorMessage)) {
	echo '<p><strong>' . htmlspecialchars($errorMessage) . "</strong></p>\n";
}

echo $html->link('Takaisin', '/calendar_events/manage');

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
