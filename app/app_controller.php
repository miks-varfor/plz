<?php

App::Import('Model', 'CalendarEvent');

/**
* app_controller.php
*
* @author Projektiryhmä
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Ohjainluokka, jonka muut ohjaimet perii. Sisältää niille yhteiset palvelut.
* 
* @author Projektiryhmä
* @package Kurre
*/
class AppController extends Controller {
	var $uses = array('User', 'CalendarEvent');
	var $helpers = array('Html', 'Form', 'Format');
	var $layout = 'membership';
	var $pageTitle = 'MIKS';
	var $components = array ('Email');
	var $loggedUser = null; // cachetus yhden kutsun ajaksi
	
	/**
	 * Ajetaan ennen jokaista actionia. Asettaa layoutin vaatimat tiedot käyttöön.
	 */
	function beforeFilter($fetchEvents = true) {

		$this->set('currentUser', $this->currentUser());

		if($fetchEvents){

			$calendarEvent = new CalendarEvent();
			$eventStatus = 'new';

			if (isset($this->data['CalendarEvents']['type'])) {
				$eventStatus = $this->data['CalendarEvents']['type'];
				$this->set('eventStatus', $eventStatus);
			}

			$this->set('currentUser', $this->currentUser());

			// Jos eventStatus == old, haetaan vain vanhat, muuten vain tuoreet
			if($eventStatus == 'old'){
				$conditions = 'DATE(starts) <= DATE(NOW())';
			}
			else{
				$conditions = 'starts >= DATE_ADD(NOW(), INTERVAL -12 HOUR)';
			}
			$this->set('Events', $calendarEvent->findAll($conditions, null, 'starts', null, null, 0));

		}

		// Login-formia varteen redirect takaisin samaan sivuun
		if(!$this->isLoggedIn() && !empty($this->params['url'])) {
			$url = $this->params['url']['url'];

			if(strcmp(substr($url, 0, 5), '/plz/') == 0) {
				$url = substr($url, 4);
			}
			else if(strcmp(substr($url, 0, 1), '/') != 0) {
				$url = '/'.$url;
			}
			
			$this->set('urli', $url);
		}
	}

	/**
	 * Hakee kirjautuneen käyttäjän tiedot.
	 * 
	 * @returns array|false käyttäjän tiedot tai false, jos käyttäjä 
	 * ei ole kirjautunut.
	 * @author Juhani Markkula
	 */
	function currentUser() {
		if($this->isLoggedIn()) {
			$id = $this->Session->read('user_id');
			
			if(!$this->loggedUser) {
				$user = $this->User->findById($id);
				$this->loggedUser = $user['User'];
			}
			return $this->loggedUser;
		}
		return false;
	}
	
	/**
	 * Tarkistaa onko käyttäjä kirjautunut sisään.
	 * 
	 * @return boolean true, jos käyttäjä on kirjautunut, false, jos ei.
	 * @author Juhani Markkula
	 */
	function isLoggedIn() {
		return $this->Session->check('user_id');
	}
	
	/**
	 * Kirjaa käyttäjän sisään sessioon.
	 * 
	 * @param mixed $user käyttäjän tiedot yksinkertaisena assosiatiivisena taulukkona tai käyttäjän id.
	 * @author Juhani Markkula, Niko Kiirala
	 */
	function loginUser($user) {
		if (is_array($user)) {
			$this->Session->write('user_id', $user['id']);
		}
		else if (is_numeric($user)) {
			$this->Session->write('user_id', $user);
		}
	}
	
	/**
	 * Vaatii annetuilta actioneilta kirjautuneen käyttäjän. Jos ei ole kirjautunut,
	 * ohjataan etusivulle.
	 * 
	 * @param array $actions actionit, jotka vaatii kirjautumista.
	 * @author Juhani Markkula
	 */
	function requireLogin($actions) {
		if(in_array($this->action, $actions) && !$this->isLoggedIn()) {
			$this->goToFrontpageWithMessage('Sinun täytyy olla kirjautunut');
		}
	}

	/**
	 * Vaatii annetuilta actioneilta vähintään annetun käyttäjäroolin. 
	 * Jos käyttäjän rooli on liian matala, ohjataan etusivulle.
	 *
	 * @param array $actions actionit, jotka vaatii roolin.
	 * @param string $role vaadittu vähimmäisrooli.
	 * @author Juhani Markkula
	 */
	function requireRole($role, $actions) {
		$user = $this->currentUser();
		
		if(in_array($this->action, $actions) &&
			$this->User->compareUserRole($user['role'], $role) < 0) {
			$this->goToFrontpageWithMessage('Käyttäjätasosi ei riitä.');
		}
	}
	
	/**
	 * Asettaa annetun flash-viestin, ohjaa selaimen etusivulle ja keskeyttää
	 * actionin suorituksen.
	 *
	 * @param string $msg flash-viesti
	 * @author Juhani Markkula
	 */
	function goToFrontpageWithMessage($msg = '') {
		$this->Session->setFlash($msg);
		$this->redirect('/');
		exit(0);
	}
	
	function makeValidDate ($date) {
		if (strlen($date) > 10) {
			return 0;
		}

		if (ereg ("([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})", $date, $regs)) {
			$day = $regs[1];
			$month = $regs[2];
			$year = $regs[3];

			$validDate = "$year-";

			if (strlen($regs[2]) == 1) {
				$validDate = $validDate . "0$month-";
			}
			else {
				$validDate = $validDate . "$month-";
			}

			if (strlen($regs[1]) == 1) {
				$validDate = $validDate . "0$day";
			}
			else {
				$validDate = $validDate . "$day";
			}

			if ($isValidDate = checkDate($month, $day, $year)) {
				return $validDate;
			}
			else {
				return 0;
			}

		}
		else {
			return 0;
		}

	}

	/**
	 * Muodostaa annetusta suomalaisessa muodossa olevasta päivämäärästä
	 * tietokannan haluamassa muodossa olevan aikaleiman.
	 *
	 * Hyväksyy sekä pelkän päivämäärän että päivämäärän ja 
	 * kellonajan.
	 *
	 * Päivämäärän voi antaa joko muodossa 'päivä.kuukausi.' tai
	 * muodossa 'päivä.kuukausi.vuosi'. Jos vuotta ei ole annettu,
	 * käytetään kuluvaa vuotta.
	 *
	 * Kellonajan voi antaa joko pisteillä tai kaksoispisteillä
	 * erotettuna. Kaksi osaa tulkitaan tunneiksi ja minuuteiksi,
	 * kolme osaa tunneiksi, minuuteiksi ja sekunneiksi.
	 *
	 * Päivämäärän ja kellonajan erottimena toimii välilyönti, ja
	 * ne annetaan järjestyksessä 'päivämäärä kellonaika'.
	 *
	 * Valinnaisella parametrilla voi määritellä, mitä kellonaikaa
	 * käytetään, jos sitä ei ole annettu. Sallittuja parametreja ovat
	 * start - päivän alku (0:00:00)
	 * end - päivän loppu (23:59:59)
	 * mid - keskipäivä (12:00:00)
	 *
	 * Jos annetusta päivämäärästä ei voitu muodostaa aikaleimaa,
	 * palauttaa tyhjän merkkijonon.
	 *
	 * @param string $date_str aika suomalaisessa muodossa
	 * @param string $default_time oletuskellonajan määrittely
	 * @return string aika tietokannan käyttämässä muodossa
	 * @author Niko Kiirala
	 */
	function parseDateTime($date_str, $default_time = 'start') {
		/* Jaa aika päiväksi ja kellonajaksi */
		$date_array = explode(' ', $date_str);
		if (count($date_array) >= 2) {
			list($date_part, $time_part) = $date_array;
		}
		else if (count($date_array == 1)) {
			$date_part = $date_array[0];
			$time_part = '';
		}
		else {
			return '';
		}

		/* Jaa päivämäärä päiväksi, kuukaudeksi ja vuodeksi */
		$date_bits = explode('.', $date_part);
		if (count($date_bits) == 3) {
			$day = $date_bits[0];
			$month = $date_bits[1];
			if (strlen($date_bits[2]) > 0) {
				$year = $date_bits[2];
			}
			else {
				$year = date('Y');
			}
		}
		else if (count($date_bits) == 2) {
			$day = $date_bits[0];
			$month = $date_bits[1];
			$year = date('Y');
		}
		else {
			return '';
		}

		/* Tarkista, onko annettu päivä olemassa (esim. 32.3. ei ole) */
		if (!checkDate($month, $day, $year)) {
			return '';
		}

		/* Jaa aika tunneiksi, minuuteiksi ja sekunneiksi */
		if (isset($time_part) && strlen($time_part) > 0) {
			preg_match('/^(\d+)[:.](\d+)[:.]?(\d+)?$/',
					   $time_part, $matches);
			if (count($matches) < 3) {
				return '';
			}
			$hour = $matches[1];
			$minute = $matches[2];
			if (isset($matches[3]) && strlen($matches[3]) > 0) {
				$second = $matches[3];
			}
			else {
				$second = 0;
			}
		}
		else {
			if ($default_time == 'start') {
				$hour = 0;
				$minute = 0;
				$second = 0;
			}
			else if ($default_time == 'end') {
				$hour = 23;
				$minute = 59;
				$second = 59;
			}
			else if ($default_time == 'mid') {
				$hour = 12;
				$minute = 0;
				$second = 0;
			}
		}

		/* Kasaa osista tietokannalle sopiva aikaleima */
		$timestamp = mktime($hour, $minute, $second,
							$month, $day, $year);
		return date('Y-m-d H:i:s', $timestamp);
	}

	function flashError($message) {
		$this->Session->setFlash('<div id="flashMessage" class="badMessage">' . htmlspecialchars($message) . '</div>', '');
	}

	function flashSuccess($message) {
		$this->Session->setFlash('<div id="flashMessage" class="goodMessage">' . htmlspecialchars($message) . '</div>', '');
	}
	
	/* Funktiot jonosta pääseville mailaamiseen / wox / 10/2012 */
	
	function handleCancellationQueueMovement($id, $oldParticipantCount, $cancelIndex) {
		$event = $this->CalendarEvent->findById($id);
		$maxparticipants = $event['CalendarEvent']['max_participants'];
		$registrations = $this->Registration->find('all',array('conditions' => array('Registration.calendar_event_id' => $id), 'order' => array('Registration.id')));
		if($oldParticipantCount > $maxparticipants && count($registrations) < $oldParticipantCount 
			&& $cancelIndex < $maxparticipants){
			// Count has actually changed, so someone has moved out of queue
			// Note that cancelling a reg with an avec creates 2 free places
			$diff = $oldParticipantCount - count($registrations);
			for($i = 1; $i <= $diff; $i++){
				$reg = $registrations[$maxparticipants-$i];
				$this->sendQueueMovementNotification($event['CalendarEvent'], $reg['Registration']['email']);
			}
		}
	}
	
	function handleMaxParticipantsIncreaseQueueMovement($id, $oldMaxParticipants){
		$event = $this->CalendarEvent->findById($id);
		$maxParticipants = $event['CalendarEvent']['max_participants'];
		$registrations = $this->Registration->find('all',array('conditions' => array('Registration.calendar_event_id' => $id), 'order' => array('Registration.id')));
		if($oldMaxParticipants < $maxParticipants && count($registrations) > $oldMaxParticipants){
			$diff = $maxParticipants - $oldMaxParticipants;
			for($i = 1; $i <= $diff; $i++){
				$reg = $registrations[$maxParticipants-$i];
				$this->sendQueueMovementNotification($event['CalendarEvent'], $reg['Registration']['email']);
			}
		}
	}
	
	function findRegistrationIndex($array, $rid){
		for($i = 0; $i < count($array); $i++){
			if($array[$i]['id'] == $rid){
				return $i;
			}
		}
		return -1;
	}
	
	function sendQueueMovementNotification($event, $email) {
		if (strtotime($event['starts']) < time()) {
			// Event in the past, do not send emails
			return;
		}
		
		$subject = "Olet noussut jonosta mukaan tapahtumaan";
		$body = "Ilmoittautumisesi MIKSin tapahtumaan \"" . $event['name']  . "\" on päässyt jonosta mukaan mahtuvien joukkoon. " .
			"Mikäli et jostain syystä enää haluakaan tapahtumaan, käythän heti peruuttamassa ilmoittautumisesi MIKSin tapahtumakalenterissa.\n\n" .
			"Your registration to MIKS event \"" . $event['name']  . "\" has moved out of queue and is now within participant limit. " .
			"Should you not wish to participate to the event any longer, please cancel your registration as soon as possible.\n\n" .
			"Event URL: http://domain.local/event/".$event['id'];
		
		$smtpOptions = array(
			'port' => 25,
			'host' => 'localhost',
			//'host' => 'smtp.welho.com',
			'timeout' => '15'
		);
		
		$this->Email->from = 'MIKS <admin@domain.local>';  
		$this->Email->to = '<'.$email.'>';
		$this->Email->subject = $subject;	
		$this->Email->delivery = 'smtp';
		$this->Email->sendAs = 'text';
		$this->Email->replyTo =  'noreply@domain.local';
		$this->Email->smtpOptions = $smtpOptions;
		$message = $body;
		$this->Email->send($message);
		
		$this->log('Email sent');
	}
	
}

?>
