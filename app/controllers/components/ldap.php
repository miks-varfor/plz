<?php
/**
* ldap.php
*
* @author Niko Kiirala
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Avustajaluokka käyttäjien tietojen hakemiseen LDAP-kannasta.
*
* @author Niko Kiirala
* @package Kurre
*/
class LdapComponent extends Object {
	/**
	 * Hakee käyttäjän tiedot yliopiston LDAP-hakemistosta käyttäjänimen
	 * perusteella.
	 * @param string $username etsittävä käyttäjätunnus. Oltava 3-8 merkkiä
	 * pitkä. Hyväksyttäviä merkkejä ovat kirjaimet a-z, numerot ja alaviiva.
	 * @return mixed Taulukko, joka sisältää käyttäjän tiedot tai false
	 * virhetilanteessa.
	 * Käyttäjän tiedot sisältävässä taulukossa on seuraavat alkiot
	 *   lastname - sukunimi
	 *   firstname - etunimi
	 *   name - koko nimi (sukunimi, etunimi ja muiden etunimien alkukirjaimet)
	 *   email - helsinki.fi -sähköpostiosoite
	 *   title - henkilön asema yliopistossa, esim. Opiskelija
	 *   department - laitos, jolla opiskelee
	 * Jos jotain näistä tiedoista käyttäjälle ei löytynyt, kyseinen
	 * kohta taulukosta sisältää tyhjän merkkijonon.
	 * Jos käyttäjää ei löytynyt ollenkaan, palautettu taulukko ei sisällä
	 * yhtään alkiota.
	 * @author Niko Kiirala
	 */
	function findUser($username) {
		
		// Disable LDAP for devel outside university network
		if(stripos($_SERVER['SERVER_NAME'], 'tko-aly.fi') === false){
			return false;
		}
		
		if (preg_match('/^[a-z0-9_]{3,8}$/', $username) !== 1) {
			return false;
		}

		$connection = @ldap_connect('ldap://ldap.helsinki.fi');
		if ($connection === false) {
			debug('LDAP-palvelimeen yhdistäminen epäonnistui');
			return false;
		}

		// LDAP BIND throws warning when server not available
		// so use @ to suppress it. It still takes ~forever to
		// timeout, though.
		$bind_result = @ldap_bind($connection);
		if ($bind_result === false) {
			debug('LDAP-palvelimelle kirjautuminen epäonnistui');
			return false;
		}

		$search = ldap_search($connection, 'o=hy', 'uid=' . $username,
							  array('sn', 'givenname', 'cn', 'mail',
									'title', 'ou'));
		if ($search === false) {
			debug('LDAP-hakemistosta etsiminen epäonnistui');
			return false;
		}

		$result = ldap_get_entries($connection, $search);
		if ($result === false) {
			debug('LDAP-haun tulosten noutaminen epäonnistui');
			return false;
		}
		if ($result['count'] == 0) {
			return array();
		}
		if ($result['count'] > 1) {
			debug('LDAP-haulla löytyi useita käyttäjiä samalla tunnuksella');
		}

		$enc = Configure::read('App.encoding');
		$ret = array();
		foreach (array('sn' => 'lastname', 'givenname' => 'firstname',
					   'cn' => 'name', 'mail' => 'email',
					   'title' => 'title', 'ou' => 'department')
				 as $ldap_name => $kurre_name) {
			if (isset($result[0][$ldap_name][0])) {
				$ret[$kurre_name] = iconv('UTF-8', $enc, $result[0][$ldap_name][0]);
			}
			else {
				$ret[$kurre_name] = '';
			}
		}

		return $ret;
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
