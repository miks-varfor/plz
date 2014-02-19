<?php
/**
* calendar_event.php
*
* @author Lauri Auvinen, Juhani Markkula
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka tapahtumakalenterin tapahtumalle
*
* @author Lauri Auvinen, Juhani Markkula
* @package Kurre
*/
class CalendarEvent extends AppModel {
	var $name = 'CalendarEvent';

	var $hasMany = array(
		'CustomField' => array(
			'order' => 'CustomField.id ASC',
		),
		'Registration' => array(
			'order' => 'Registration.created ASC',
		)
	);
	var $belongsTo = 'User';

	var $validate = array(
		'registration_starts' =>
		array('custom' =>
			  array('rule' => 'registrationEnabled',
					'message' => 'Tapahtumaan ei ole ilmoittautumista'),
			  'muoto' =>
			  array('rule' => 'registrationStarts',
					'message' => 'Tarkista päivämäärän muoto',
					'required' => true,
					'allowEmpty' => true)),
		'registration_ends' => 
		array('custom' =>
			  array('rule' => 'registrationEnabled',
					'message' => 'Tapahtumaan ei ole ilmoittautumista'),
			  'muoto' =>
			  array('rule' => 'registrationEnds',
					'message' => 'Tarkista päivämäärän muoto',
					'required' => true,
					'allowEmpty' => true)),
		'cancellation_starts' =>
		array('custom' =>
			  array('rule' => 'registrationEnabled',
					'message' => 'Tapahtumaan ei ole ilmoittautumista'),
			  'muoto' =>
			  array('rule' => 'cancellationStarts',
					'message' => 'Tarkista päivämäärän muoto',
					'required' => true,
					'allowEmpty' => true)),
		'cancellation_ends' =>
		array('custom' =>
			  array('rule' => 'registrationEnabled',
					'message' => 'Tapahtumaan ei ole ilmoittautumista'),
			  'muoto' =>
			  array('rule' => 'cancellationEnds',
					'message' => 'Tarkista päivämäärän muoto',
					'required' => false,
					'allowEmpty' => true)),
		'name' => array(
				'rule' => array('maxLength', 255),
				'allowEmpty' => false,
				'message' => 'Anna tapahtuman nimi'),
		'date' => array(
				'rule' => 'date',
				'required' => true,
				'allowEmpty' => false,
				'message' => 'Tarkista, että päivämäärä ja aika ovat oikeassa muodossa ja tulevaisuudessa'),
		'time' => array(
				'rule' => 'time',
				'required' => true,
				'allowEmpty' => false,
				'message' => 'Tarkista, että päivämäärä ja aika ovat oikeassa muodossa ja tulevaisuudessa'),
		'location' => array(
				'rule' => 'location',
				'required' => true,
				'message' => 'Anna tapahtuman paikka'),
		'category' => array(
				'rule' => 'category',
				'required' => true,
				'message' => 'Anna tapahtuman tyyppi'),
		'description' => array(
				'rule' => array ('maxLength',100000),
				'required' => true,
				'allowEmpty' => false,
				'message' => 'Anna tapahtuman kuvaus'),
		'show_responsible' => array(
				'rule' => 'show_responsible',
				'required' => true,
				'message' => 'Vastuuhenkilön nimeä ei annettu'),
		'price' => array(
				'rule' => array('maxLength', 255),
				'allowEmpty' => true),
		'map' => array(
				'rule' => array('maxLength', 255),
				'allowEmpty' => true),
		'max_participants' => array(
				'rule' => 'maxParticipants',
				'required' => true,
				'message' => 'Anna suurin osallistujamäärä kokonaislukuna'),
		'can_participate' => array(
				'rule' => 'canParticipate',
				'required' => true,
				'message' => 'Tarkista päivämäärien oikeellisuus'),
		'membership_required' => array(
				'rule' => 'membershipRequired',
				'required' => true,
				'message' => 'Tapahtumaan ei voi ilmoittautua'),
		'avec' => array(
				'rule' => 'avec',
				'required' => true,
				'message' => 'Tapahtumaan ei voi ilmoittautua'));

	/**
	* Tarkistaa voiko tapahtumaan ilmoittautua ulkopuoliset
	* @return bool voivatko ulkopuoliset ilmoittautua tapahtumaan
	* @author Lauri Auvinen
	*/
	function membershipRequired() {
		if ($this->data['CalendarEvent']['can_participate'] == 0 && $this->data['CalendarEvent']['membership_required'] == 1) {
			return false;
		}
		else {
			return true;
		}
	}

	/**
	* Tarkistaa voiko tapahtumaan ilmoittaa avecin
	* @return bool voiko tapahtumaan ilmoittautua
	* @author Lauri Auvinen
	*/
	function avec() {
		if ($this->data['CalendarEvent']['can_participate'] == 0 && $this->data['CalendarEvent']['avec'] == 1) {
			return false;
		}
		else {
			return true;
		}
	}

	function registrationEnabled($data) {
		reset($data);
		$data = current($data);

		if (empty($data)
			&& $this->data['CalendarEvent']['can_participate'] == 0) {
			return true;
		}
		else if (!empty($data)
				 && $this->data['CalendarEvent']['can_participate'] != 0) {
			return true;
		}
		return false;
	}

	/**
	* Tarkistaa rekisteröitymisen aloituspäivämäärän oikeellisuuden
	* @return bool onko päivämäärä kunnossa
	* @author Lauri Auvinen
	*/
	function registrationStarts($data) {
		reset($data);
		$data = current($data);

		if (!$validDateTime = $this->convertDate($data)) {
			return false;
		}
		else if ($this->data['CalendarEvent']['registration_starts'] == " ") {
			return true;
		}
		else {
			$this->data['CalendarEvent']['registration_starts'] = $validDateTime;
			return true;
		}	
	}

	/**
	* Tarkistaa rekisteröitymisen lopetuspäivämäärän oikeellisuuden
	* @return bool onko päivämäärä kunnossa
	* @author Lauri Auvinen
	*/
	function registrationEnds($data) {
		reset($data);
		$data = current($data);

		if (!$validDateTime = $this->convertDate($data)) {
			return false;
		}
		else if ($this->data['CalendarEvent']['registration_ends'] == " ") {
			return true;
		}
		else {
			$this->data['CalendarEvent']['registration_ends'] = $validDateTime;
			return true;
		}	
	}

	/**
	* Tarkistaa rekisteröitymisen perumisen aloituspäivämäärän oikeellisuuden
	* @return bool onko päivämäärä kunnossa
	* @author Lauri Auvinen
	*/
	function cancellationStarts($data) {
		reset($data);
		$data = current($data);

		if(!isset($this->data['CalendarEvent']['cancellation_starts'])) {// Päivämäärää ei asetettu, kelpaa.
			return true;
		}
  
		if (!$validDateTime = $this->convertDate($data)) {
			return false;
		}
		else if ($this->data['CalendarEvent']['cancellation_starts'] == " ") {
			return true;
		}
		else {
			$this->data['CalendarEvent']['cancellation_starts'] = $validDateTime;
			return true;
		}	
	}

	/**
	* Tarkistaa rekisteröitymisen perumisen lopetuspäivämäärän oikeellisuuden
	* @return bool onko päivämäärä kunnossa
	* @author Lauri Auvinen
	*/
	function cancellationEnds($data) {
		reset($data);
		$data = current($data);
		
		if(!isset($this->data['CalendarEvent']['cancellation_ends'])) {// Päivämäärää ei asetettu, kelpaa.
			return true;
		}

		if (!$validDateTime = $this->convertDate($data)) {
			return false;
		}
		else if ($this->data['CalendarEvent']['cancellation_ends'] == " ") {
			return true;
		}
		else {
			$this->data['CalendarEvent']['cancellation_ends'] = $validDateTime;
			return true;
		}	
	}

	/**
	* Tarkistaa muodossa pp.kk.vvvv syötetyn päivämäärän oikeellisuuden
	* @return bool onko päivämäärä kunnossa
	* @author Lauri Auvinen
	*/
	function date($data) {
		reset($data);
		$data = current($data);

		if (strlen($data) > 10) {
			return false;
		}

		// Tarkistetaan syötetyn päivämäärän oikeellisuus
		if (ereg ("([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})", $data, $regs)) {
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
				$this->data['CalendarEvent']['date'] = $validDate;
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	* Tarkistaa muodossa tt:mm syötetyn ajan oikeellisuuden
	* @return bool onko aika kunnossa
	* @author Lauri Auvinen
	*/
	function time($data) {
		reset($data);
		$data = current($data);

		if (strlen($data) > 5) {
			return false;
		}

		// Tarkistetaan syötetyn ajan oikeellisuus
		if (ereg ("([0-9]{1,2}):([0-9]{2})", $data, $regs)) {
			$hour = $regs[1];
			$min = $regs[2];

			if ($hour > 23 || $hour < 0) {
				return false;
			}
	
			if ($min > 59 || $min < 0) {
				return false;
			}
	
			if (strlen($hour < 2)) {
				$validTime = "0$hour:";
			}
			else {
				$validTime = "$hour:";
			}

			if (strlen($min < 2)) {
				$validTime = $validTime . "$min:00";
			}
			else {
				$validTime = $validTime . "$min:00";
			}
			$this->data['CalendarEvent']['time'] = $validTime;
			$this->data['CalendarEvent']['starts'] = $this->_makeValidDatetime($this->data['CalendarEvent']['date'],$this->data['CalendarEvent']['time']);
			if ($this->data['CalendarEvent']['starts'] == 0) {
				return false;
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	}

	/**
	* Merkkaa tapahtumalle oikean paikan ja tarkastaa että paikka on valittu listalta TAI kirjoitettu
	* Sampsa Lappalainen muokannut 31.7.2008: Mikäli paikka on kirjoitettu JA valittu listalta, valitaan kirjoitettu
	* @return bool onnistuiko operaatio
	* @author Lauri Auvinen
	*/
	function location() {
		if (!$this->data['CalendarEvent']['fixedLocation'] && $this->data['CalendarEvent']['location']) {
			return true;
		}
		else if ($this->data['CalendarEvent']['fixedLocation'] && !$this->data['CalendarEvent']['location']) {
			$this->data['CalendarEvent']['location'] = $this->data['CalendarEvent']['fixedLocation'];
			return true;
		}
		else if ($this->data['CalendarEvent']['fixedLocation'] && $this->data['CalendarEvent']['location']) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Merkkaa tapahtumalle tyypin ja tarkastaa että tyyppi on valittu listalta TAI kirjoitettu
	* Sampsa Lappalainen muokannut 31.7.2008: Mikäli tyyppi on kirjoitettu JA valittu listalta, valitaan kirjoitettu
	* @return bool onnistuiko operaatio
	* @author Lauri Auvinen
	*/
	function category() {
		if (!$this->data['CalendarEvent']['fixedEventType'] && $this->data['CalendarEvent']['category']) {
			//$this->data['CalendarEvent']['category'] = $this->data['CalendarEvent']['eventType'];
			return true;
		}
		else if ($this->data['CalendarEvent']['fixedEventType'] && !$this->data['CalendarEvent']['category']) {
			$this->data['CalendarEvent']['category'] = $this->data['CalendarEvent']['fixedEventType'];
			return true;
		}
		else if ($this->data['CalendarEvent']['fixedEventType'] && $this->data['CalendarEvent']['category']) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Tarkistaa onko vastuuhenkilön nimi annettu, jos "Näytä vastuuhenkilö"-checkbox merkattu
	* @return bool onko vastuuhenkilön nimi annettu
	* @author Lauri Auvinen
	*/
	function show_responsible() {
		if ($this->data['CalendarEvent']['show_responsible'] == 1 && $this->data['CalendarEvent']['responsible']) {
			return true;
		}
		else if ($this->data['CalendarEvent']['show_responsible'] == 1 && !$this->data['CalendarEvent']['responsible']) {
			return false;
		}
		else {
			return true;
		}
	}

	/**
	* Tarkistaa onko annettu osallistujamäärä kokonaisluku
	* @return bool onko osallistujamäärä kokonaisluku
	* @author Lauri Auvinen
	*/
	function maxParticipants() {
		if ($this->data['CalendarEvent']['max_participants']) {
			if (ereg("^[0-9]+$", $this->data['CalendarEvent']['max_participants'])) {
				if ($this->data['CalendarEvent']['max_participants'] == 0) {
					$this->data['CalendarEvent']['max_participants'] = NULL;
				}
				return true;
			}
			else {
				return false;
			}
		}
		else {
			$this->data['CalendarEvent']['max_participants'] = NULL;
			return true;
		}
	}

	/**
	* Tarkistaa voiko tapahtumaan ilmoittautua ja voiko myös ulkopuoliset ilmoittautua tapahtumaan
	* Tarkistaa myös kaikki ilmoittautumiseen ja sen perumiseen liittyvät päivämäärät, että ne ovat kunnossa
	* @return bool onko kaikki kunnossa
	* @author Lauri Auvinen
	*/
	function canParticipate() {
		$regStartTime = strtotime($this->data['CalendarEvent']['registration_starts']);
		$regEndTime = strtotime($this->data['CalendarEvent']['registration_ends']);
		$cancelStartTime = strtotime($this->data['CalendarEvent']['cancellation_starts']);
		$cancelEndTime = strtotime($this->data['CalendarEvent']['cancellation_ends']);
		$eventStartTime = strtotime($this->data['CalendarEvent']['starts']);

		if ($this->data['CalendarEvent']['can_participate'] == 1) {
			if ($this->data['CalendarEvent']['membership_required'] == 1) {
				$this->data['CalendarEvent']['outsiders_allowed'] = 0;
			}
			else {
				$this->data['CalendarEvent']['outsiders_allowed'] = 1;
			}

			if (isset($regStartTime) && isset($regEndTime)) {
				if ($regStartTime < $regEndTime) { 
					if (isset($cancelStartTime) && !isset($cancelEndTime)) {
						return false;
					}
					else if (!isset($cancelStartTime) && isset($cancelEndTime)) {
						return false;
					}
					else if (isset($cancelStartTime) && isset($cancelEndTime)) {
						if ($cancelStartTime >= $regStartTime && $cancelEndTime <= $eventStartTime) {
							return true;
						}
						else { 
							return false;
						}
					}
					else { // ei ole asetettu Cancel-aikoja
						return true;
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		else {
			$this->data['CalendarEvent']['registration_starts'] = NULL;
			$this->data['CalendarEvent']['registration_ends'] = NULL;
			$this->data['CalendarEvent']['cancellation_starts'] = NULL;
			$this->data['CalendarEvent']['cancellation_ends'] = NULL;
			return true;
		}
	}

	/**
	* Muuntaa muodossa pp.kk.vvvv tt:mm annetun päivämäärän ja ajan MySQL:n Datetime-muotoon
	* @param string $datetime aika ja päivämäärä muodossa pp.kk.vvvv tt:mm
	* @return boolean|string palauttaa Datetimen tai totuusarvon false jos muuntaminen ei onnistu
	*/
	function convertDate ($datetime) {

		if (ereg ("([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4}) ([0-9]{1,2}):([0-9]{2})", $datetime, $regs)) {
			$day = $regs[1];
			$month = $regs[2];
			$year = $regs[3];
			$hour = $regs[4];
			$min = $regs[5];

			if (!checkDate($month, $day, $year)) {
				return false;
			}

			if ($hour > 23 || $min > 59) {	
				return false;
			}

			$returnDateTime = "$year-";

			if (strlen($month) == 1) {
				$returnDateTime = $returnDateTime . "0$month-";
			}
			else {
				$returnDateTime = $returnDateTime . "$month-";
			}

			if (strlen($day) == 1) {
				$returnDateTime = $returnDateTime . "0$day ";
			}
			else {
				$returnDateTime = $returnDateTime . "$day ";
			}

			if (strlen($hour) == 1) {
				$returnDateTime = $returnDateTime . "0$hour:";
			}
			else {
				$returnDateTime = $returnDateTime . "$hour:";
			}

			$returnDateTime = $returnDateTime . "$min:00";

			return $returnDateTime;
		}
		else {
			return false;
		}
	}

	/**
	* Yhdistää päivämäärän ja kellonajan datetimeksi
	* @param string $date päivämäärä muodossa PP.KK.VVVV
	* @param string $time aika muodossa TT:MM
	* @return string päivämäärän ja ajan datetime-muodossa VVVV-KK-PP TT:MM:SS
	* @author Lauri Auvinen
	*/
	function _makeValidDatetime ($date, $time) {
		$validDatetime = $date . " " . $time;
		if (strtotime($validDatetime) < strtotime('now')) {
			return 0;
		}
		else {
			return $validDatetime;
		}
	}

	/**
	* Tarkistaa voiko tapahtumaan ilmottautua tällä hetkellä.
	* Otaa myös huomioon onko tapahtuma täynnä.
	* @param mixed $event tapahtuma...
	* @return bool voiko tapahtumaan ilmottautua nyt
	* @author Juhani Markkula
	*/
	function isRegistrableNow($event) {
		$regStarts = strtotime($event['CalendarEvent']['registration_starts']);
		$regEnds = strtotime($event['CalendarEvent']['registration_ends']);
		$eventStarts = strtotime($event['CalendarEvent']['starts']);
		$max = $event['CalendarEvent']['max_participants'];
		$registrants = count($event['Registration']);
		$registrable = false;
		
		if($regStarts)
			$registrable = $regStarts < time() && $eventStarts > time();
		if($regEnds)
			$registrable = $registrable && $regEnds > time();
//		if($registrable && $max > 0)
//			$registrable = $registrants < $max;
			
		return $registrable;
	}
	
	/**
	* Tarkistaa voiko tapahtumaan ilmottautumisen perua tällä hetkellä
	* @param mixed $event tapahtuma...
	* @return bool voiko tapahtumaan ilmottautumisen perua nyt
	* @author Juhani Markkula
	*/
	function isCancelableNow($event) {
		$cancelStarts = strtotime($event['CalendarEvent']['cancellation_starts']);
		$cancelEnds = strtotime($event['CalendarEvent']['cancellation_ends']);
		$eventStarts = strtotime($event['CalendarEvent']['starts']);
		$cancelable = false;
		
		if($cancelStarts)
			$cancelable = $cancelStarts < time() && $eventStarts > time();
		if($cancelEnds)
			$cancelable = $cancelable && $cancelEnds > time();
		
		return $cancelable;
	}

	/**
	* Merkkaa tapahtuman poistetuksi, jolloin sitä ei näytetä listassa
	* @param int $id tapahtuman id
	* @return bool onnistuko tapahtuman poistaminen
	* @author Lauri Auvinen
	*/
	function delete($id) { 
		$this->query("UPDATE calendar_events SET deleted=1 WHERE id=".$id);
		return true;
	}

	/**
	* Ilmoittaa tapahtuman lisäyksestä onnistumisen jälkeen
	* @author Lauri Auvinen
	*/
	function afterSave() {
		// Flashia pitää käyttää controllerissa, ei modelissa. 
		// Kommentoitu pois (aiheutti sql errorin) / wox / 10/2012
		//$this->flash('Tapahtuma lisätty.', '/calendar_events');
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
