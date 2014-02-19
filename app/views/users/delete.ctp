<?php
/**
 * views/users/delete.ctp
 * Käyttäjien poiston käyttämä näkymä, kertoo toiminnon onnistumisesta.
 *
 * Pakolliset parametrit:
 * @param int $update_count poistettujen käyttäjien lukumäärä
 * 
 * @author Niko Kiirala
 * @package kurre
 * @license GNU General Public License v2
 */

echo '<p>Poistettu ';
echo $update_count;
if ($update_count == 1) {
    echo ' käyttäjä';
}
else {
    echo ' käyttäjää';
}
echo "</p>\n";

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
