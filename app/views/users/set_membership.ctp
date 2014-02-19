<?php
/**
 * views/users/set_membership.ctp
 * Jäsenyystason muokkauksen käyttämä näkymä. Kertoo muokattujen käyttäjien
 * määrän ja uuden jäsenyystason.
 *
 * Pakolliset parametrit:
 * @param int $update_count muokattujen käyttäjien lukumäärä
 * @param string $new_membership uusi jäsenyystaso
 *
 * Valinnaiset parametrit:
 * @param string $error virheilmoitus. Jos tämä on asetettu, muita tietoja
 * ei näytetä.
 * 
 * @author Niko Kiirala
 * @package kurre
 * @license GNU General Public License v2
 */

if (isset($error)) {
	echo '<p>' . htmlspecialchars($error) . "</p>\n";
}
else {
	echo '<p>Päivitetty ';
	echo $update_count;
	echo ' käyttäjän uudeksi jäsenyystasoksi ';
	echo htmlspecialchars($format->membership($new_membership));
	echo "</p>\n";
}

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
