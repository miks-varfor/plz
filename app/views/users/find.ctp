<?php
/**
 * views/users/find.ctp
 * Etsi käyttäjä -toiminnon antama listaus, jos löytyi useampia kuin yksi
 * käyttäjä.
 *
 * Pakolliset parametrit:
 * @param array $users löytyneet käyttäjät, CakePHP:n findAll -muodossa
 * taulusta users
 * 
 * Valinnaiset parametrit:
 * @param string $error virheilmoitus. Jos tämä on asetettu, käyttäjälistausta
 * ei näytetä lainkaan
 * 
 * @author Niko Kiirala
 * @package kurre
 * @license GNU General Public License v2
 */

if (isset($error)) {
	echo '<p>' . htmlspecialchars($error) . "</p>\n";
}
else {
	$num = count($users);
	if ($num == 0) {
		echo "<p>Hakuehdoilla ei löytynyt yhtään käyttäjää.</p>\n";
	}
	else {
		echo '<p>Hakuehdoilla löytyi ' . count($users) . " käyttäjää.</p>\n";
	}
	/* Yhden käyttäjän tilannetta ei tarvitse hallita, koska sen pitäisi
	 * ohjata suoraan käyttäjän muokkaukseen ("löytyi 1 käyttäjää") */

	echo "<table id=\"user_list_admin\">\n";
	echo "<tr>\n";
	echo "    <th>Nimi</th>\n";
	echo "    <th>Käyttäjätunnus</th>\n";
	echo "    <th>Sähköposti</th>\n";
	echo "</tr>\n";

	foreach ($users as $info) {
		echo "<tr>\n";
		echo '    <td>';
		echo $html->link($info['User']['screen_name'],
						 '/users/edit/' . $info['User']['id']);
		echo "</td>\n";
		echo '    <td>';
		echo htmlspecialchars($info['User']['username']);
		echo "</td>\n";
		echo '    <td>';
		echo htmlspecialchars($info['User']['email']);
		echo "</td>\n";
		echo "</tr>\n";
	}

	echo "</table>\n";
}

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
