<?php
/**
* date_formatter.php
*
* @author Niko Kiirala, Lauri Auvinen
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Avustajaluokka päivämäärän muuntoon muodosta vvvv-kk-pp tt:mm:ss muotoon pp.kk.vvvv tt:mm
*
* @author Niko Kiirala, Lauri Auvinen
* @package Kurre
*/
class DateFormatterComponent extends Object {
	/**
	 * Muotoilee tietokannan antamassa muodossa olevan aikaleiman suomalaiseen
	 * muotoon. Sisältää sekä päivämäärän että kellonajan minuutin tarkkuudella.
	 * @param string $date_string tietokannasta saatu päivämäärä
	 * @return string päivämäärä ja kellonaika suomalaisessa muodossa minuuttien
	 * tarkkuudella tai tyhjän merkkijonon, jos muunnos ei onnistunut.
	 * @author Niko Kiirala
	 */
	function dateTime($date_string) {
		$date = strptime($date_string, '%Y-%m-%d %H:%M:%S');

		if ($date === false) {
			return '';
		}

		return $date['tm_mday'] . '.'
			. ($date['tm_mon'] + 1) . '.'
			. ($date['tm_year'] + 1900) . ' '
			. $date['tm_hour'] . ':'
			. ($date['tm_min'] < 10 ? '0' : '') . $date['tm_min'];
	}

	/**
	 * Muotoilee tietokannan antamassa muodossa olevan aikaleiman päivämääräosuuden
	 * suomalaiseen muotoon.
	 * @param string $date_string tietokannasta saatu päivämäärä
	 * @return string päivämäärän suomalaisessa muodossa tai tyhjän merkkijonon,
	 * jos muunnos ei onnistunut
	 * @author Niko Kiirala
	 */
	function date($date_string) {
		$date = strptime($date_string, '%Y-%m-%d %H:%M:%S');

		if ($date === false) {
			return '';
		}

		return $date['tm_mday'] . '.'
			. ($date['tm_mon'] + 1) . '.'
			. ($date['tm_year'] + 1900);
	}
	
	/**
	 * Muotoilee tietokannan antamassa muodossa olevan aikaleiman kellonaikaosuuden
	 * minuuttien tarkkuudella suomalaiseen muotoon.
	 * @param string $date_string tietokannasta saatu päivämäärä
	 * @return string kellonaika suomalaisessa muodossa minuuttien tarkkuudella
	 * tai tyhjän merkkijonon, jos muunnos ei onnistunut
	 * @author Niko Kiirala
	 */
	function time($date_string) {
		$date = strptime($date_string, '%Y-%m-%d %H:%M:%S');

		if ($date === false) {
			return '';
		}

		return $date['tm_hour'] . ':'
			. ($date['tm_min'] < 10 ? '0' : '') . $date['tm_min'];
	}
}
?>
