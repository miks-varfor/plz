<?php
/**
 * externals_controller.php
 *
 * @author Samu Kytöjoki
 * @package Kurre
 * @version 1.0
 * @license GNU General Public License v2
 */

/**
 * Ulkoisen rajapinnan ohjain
 *
 * Kutakin metodia kutsutaan osoitteella joko on muotoa
 * http://domain.net/kurre/externals/metodinNimi/param1/param2/param3 jne.
 * Tällöin metodi metodinNimi saa parametrikseeen arvot param1, param2, param3
 * siinä järjestyksessä jossa parametrit on määritelty metodin nimessä.
 *
 * Esimerkiksi kutsu
 * http://domain.net/kurre/externals/getUserInfo/tunnus/salasana kutsuu metodia
 * getUserInfo parametreillä $username = tunnus ja $password = salasana.
 *
 * Kukin metodi palauttaa XML-muotoisen vastauksen joka on muotoa
 * <pre>
 * &lt;response&gt;
 * 	&lt;status&gt;
 * 		&lt;code&gt;int&lt;/code&gt;
 * 		&lt;code&gt;string&lt;/code&gt;
 * 	&lt;/status&gt;
 * 	&lt;array&gt;
 *		...
 * 	&lt;/array&gt;
 * &lt;/response&gt;
 * </pre>
 * Jos code == 0, niin komento onnistui.
 * Muussa tapauksessa tapahtui jokin virhe jota message kuvaa.
 *
 * @author Samu Kytöjoki
 * @package Kurre
 */
class ExternalsController extends AppController {

	var $name = 'Externals';
	var $helpers = array('Html', 'Form');
	var $uses = array('User', 'Group', 'GroupsUser', 'Pricing', 'Payment',
		'CalendarEvent', 'Registration', 'CustomField', 'CustomFieldAnswer');
	var $webservices = true;
	var $layout = 'xml';

	/**
	 * Kutsutaan automaattisesti ennen kutakin julkista metodia
	 */
	function beforeFilter() {
		$this->setMessage(-1, 'Viestiä ei ole asetettu');
	}

	/**
	 * Asettaa statuskoodin ja viestin vastauksiin
	 *
	 * @param int $status statuskoodi
	 * @param string $message tilanteen selkokielinen selitys
	 */
	private function setMessage($status = null, $message = null) {
		$this->set('status', $status);
		$this->set('message', $message);
	}

	/**
	 * Tarkastaa käyttäjän salasanan
	 *
	 * @param string $username käyttäjätunnus
	 * @param string $password salasana
	 * @returns mixed käyttäjän tiedot taulukossa tai arvon false, jos salasana on virheellinen
	 */
	private function checkUser($username = null, $password = null) {
		return $this->User->validateLogin(array('username' => $username, 'password' => $password));
	}

	/**
	 * Hakee käyttäjän tiedot
	 *
	 * Palauttaa käyttäjän tiedot taulukossa ja statuskoodin
	 *
	 * @param string $username käyttäjätunnus
	 * @param string $password salasana
	 */
	function getUserInfo($username = null, $password = null) {

		// Tarkista käyttäjän salasana
		if ($this->checkUser($username, $password)) {
			$data = $this->User->findByUsername($username);
			$this->set('data', $data);
			$this->setMessage(0, 'Komento suoritettu');
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Tarkistaa, onko käyttäjä jäsen
	 *
	 * Palauttaa kysytyn käyttäjänimen ja onko käyttäjä jäsen
	 *
	 * @author aoforsel
	 * @param string $username käyttäjätunnus
	 */
	function isMember($username = null) {
		$user = $this->User->findByUsername($username);
		$isMember = false;
		if ($user) {
			$isMember = User::isMember($user);
		}
		$data['username'] = $username;
		$data['timestamp'] = time() . '';
		$data['ismember'] = $isMember?'true':'false';
		$this->set('data', $data);
		$this->setMessage(0, 'Komento suoritettu');
	}
	
	/**
	 * Tarkastaa onko käyttäjä tenttiarkistovirkailija.
	 * Palauttaa tiedon ja asettaa statuskoodin sen mukaan.
	 * Lisätty 2011-09-08
	 *
	 * @author wox
	 */
	function isExamOfficer($username = null){
		$user = $this->User->findByUsername($username);
		$isExamOfficer = false;
		if ($user && ($user['User']['role'] == 'tenttiarkistovirkailija' || 
				$user['User']['role'] == 'yllapitaja')) {
			$isExamOfficer = true;
		}
		if($isExamOfficer){
			$this->header('HTTP/1.1 200 OK');
		}
		else{
			$this->header('HTTP/1.1 403 Forbidden');
		}
		$data['username'] = $username;
		$data['timestamp'] = time() . '';
		$data['isExamOfficer'] = $isExamOfficer?'true':'false';
		$this->set('data', $data);
		$this->setMessage(0, 'Komento suoritettu');	
	}
	
	/**
	 * Lisää uuden käyttäjän järjestelmään
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param string $username yliopiston käyttäjätunnus
	 * @param string $password haluttu salasana
	 * @param string $surname sukunimi
	 * @param string $firstNames etunimet
	 * @param string $callName kutsumanimi
	 * @param string $email sähköpostiosoite
	 * @param string $phone puhelinnumero
	 * @param string $residence kotikunta
	 * @param boolean $hyyMember onko HYY:n jäsen (1 = true, 0 = false)
	 * @param boolean $tktl onko tietojenkäsittelytieteen opiskelija (1 = true, 0 = false)
	 */
	function createUser($username = null, $password = null, $surname = null,
		$firstNames = null, $callName = null, $email = null, $phone = null,
		$residence = null, $hyyMember = null, $tktl = null) {

		$data = $this->User->create();

		// Siirrä annetut parametrit taulukkoon
		$data['User']['username'] = $username;
		$data['User']['email'] = $email;
		$data['User']['phone'] = $phone;
		$data['User']['residence'] = $residence;
		$data['User']['hyy_member'] = $hyyMember;
		$data['User']['tktl'] = $tktl;

		// Aseta oletustiedot joihin käyttäjä ei voi vaikuttaa
		$data['User']['membership'] = 'ei-jasen';
		$data['User']['role'] = 'kayttaja';
		$data['User']['deleted'] = false;

		// Muodosta koko nimi etu- ja sukunimestä
		$data['User']['name'] = $firstNames . ' ' . $surname;
		$data['User']['screen_name'] = $callName . ' ' . $surname;

		// Aseta hajautettu salasana
		$pass = $this->User->encryptPassword($password);
		$data['User']['salt'] = $pass['salt'];
		$data['User']['hashed_password'] = $pass['hashed_password'];

		if ($this->User->save($data)) {
			$this->setMessage(0, 'Komento suoritettu');
		}
		else {
			// Rakenna virheilmoitus virheellisistä kentistä
			$errors = $this->User->invalidFields();
			$message = 'Tietoja ei voitu tallentaa: ';
			foreach ($errors as $error) {
				$message = $message . "\n" . $error;
			}
			$this->setMessage(1, $message);
		}
	}

	/**
	 * Muokkaa haluttua käyttäjätietokenttää
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param string $username käyttäjätunnus
	 * @param string $password salasana
	 * @param string $field muutettavan kentän nimi
	 * @param string $value uusi arvo
	 */
	function modifyUser($username = null, $password = null, $field = null, $value = null) {

		// Kentät joita voi muokata
		$modify = array('screen_name', 'email', 'residence',
			'phone', 'hyy_member', 'tktl', 'password');

		// Tarkista käyttäjän salasana
		if ($this->checkUser($username, $password)) {
			$data = $this->User->findByUsername($username);

			// Tarkista saako käyttäjä muokata haluttua kenttää
			if (in_array($field, $modify)) {

				if ($field == 'password') {
					// Aseta hajautettu salasana
					$pass = $this->User->encryptPassword($value);
					$data['User']['salt'] = $pass['salt'];
					$data['User']['hashed_password'] = $pass['hashed_password'];
				}
				else {
					$data['User'][$field] = $value;
				}

				// Yritä tallentaa muutos
				if ($this->User->save($data)) {
					$this->setMessage(0, 'Komento suoritettu');
				}
				else {
					// Rakenna virheilmoitus virheellisistä kentistä
					$errors = $this->User->invalidFields();
					$message = 'Tietoja ei voitu tallentaa: ';
					foreach ($errors as $error) {
						$message = $message . "\n" . $error;
					}
					$this->setMessage(3, $message);
				}
			}
			else {
				$this->setMessage(2, 'Sinulla ei ole oikeutta muokata kenttää');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Hakee järjestelmään tallennetut sähköpostilistat
	 *
	 * Palauttaa sähköpostilistojen tiedot taulukossa ja statuskoodin
	 */
	function getMailingLists() {

		if (($data = $this->Group->findAll()) != null) {
			$this->set('data', $data);
			$this->setMessage(0, 'Komento suoritettu');
		}
		else {
			$this->setMessage(1, 'Sähköpostilistoja ei löytynyt');
		}
	}

	/**
	 * Lisää käyttäjän sähköpostilistalle
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param string $username käyttäjärunnus
	 * @param string $password salasana
	 * @param int $list sähköpostilistan tunnistenumero
	 */
	function joinMailingList($username = null, $password = null, $list = null) {

		// Tarkista käyttäjän salasana
		if ($user = $this->checkUser($username, $password)) {

			// Tarkista löytyykö haluttu lista
			if (is_numeric($list) && $this->Group->findById($list)) {

				// Tarkista onko käyttäjä jo nyt listalla
				if ($this->GroupsUser->findByUserIdAndGroupId($user['id'], $list) == null) {

					// Lisää käyttäjä listalle
					$query = sprintf("INSERT INTO groups_users VALUES ('%d', '%d');",
						$user['id'], $list);
					$this->GroupsUser->query($query);
					$this->setMessage(0, 'Komento suoritettu');
				}
				else {
					$this->setMessage(3, 'Olet jo valmiiksi tällä listalla');
				}
			}
			else {
				$this->setMessage(2, 'Sähköpostilistaa ei löytynyt');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Poistaa käyttäjän sähköpostilistalta
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param string $username käyttäjärunnus
	 * @param string $password salasana
	 * @param int $list sähköpostilistan tunnistenumero
	 */
	function leaveMailingList($username = null, $password = null, $list = null) {

		// Tarkista käyttäjän salasana
		if ($user = $this->checkUser($username, $password)) {

			// Tarkista löytyykö haluttu lista
			if (is_numeric($list) && $this->Group->findById($list)) {

				// Tarkista onko käyttäjä listalla
				if ($this->GroupsUser->findByUserIdAndGroupId($user['id'], $list) != null) {

					// Poista käyttäjä listalta
					$query = sprintf("DELETE FROM groups_users WHERE user_id = '%d' AND group_id = '%d';",
						$user['id'], $list);
					$this->GroupsUser->query($query);
					$this->setMessage(0, 'Komento suoritettu');
				}
				else {
					$this->setMessage(3, 'Et ole tällä listalla');
				}
			}
			else {
				$this->setMessage(2, 'Sähköpostilistaa ei löytynyt');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Hakee jäsenmaksukausien tiedot
	 *
	 * Palauttaa jäsenmaksukaudet taulukossa ja statuskoodin
	 *
	 * @param string $type jäsentyyppi
	 */
	function getMembershipPricings($type = null) {

		if ($type != null) {
			$data = $this->Pricing->findAllByMembership($type);
		}
		else {
			$data = $this->Pricing->findAll();
		}

		if ($data != null) {
			$this->set('data', $data);
			$this->setMessage(0, 'Komento suoritettu');
		}
		else {
			$this->setMessage(1, 'Jäsentyyppiä vastaavia jäsenyysjaksoja ei löytynyt');
		}
	}

	/**
	 * Tilaa käyttäjälle jäsenmaksulaskun
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param string $username käyttäjätunnus
	 * @param string $password salasana
	 * @param int $season jäsenkauden pituus vuosina
	 */
	function orderInvoice($username = null, $password = null, $season = null) {

		// Tarkista käyttäjän salasana
		if ($this->checkUser($username, $password)) {
			$user = $this->User->findByUsername($username);

			// Tarkista onko käyttäjällä voimassa oleva jäsenmaksu
			if ($this->Payment->getUserPayment($user['User']['id']) == false) {

				// Yritä luoda jäsenmaksulasku käyttäjälle
				if ($this->Payment->addBankPayment($user['User']['id'], $season,
					$user['User']['membership']) != false) {
					$this->setMessage(0, 'Komento suoritettu');
				}
				else {
					$this->setMessage(3, 'Jäsenmaksulaskua ei voitu luoda');
				}
			}
			else {
				$this->setMessage(2, 'Sinulla on jo voimassa oleva jäsenmaksu');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Näyttää käyttäjän viimeisimmän jäsenmaksulaskun
	 *
	 * Palauttaa jäsenmaksun tiedot ja statuskoodin
	 *
	 * @param string $username käyttäjätunnus
	 * @param string $password salasana
	 */
	function showInvoice($username = null, $password = null) {

		// Tarkista käyttäjän salasana
		if ($user = $this->checkUser($username, $password)) {

			// Etsi käyttäjän viimeisin jäsenmaksulasku
			if ($data = $this->Payment->findByPayerId($user['id'], null, 'created DESC')) {
				$this->set('data', $data);
				$this->setMessage(0, 'Komento suoritettu');
			}
			else {
				$this->setMessage(2, 'Jäsenmaksulaskua ei löytynyt');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Hakee tapahtumakalenterin tapahtumat aikaväliltä
	 *
	 * Palauttaa kalenteritapahtumien tiedot taulukossa ja statuskoodin
	 *
	 * @param string $startDate päivämäärä josta eteenpäin tapahtumia näytetään muodossa 'VVVV-KK-PP'
	 * @param string $endDate päivämäärä johon asti tapahtumia näytetään 'VVVV-KK-PP'
	 */
	function browseCalendar($startDate = null, $endDate = null) {

		$conditions = array('CalendarEvent.deleted !=' => '1',
							'CalendarEvent.template !=' => '1');
		$ok = true;

		// Tarkista alkamispäivämäärä
		if ($startDate != null) {
			if ($this->Payment->isDate($startDate)) {
				$conditions['starts >='] = "'$startDate'";
			}
			else {
				$ok = false;
			}
		}
		else{
			
			$conditions['starts >='] = date('Y-m-d H:i:s', strtotime("-1 hour"));
		}

		// Tarkista loppumispäivämäärä
		if ($endDate != null) {
			if ($this->Payment->isDate($endDate)) {
				$conditions['starts <='] = "'$endDate'";
			}
			else {
				$ok = false;
			}
		}
		
		

		// Parametrejä ei annettu tai ne olivat kunnossa
		if ($ok) {
			if ($data = $this->CalendarEvent->find('all', 
					array('conditions' => $conditions, 'recursive' => 0, 'order' => 'starts'))) {
				$this->set('data', $data);
				$this->setMessage(0, 'Komento suoritettu');
			}
			else {
				$this->setMessage(2, 'Ehtoja vastaavia tapahtumia ei löytynyt');
			}
		}
		else {
			$this->setMessage(1, 'Tarkista päivämäärän muoto');
		}
	}

	/**
	 * Hakee tapahtuman perustiedot
	 *
	 * Palauttaa tapahtuman perustiedot taulukossa ja statuskoodin
	 *
	 * @param int $event tapahtuman tunnistenumero
	 */
	function getEventInfo($event = null) {

		if (is_numeric($event) && $data = $this->CalendarEvent->findById($event)) {
			$this->set('data', $data);
			$this->setMessage(0, 'Komento suoritettu');
		}
		else {
			$this->setMessage(1, 'Tapahtumaa ei löytynyt');
		}
	}

	/**
	 * Hakee tapahtuman lisätietokenttien tiedot
	 *
	 * Palauttaa tapahtuman lisätietokentät taulukossa ja statuskoodin
	 *
	 * @param int $event tapahtuman tunnistenumero
	 */

	function getEventInfoFields($event = null) {

		if (is_numeric($event) && $data = $this->CustomField->findAllByCalendarEventId($event)) {
			$this->set('data', $data);
			$this->setMessage(0, 'Komento suoritettu');
		}
		else {
			$this->setMessage(1, 'Tapahtumaa ei löytynyt');
		}
	}

	/**
	 * Listaa tapahtumaan ilmoittautuneet käyttäjät
	 *
	 * Palauttaa ilmoittautuneiden tiedot taulukossa ja statuskoodin
	 *
	 * @param int $event tapahtuman tunnistenumero
	 */

	function listParticipants($event = null) {

		// Tarkista löytyyko tapahtuma
		if (is_numeric($event) && $event = $this->CalendarEvent->findById($event)) {

			// Tarkista onko tapahtumaan mahdollista ilmoittautua
			if ($event['CalendarEvent']['registration_starts'] != null) {

				// Hae tapahtumaan ilmoittautuneet
				if ($data = $this->Registration->findAllByCalendarEventId(
					$event['CalendarEvent']['id'],
					array('id', 'user_id', 'calendar_event_id', 'name'))) {
					$this->set('data', $data);
					$this->setMessage(0, 'Komento suoritettu');
				}
				else {
					$this->setMessage(3, 'Ilmoittautuneita ei löytynyt');
				}
			}
			else {
				$this->setMessage(2, 'Tapahtumaan ei voi ilmoittautua');
			}
		}
		else {
			$this->setMessage(1, 'Tapahtumaa ei löytynyt');
		}
	}

	/**
	 * Hakee oman ilmoittautumisen tiedot
	 *
	 * Palauttaa ilmoittautumisen tiedot taulukossa ja statuskoodin
	 *
	 * @param string $username käyttäjätunnus
	 * @param string $password salasana
	 * @param int $event tapahtuman tunnistenumero
	 */
	function getParticipationInfo($username = null, $password = null, $event = null) {

		// Tarkista käyttäjän salasana
		if ($user = $this->checkUser($username, $password)) {

			// Tarkista löytyykö tapahtuma
			if (is_numeric($event) && $event = $this->CalendarEvent->findById($event)) {

				$registration = $this->Registration->findByUserIdAndCalendarEventId(
					$user['id'], $event['CalendarEvent']['id']);
				$data = $this->CustomFieldAnswer->findAllByRegistrationId(
					$registration['Registration']['id']);

				if ($data != null) {
					$this->set('data', $data);
					$this->setMessage(0, 'Komento suoritettu');
				}
				else {
					$this->setMessage(3, 'Ilmoittautumista ei löytynyt');
				}
			}
			else {
				$this->setMessage(2, 'Tapahtumaa ei löytynyt');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Ilmoittautuu tapahtumaan
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param string $username käyttäjätunnus
	 * @param string $password salasana
	 * @param int $event tapahtuman tunnistenumero
	 * @param string $avecName avecin nimi
	 * @param string $avecEmail avecin sähköposti
 	 * @param string $avecPhone avecin puhelinnumero
	 */
	function participateToEvent($username = null, $password = null, $event = null,
		$avecName = null, $avecEmail = null, $avecPhone = null) {

		// Tarkista käyttäjän salasana
		if ($this->checkUser($username, $password)) {
			$user = $this->User->findByUsername($username);

			//Tarkista löytyykö tapahtuma
			if (is_numeric($event) && $event = $this->CalendarEvent->findById($event)) {

				// Tarkista onko käyttäjä ilmoittautunut jo tapahtumaan
				if ($this->Registration->findByUserIdAndCalendarEventId(
					$user['User']['id'], $event['CalendarEvent']['id']) == null) {

					// Tarkista voiko tapahtumaan ilmoittautua nyt
					if ($this->CalendarEvent->isRegistrableNow($event)) {

						// Aseta tiedot tallentamista varten
						$data['Registration']['calendar_event_id'] = $event['CalendarEvent']['id'];
						$data['Registration']['user_id'] = $user['User']['id'];
						$data['Registration']['name'] = $user['User']['screen_name'];
						$data['Registration']['email'] = $user['User']['email'];
						$data['Registration']['phone'] = $user['User']['phone'];
						$data['Registration']['paid'] = null;

						// Tallenna normaali ilmodata
						if ($this->Registration->save($data)) {

							// Tarkista voiko tapahtumaan ilmoittaa avecin
							if ($event['CalendarEvent']['avec'] && $avecName != null) {
								$id = $this->Registration->id;

								// Aseta avecin tiedot tallentamista varten
								$avec['Registration']['id'] = null;
								$avec['Registration']['calendar_event_id'] = $event['CalendarEvent']['id'];
								$avec['Registration']['avec_id'] = $id;
								$avec['Registration']['name'] = $avecName;
								$avec['Registration']['email'] = $avecEmail;
								$avec['Registration']['phone'] = $avecPhone;
								$avec['Registration']['paid'] = null;

								if ($this->Registration->save($avec)) {
									$this->setMessage(0, 'Komento suoritettu');
								}
								else {
									$this->Registration->delete($id);
									$this->setMessage(6, 'Avecin tietoja ei voitu tallentaa');
								}
							}
							else {
								$this->setMessage(0, 'Komento suoritettu');
							}
						}
						else {
							$this->setMessage(5, 'Tietoja ei voitu tallentaa');
						}
					}
					else {
						$this->setMessage(4, 'Tapahtumaan ei voi ilmoittautua nyt');
					}
				}
				else {
					$this->setMessage(3, 'Olet jo ilmoittautunut tapahtumaan');
				}
			}
			else {
				$this->setMessage(2, 'Tapahtumaa ei löytynyt');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Ilmoittautuu tapahtumaan ulkopuolisena
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param int $event tapahtuman tunnistenumero
	 * @param string $name nimi
	 * @param string $email sähköpostiosoite
	 * @param string $phone puhelinnumero
	 * @param string $avecName avecin nimi
	 * @param string $avecEmail avecin sähköposti
 	 * @param string $avecPhone avecin puhelinnumero
	 */
	function participateToEventOutsider($event = null, $name = null,
		$email = null, $phone = null, $avecName = null,
		$avecEmail = null, $avecPhone = null) {

		//Tarkista löytyykö tapahtuma
		if (is_numeric($event) && $event = $this->CalendarEvent->findById($event)) {

			// Tarkista voiko tapahtumaan ilmoittautua nyt
			if ($this->CalendarEvent->isRegistrableNow($event)) {

				// Aseta tiedot tallentamista varten
				$data['Registration']['calendar_event_id'] = $event['CalendarEvent']['id'];
				$data['Registration']['user_id'] = null;
				$data['Registration']['name'] = $name;
				$data['Registration']['email'] = $email;
				$data['Registration']['phone'] = $phone;
				$data['Registration']['paid'] = null;

				// Tallenna normaali ilmodata
				if ($this->Registration->save($data)) {

					// Tarkista voiko tapahtumaan ilmoittaa avecin
					if ($event['CalendarEvent']['avec'] && $avecName != null) {
						$id = $this->Registration->id;

						// Aseta avecin tiedot tallentamista varten
						$avec['Registration']['id'] = null;
						$avec['Registration']['calendar_event_id'] = $event['CalendarEvent']['id'];
						$avec['Registration']['avec_id'] = $id;
						$avec['Registration']['name'] = $avecName;
						$avec['Registration']['email'] = $avecEmail;
						$avec['Registration']['phone'] = $avecPhone;
						$avec['Registration']['paid'] = null;

						if ($this->Registration->save($avec)) {
							$this->setMessage(0, 'Komento suoritettu');
						}
						else {
							$this->Registration->delete($id);
							$this->setMessage(4, 'Avecin tietoja ei voitu tallentaa');
						}
					}
					else {
						$this->setMessage(0, 'Komento suoritettu');
					}
				}
				else {
					$this->setMessage(3, 'Tietoja ei voitu tallentaa');
				}
			}
			else {
				$this->setMessage(2, 'Tapahtumaan ei voi ilmoittautua nyt');
			}
		}
		else {
			$this->setMessage(1, 'Tapahtumaa ei löytynyt');
		}
	}

	/**
	 * Muokkaa avecin ilmoittautumista tapahtumaan
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param string $username käyttäjätunnus
	 * @param string $password salasana
	 * @param int $event tapahtuman tunnistenumero
	 * @param int $field muokattavan kentän tunnistenumero
	 * @param string $value kentän uusi arvo
	 */
	function modifyAvecParticipation($username = null, $password = null,
		$event = null, $field = null, $value = null) {

		// Tarkista käyttäjän salasana
		if ($this->checkUser($username, $password)) {
			$user = $this->User->findByUsername($username);

			//Tarkista löytyykö tapahtuma
			if (is_numeric($event) && $event = $this->CalendarEvent->findById($event)) {

				// Tarkista liittyykö lisätietokenttä tapahtumaan
				if ($this->CustomField->findByIdAndCalendarEventId(
					$field, $event['CalendarEvent']['id'])) {
			
					// Tarkista onko käyttäjä ilmoittautunut tapahtumaan
					if ($reg = $this->Registration->findByUserIdAndCalendarEventId(
						$user['User']['id'], $event['CalendarEvent']['id'])) {
					
						// Tarkista onko käyttäjä ilmoittanut avecin
						if ($avec = $this->Registration->findByAvecId($reg['Registration']['id'])) {
						
							// Yritä tallentaa tiedot
							$result = $this->CustomFieldAnswer->saveAnswer(
								$avec['Registration']['id'], $field, $value);

							switch ($result) {
								case 0:
									$this->setMessage(0, 'Komento suoritettu');
									break;
								case 1:
									$this->setMessage(7, 'Virheellinen arvo');
									break;
								default:
									$this->setMessage(6, 'Lisätietokenttää ei löytynyt');
									break;
							}
						}
						else {
							$this->setMessage(5, 'Et ole ilmoittanut avecia');
						}
					}
					else {
						$this->setMessage(4, 'Et ole ilmoittautunut tapahtumaan');
					}
				}
				else {
					$this->setMessage(3, 'Lisätieto ei liity tähän tapahtumaan');
				}
			}
			else {
				$this->setMessage(2, 'Tapahtumaa ei löytynyt');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Muokkaa omaa ilmoittautumista tapahtumaan
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param string $username käyttäjätunnus
	 * @param string $password salasana
	 * @param int $event tapahtuman tunnistenumero
	 * @param int $field muokattavan kentän tunnistenumero
	 * @param string $value kentän uusi arvo
	 */
	function modifyEventParticipation($username = null, $password = null,
		$event = null, $field = null, $value = null) {

		// Tarkista käyttäjän salasana
		if ($this->checkUser($username, $password)) {
			$user = $this->User->findByUsername($username);

			//Tarkista löytyykö tapahtuma
			if (is_numeric($event) && $event = $this->CalendarEvent->findById($event)) {

				// Tarkista liittyykö lisätietokenttä tapahtumaan
				if ($this->CustomField->findByIdAndCalendarEventId(
					$field, $event['CalendarEvent']['id'])) {
			
					// Tarkista onko käyttäjä ilmoittautunut tapahtumaan
					if ($reg = $this->Registration->findByUserIdAndCalendarEventId(
						$user['User']['id'], $event['CalendarEvent']['id'])) {

						// Yritä tallentaa tiedot
						$result = $this->CustomFieldAnswer->saveAnswer(
							$reg['Registration']['id'], $field, $value);

						switch ($result) {
							case 0:
								$this->setMessage(0, 'Komento suoritettu');
								break;
							case 1:
								$this->setMessage(6, 'Virheellinen arvo');
								break;
							default:
								$this->setMessage(5, 'Lisätietokenttää ei löytynyt');
								break;
						}
					}
					else {
						$this->setMessage(4, 'Et ole ilmoittautunut tapahtumaan');
					}
				}
				else {
					$this->setMessage(3, 'Lisätieto ei liity tähän tapahtumaan');
				}
			}
			else {
				$this->setMessage(2, 'Tapahtumaa ei löytynyt');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}

	/**
	 * Peruu ilmoittautumisen tapahtumaan
	 *
	 * Palauttaa statuskoodin
	 *
	 * @param string $username käyttäjänimi
	 * @param string $password salasana
	 * @param int $event tapahtuman tunnistenumero
	 */
	function cancelEventParticipation($username = null, $password = null, $event = null) {

		// Tarkista käyttäjän salasana
		if ($user = $this->checkUser($username, $password)) {

			// Tarkista löytyyko tapahtuma
			if (is_numeric($event) && $event = $this->CalendarEvent->findById($event)) {

				// Tarkista onko käyttäjä ilmoittautunut tapahtumaan
				if ($data = $this->Registration->findByUserIdAndCalendarEventId(
					$user['id'], $event['CalendarEvent']['id'])) {

					// Tarkista voiko ilmoittautumisen perua nyt
					if ($this->CalendarEvent->isCancelableNow($event)) {

						// Poista myös avecin ilmoittautuminen
						if ($avec = $this->Registration->findByAvecId($data['Registration']['id'])) {
							$this->Registration->delete($avec['Registration']['id']);
						}
						$this->Registration->delete($data['Registration']['id']);
						$this->setMessage(0, 'Komento suoritettu');
					}
					else {
						$this->setMessage(4, 'Ilmoittautumista ei voi perua nyt');
					}
				}
				else {
					$this->setMessage(3, 'Et ole ilmoittautunut tapahtumaan');
				}
			}
			else {
				$this->setMessage(2, 'Tapahtumaa ei löytynyt');
			}
		}
		else {
			$this->setMessage(1, 'Virheellinen käyttäjätunnus tai salasana');
		}
	}
}
?>
