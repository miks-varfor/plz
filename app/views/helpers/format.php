<?php
/**
 * format.php
 *
 * @author Niko Kiirala
 * @package kurre
 * @license GNU General Public License v2
 */

/**
 * Avustajaluokka tietokannasta tulevien tietojen muokkaamiseen käyttäjälle
 * sopivaan muotoon.
 *
 * @author Niko Kiirala
 * @package kurre
 */
class FormatHelper extends Helper {
	/**
	 * Muotoilee tietokannan antamassa muodossa olevan aikaleiman päivämääräosuuden
	 * suomalaiseen muotoon.
	 * @param string $date_string tietokannasta saatu päivämäärä
	 * @param boolean $weekday halutaanko viikonpäivän kaksikirjaiminen lyhenne (esim Ma) mukaan vai ei, oletuksena false
	 * @return string päivämäärä suomalaisessa muodossa tai tyhjän merkkijonon,
	 * jos muunnos ei onnistunut
	 * @author Niko Kiirala
	 */
	function date($date_string, $weekday=false) {
		$date = strptime($date_string, '%Y-%m-%d %H:%M:%S');

		if ($date === false) {
			return '';
		}

		$wday_fin = array ("Su", "Ma", "Ti", "Ke", "To", "Pe", "La");
		$ret_str = "";
		if($weekday)
			 $ret_str = $wday_fin[date("w", strtotime($date_string))] . " ";			
		return $ret_str .  $date['tm_mday'] . '.'
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
	 * Muotoilee tietokannan antamassa muodossa olevan totuusarvon
	 * merkkijonoksi 'kyllä' tai 'ei'.
	 * @param string $bool_string tietokannan antamassa muodossa oleva
	 * totuusarvo
	 * @return string merkkijono 'kyllä' tai 'ei', jos parametri oli
	 * tunnistettava totuusarvo, muutoin tyhjän merkkijonon
	 * @author Niko Kiirala
	 */
	function boolean($bool_string) {
		if ($bool_string == '1') {
			return 'kyllä';
		}
		else if ($bool_string == '0') {
			return 'ei';
		}
		else {
			return '';
		}
	}

	/**
	 * Muotoilee tietokannassa olevassa muodossa olevan jäsentason
	 * vastaavaksi suomen sanaksi
	 * @param string $membership tietokannan antamassa muodossa oleva
	 * jäsentaso
	 * @return string suomenkielinen jäsentaso tai alkuperäinen parametri,
	 * jos jäsentasoa ei tunnistettu
	 * @author Niko Kiirala
	 */
	function membership($membership) {
		$translate = array('ei-jasen' => 'Ei jäsen',
						   'erotettu' => 'Erotettu',
						   'ulkojasen' => 'Ulkojäsen',
						   'jasen' => 'Jäsen',
						   'kannatusjasen' => 'Kannatusjäsen',
						   'kunniajasen' => 'Kunniajäsen');
		if (isset($translate[$membership])) {
			return $translate[$membership];
		}
		else {
			return $membership;
		}
	}
}

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
