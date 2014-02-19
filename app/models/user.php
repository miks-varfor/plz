<?php
/**
* user.php
*
* @author Niko Kiirala
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka jäsenille
*
* @author Niko Kiirala
* @package Kurre
*/
class User extends AppModel {
	var $name = 'User';
	var $recursive = 0;

	var $hasMany =
	array(
		  'Payment' => array(
							 'ClassName' => 'Payment',
							 'foreignKey' => 'payer_id',
							 ),
		  'Payment' => array(
							 'ClassName' => 'Payment',
							 'foreignKey' => 'confirmer_id'
							 ),
		  'Registration' => array(
								  'ClassName' => 'Registration'
								  ),
		  'CalendarEvent' => array(
								   'ClassName' => 'CalendarEvent'
								   ) );
	
	var $hasAndBelongsToMany = 'Group';
	
	var $validate =
	array(
		  'username' =>
		  array('custom' =>
				array('rule' => array('between', 3, 100),
					  'required' => true,
					  'message' => 'Käyttäjätunnuksen on oltava 3-100 merkkiä pitkä'),
				'unique_username' =>
				array('rule' => 'unique_username',
					  'message' => 'Antamasi käyttäjätunnus on jo käytössä'),
				'ldap_check' =>
				array('rule' => 'ldap_check',
					  'message' => 'Antamasi käyttäjätunnus on varattu toiselle henkilölle Helsingin yliopiston verkossa') ),
		  'firstname' => array('rule' => array('minLength', 1),
						  'required' => true,
						  'message' => 'Etunimi puuttuu.'),
		  'lastname' => array('rule' => array('minLength', 1),
						  'required' => true,
						  'message' => 'Sukunimi puuttuu.'),
		  'screen_name' => array('rule' => array('minLength', 1),
								 'required' => true,
								 'message' => 'Kutsumanimi puuttuu.'),
		  'email' => array('rule' => 'email',
						   'required' => true,
						   'message' => 'Tarkista sähköpostiosoitteesi.'),
		  'residence' => array('rule' => array('minLength', 2),
							   'required' => true,
							   'message' => 'Aseta kotikuntasi'),
		  'phone' => array('rule' => array('custom', '/^((\+?\d+(-\d+)?)|(\(\d+\)\d*))(-\d+)?( \d+)*$/'),
						   'allowEmpty' => true,
						   'message' => 'Tarkista puhelinnumeron muoto. Jos et halua tallettaa puhelinnumeroa, jätä kenttä tyhjäksi.'),
		  'hyy_member' => array('rule' => 'boolean', 
								'required' => true),			
		  'membership' => array('rule' => 'membership',
								'required' => true),
		  'role' => array('rule' => 'role',
						  'required' => true),
		  'hashed_password' => array('rule' =>
									 array('custom', '/^[a-fA-F0-9]{40}$/'),
									 'required' => true),
		  //'salt' => array('required' => true),
		  'tktl' => array('rule' => 'boolean',
						  'required' => true),
		  'created' => array('rule' => array('custom', '/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/'),
							 'on' => 'update',
							 'required' => true,
							 'message' => 'Virheellinen liittymispäivä'),
		  'faculty' => array('rule' => array('minLenght', 2),
							 'allowEmpty' => true,
							 'message' => 'Tarkista tiedekuntasi')
		  );

	/**
	 * Tarkistaa käyttäjätunnuksen uniikkiuden.
	 * @param array $data taulukko, jonka ensimmäisenä alkiona
	 * tarkistettava käyttäjätunnus.
	 * @return boolean true, jos tunnus on uniikki, muuten false
	 * @author Niko Kiirala
	 */
	function unique_username($data) {
		reset($data);
		$username = current($data);
		$found = $this->find(array('User.username' => $username),
							 array('User.id', 'User.username'),
							 null, 0);
		if (empty($found)) {
			return true;
		}

		if (isset($found['User']['id']) && isset($this->data['User']['id'])
			&& $found['User']['id'] == $this->data['User']['id']) {
			return true;
		}

		return false;
	}

	/**
	 * Tarkistaa, vastaavatko käyttäjätunnus ja LDAP-tiedot toisiaan.
	 * Tiedot vastaavat toisiaan, jos LDAP-tietojen etu- ja sukunimi
	 * löytyvät osana käyttäjän nimeä.
	 * @param array $data taulukko, jonka ensimmäisenä alkiona tarkistettava
	 * käyttäjätunnus
	 * @return boolean false, jos käyttäjätunnus ja LDAP-tiedot eivät
	 * vastaa toisiaan. true, jos ne vastaavat tai LDAP-tiedot puuttuvat
	 * @author Niko Kiirala
	 */
	function ldap_check($data) {
		if (!isset($this->data['User']['ldap'])) return true;

		reset($data);
		$username = current($data);

		$ldap =& $this->data['User']['ldap'];
		if (empty($ldap)) {
			return true;
		}
		$user =& $this->data['User'];

		if (stripos($user['lastname'], $ldap['lastname']) !== false
			&& stripos($user['firstname'], $ldap['firstname']) !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Tarkistaa käyttäjänimen. Vaatii vähintään kaksi välilyönnillä
	 * erotettua osaa, ts. etu- ja sukunimen.
	 * @param array $data taulukko, jonka ensimmäisenä alkiona
	 * tarkistettava nimi
	 * @return boolean true, jos nimi on hyväksyttävä, muuten false
	 * @author Niko Kiirala
	 */
	function name($data) {
		reset($data);
		if (preg_match('/\S+( \S+)+/', current($data)) === 1)
			return true;
		else
			return false;
	}

	/**
	 * Tarkistaa totuusarvon. Hyväksyttyjä syötteitä ovat merkkijonot
	 * '0' ja '1'.
	 * @param array $data taulukko, jonka ensimmäisenä alkiona
	 * tarkistettava totuusarvo
	 * @return boolean true, jos totusarvo on hyväksyttävä, muuten false
	 * @author Niko Kiirala
	 */
	function boolean($data)
	{
		reset($data);
		if (current($data) == '0' || current($data) == '1')
			return true;
		else
			return false;
	}

	/**
	 * Tarkistaa jäsenyystason, jotta se on yksi käytetyistä
	 * merkkijonoista.
	 * @param array $data taulukko, jonka ensimmäisenä alkiona
	 * tarkistettava jäsenyystaso
	 * @return boolean true, jos jäsenyystaso on hyväksyttävä, muuten false
	 * @author Niko Kiirala
	 */
	function membership($data)
	{
		reset($data);
		$data = current($data);
		if ($data == 'ei-jasen' ||
		    $data == 'erotettu' ||
		    $data == 'ulkojasen' ||
		    $data == 'jasen' ||
		    $data == 'kannatusjasen' ||
		    $data == 'kunniajasen')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Tarkistaa käyttäjäroolin, jotta se on yksi käytetyistä
	 * merkkijonoista.
	 * @param array $data taulukko, jonka ensimmäisenä alkiona
	 * tarkistettava käyttäjärooli
	 * @return boolean true, jos käyttäjärooli on hyväksyttävä, muuten false
	 * @author Niko Kiirala
	 */
	function role($data)
	{
		reset($data);
		$data = current($data);
		if ($data == 'kayttaja' ||
		    $data == 'virkailija' ||
		    $data == 'jasenvirkailija' ||
		    $data == 'yllapitaja')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Hajauttaa salasanan ja luo sille suolan
	* @param string $password selkokielinen salasana
	* @return array hajautettu salasana ja suola
	* @author Juhani Markkula
	*/
	function encryptPassword($password) {
		$salt = $this->generateSalt();
		$hashed_password = $this->encryptPasswordWithSalt($password, $salt);
		
		return array(
			'hashed_password' => $hashed_password, 
			'salt' => $salt
		);
	}
	
	/**
	* Hajauttaa salasanan suolan kera sha1-algoritmilla
	* @param string $password selkokielinen salasana
	* @param string $salt salasanan suola
	* @return string hajautettu salasana
	* @author Juhani Markkula
	*/
	function encryptPasswordWithSalt($password, $salt) {
		return sha1($salt . 'kekbUr' . $password);
	}
	
	/**
	* Luo satunnaismerkkijonon salasanan suolaksi
	* Suola yksilöllinen jokaiselle käyttäjälle
	* @return string yksilöllinen suola
	* @author Juhani Markkula
	*/
	function generateSalt() {
		return substr(md5(time() + rand()), 0, 20);
	}
	
	
	/**
	* Tarkistaa onko käyttäjätunnus olemassa ja täsmääkö salasana siihen.
	* @param array $data käyttäjätunnus ja selkokielinen salasana 
	* @return array|false käyttäjän tiedot tai false, jos tunnusta ei ole 
	* olemassa tai salasana ei täsmää siihen.
	* @author Juhani Markkula
	*/
	function validateLogin($data)	{
		$user = $this->find(array('username' => $data['username']), array('id', 'username', 'hashed_password', 'salt', 'modified'));

		// Väärä käyttäjätunnus
		if(empty($user))
			return false;
		
		$password = $user['User']['hashed_password'];
		$given_password = $this->encryptPasswordWithSalt($data['password'], $user['User']['salt']);

		// Väärä salasana
		if($given_password != $password)
			return false;
		
		return $user['User'];
	}

	/**
	 * Vertaa kahta käyttäjäroolia. Palauttaa pienemmän kuin nolla,
	 * nollan tai suuremman kuin nolla, jos ensimmäinen käyttäjätaso
	 * on matalampi, yhtä suuri tai suurempi kuin toinen.
	 * Toisin sanoen, jos user.role olisi suoraan verrattavissa,
	 * tämän funktion kutsu
	 * compareUserRole($user1.role, $user2.role) < 0
	 * vastaisi vertailua
	 * $user1.role < $user2.role
	 * @param string $a ensimmäisen käyttäjän rooli
	 * @param string $b toisen käyttäjän rooli
	 * @return int vertailun tulos
	 * @author Niko Kiirala
	 */
	function compareUserRole($a, $b)
	{
		$role_numbers = array('kayttaja' => 1,
				      'virkailija' => 2,
				      'tenttiarkistovirkailija' => 2,
				      'jasenvirkailija' => 3,
				      'yllapitaja' => 4);

		$a_n = 0;
		$b_n = 0;

		if (isset($role_numbers[$a]))
			$a_n = $role_numbers[$a];

		if (isset($role_numbers[$b]))
			$b_n = $role_numbers[$b];

		if ($a_n < $b_n)
			return -1;
		else if ($a_n > $b_n)
			return 1;
		else
			return 0;
	}

	/**
	 * Palauttaa taulukkona annettua tunnistetta vastaavan joukon
	 * SQL-kyselypalasia, joista muodostetaan listSelected-funktiossa
	 * SQL-haun kyselyosa yhdistämällä ne AND-operaatiolla.
	 * Tuetut hakuehdot:
	 *   paid - käyttäjän jäsenmaksu on voimassa
	 *   nonpaid - käyttäjän jäsenmaksu ei ole voimassa
	 *   nonmember - käyttäjä ei ole jäsen
	 *   member - käyttäjä on jäsen
	 *   revoked - käyttäjä on erotettu
	 * @param string $condition kyselyehdon tunniste
	 * @author Niko Kiirala
	 */
	private function parse_search_condition($condition) {
		if ($condition == 'paid') {
			// Muutettu 20100303 / dogo
			// return array('Payment.paid IS NOT NULL');
			return array('Payment.paid IS NOT NULL AND Payment.valid_until >= (SELECT(NOW()))');
		}
		else if($condition == 'paid_or_recent') {
			// Lisätty 20110929 / wox, hyväksytään maksetuiksi laskut joiden luonnista on alle 14 päivää
			return array('Payment.valid_until >= NOW() AND (Payment.paid IS NOT NULL OR DATEDIFF(NOW(),Payment.created) <= 14 )');
		}
		else if($condition == 'paid_or_new') {
			return array("Payment.paid IS NOT NULL AND (Payment.valid_until >= NOW() OR User.membership = 'ei-jasen')");
		}
		else if ($condition == 'nonpaid') {
			
			return array('(Payment.paid IS NULL OR Payment.valid_until < NOW()) AND User.membership!=\'kunniajasen\'');
		}
		else if ($condition == 'nonmember') {
			return array('User.membership NOT IN (\'ulkojasen\', \'jasen\', \'kannatusjasen\', \'kunniajasen\')');
		}
		else if ($condition == 'member') {
			return array('User.membership IN (\'ulkojasen\', \'jasen\', \'kannatusjasen\', \'kunniajasen\')');
		}
		else if ($condition == 'revoked') {
			return array('User.membership = \'erotettu\'');
		}
		else {
			return array();
		}
	}

	/**
	 * Hakee kannasta annettujen hakuehtojen mukaiset käyttäjät.
	 * Jos hakuehtoja ei ole annettu, haetaan kaikki käyttäjät.
	 * Jos on annettu useita hakuehtoja, näytetään ne käyttäjät, joihin
	 * sopivat kaikki hakuehdot.
	 * Hakuehtojen keskinäinen järjestys ei ole merkitsevä.
	 * Tuetut hakuehdot: (funktiosta parse_search_condition)
	 *   paid - käyttäjän jäsenmaksu on voimassa
	 *   nonpaid - käyttäjän jäsenmaksu ei ole voimassa
	 *   nonmember - käyttäjä ei ole jäsen
	 *   member - käyttäjä on jäsen
	 *   revoked - käyttäjä on erotettu
	 * @param array $input_conditions halutut hakuehdot
	 * @param array $fields halutut kentät tauluista User ja Payment
	 * @return array löytyneistä käyttäjistä halutut kentät
	 */
	function userList($input_conditions, $fields) {
		$conditions = array('User.deleted <> \'1\'');
		foreach($input_conditions as $cond) {
			$conditions = array_merge($conditions,
									  $this->parse_search_condition($cond));
		}

		$query = 'SELECT ' . implode(', ', $fields) .
			' FROM users as User LEFT OUTER JOIN payments as Payment ON (User.id = Payment.payer_id  AND Payment.id = (SELECT MAX(id) FROM payments p2 WHERE p2.payer_id = User.id))';
		if (!empty($conditions)) {
			$query .= ' WHERE ';
			$query .= implode(' AND ', $conditions);
		}
		// TODO: pitäisi olla valittavissa
		$query .= ' ORDER BY User.lastname, User.firstname';
		$results = $this->query($query);
		return $results;
	}
	
	/**
	 * Kertoo onko annettu käyttäjä jonkinasteinen jäsen.
	 *
	 * @param array $user käyttäjän tiedot
	 * return boolean onko käyttäjä jäsen
	 */
	function isMember($user) {
		if(isset($user['User'])) {
			$isMember = $user['User']['membership'] != 'ei-jasen' && $user['User']['membership'] != 'erotettu';
		}
		else {
			$isMember = $user['membership'] != 'ei-jasen' && $user['membership'] != 'erotettu';
		}
		
		return $isMember;
	}

	/**
        * Kertoo onko käyttäjä ei-erotettu
        *
        * @param array $user käyttäjän tiedot
        * return boolean onko käyttäjä ei-erotettu
	*/
	function canRegister($user) {
		if(isset($user['User'])) {
                        $canRegister = $user['User']['membership'] != 'erotettu';
                }
                else {
                        $canRegister = $user['membership'] != 'erotettu';
                }

                return $canRegister;
	}

        /**
        *       Kertoo voiko käyttäjä ilmoittautua tapahtumaan.
        *       jäsenyystaso voi olla 'erotettu' jos käyttäjälle on jo rekisteröity
        *       jäsenmaksu maksetuksi. Tämä on tekninen muotoseikka ja odotellaan ennakkotapausta,
        *       jossa maksanutta jäsentä ei hyväksytäkään jäseneksi. Tällöin jäsenmaksu palautettaneen
        *       (?) mutta jos tapahtumaan on jo osallistuttu, pitänee miettiä halutaanko tästä periä
        *       jokin maksu. Lisäksi tulee ihmettely siitä, onko tämä ei-jäsen vienyt jonkun erityisesti
        *       tapahtumaan ilmoittautuneen paikan. Mietitään sitä sitten kun asia on ajankohtainen.
		*       
		*		Luotu ünd lisätty 20100304:0030 / dogo
		*		Muutettu tarkastamaan myös ei-jasenet 20110929 / wox
        *
        * @param array $user käyttäjän tiedot
        * return boolean onko käyttäjä ei-erotettu tai erotettu, mutta jäsenmaksun maksanut
        * */
        function canParticipateEvent($user) {
                if(isset($user['User'])) {
					$canParticipateEvent = ($user['User']['membership'] != 'erotettu') && ($user['User']['membership'] != 'ei-jasen');
                }
                else {
                    $canParticipateEvent = ($user['membership'] != 'erotettu') && ($user['membership'] != 'ei-jasen') ;
                }

// If membership was revoked, the user might have a registered payment. In that case,
// we'll let the user to participate to members only events.
// This is marginal case, when there has not yet been meeting of the board to accept the
// applicant as member of TKO-äly ry.

                if (!$canParticipateEvent) {
                    $revokedButPaid = $this->userList( array('paid_or_recent', 'nonmember'), array('username') ); 

// revodekButPaid is now array of arrays of arrays.
// First foreach iterates through the arrays which contain arrays which contain arrays of 'username's.

                    foreach($revokedButPaid as $key => $val) {

// Second foreach iterates through the arrays containing arrays of 'username's.

                        foreach($revokedButPaid[$key] as $key2 => $val2) {

// And third foreach iterates through the array containing 'username's.
// If parameter $user has element 'username' set, we'll see if it matches
// to a username that is known to be of status revoked but who has paid
// membership fee, we'll let this user participate to the event that is
// members only (i.e. set the flag-variable as true).

                            foreach($revokedButPaid[$key][$key2] as $key3 => $val3) {
                                if (isset($user['username']) && $val3 == $user['username']) {
//                                    $string .= "\nFOUND: user with status 'revoked'; allowing to participate on event.\n\n";
                                    $canParticipateEvent = true;
                                }
                            }

                        }
                    }
                }
                return $canParticipateEvent;
        }

	

	/**
	 * Palauttaa käyttäjän maksettavan jäsenyysjakson tyypin.
	 * Ei-jäsenet ja erotetut maksavat normaalijäsenen hinnan, muut
	 * oman jäsentyyppinsä mukaan.
	 * @param string $membership käyttäjän jäsentyyppi
	 * @return string jäsentyyppi, jonka mukaan maksaa jäsenmaksun tai false,
	 * jos jäsentyyppi on virheellinen
	 * @author Niko Kiirala
	 */
	function paymentMembership($membership) {
		switch ($membership) {
		case 'ei-jasen':
		case 'erotettu':
			return 'jasen';
		case 'jasen':
		case 'ulkojasen':
		case 'kannatusjasen':
		case 'kunniajasen':
			return $membership;
		default:
			return false;
		}
	}
	
	/**
	 * Asettaa käyttäjälle uuden satunnaistetun salasanan.
	 * 
	 * @param array $user käyttäjän tiedot
	 * @return string uusi salasana
	 * @author Juhani Markkula
	 */
	function resetPassword($user) {
		if(isset($user['User'])) $user = $user['User'];
		
		//uses('neat_string'); 
		//$password = NeatString::randomPassword(8, 'abcdefghijklmnopqrstuvwxyzABDEFHKMNPRTWXYABDEFHKMNPRTWXY23456789');
		$password = $this->generateRandomString();
		$hashed = $this->encryptPasswordWithSalt($password, $user['salt']);
		
		$this->id = $user['id'];
		$this->saveField('hashed_password', $hashed, false);
		
		return $password;
	}

	/**
	 * Generate random string for password reset
	 * Snatched from http://planetcakephp.org/aggregator/items/3400-updated-random-string-generator-cakephp-component
	 * WoX / 20100914
	 */
	function generateRandomString($length = 8, $possible = '0123456789abcdefghijklmnopqrstuvwxyz') {
		// initialize variables
		$password = "";
		$i = 0;
 
		// add random characters to $password until $length is reached
		while ($i < $length) {
			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
 
			// we don't want this character if it's already in the password
			if (!strstr($password, $char)) { 
				$password .= $char;
				$i++;
			}
		}
		return $password;
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
