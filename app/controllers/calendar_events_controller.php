<?php
/**
* calendar_events.controller.php
*
* @author Lauri Auvinen, Juhani Markkula
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Ohjainluokka tapahtumakalenterin käsittelyyn
* @author Lauri Auvinen, Juhani Markkula
* @package Kurre
*/
class CalendarEventsController extends AppController {
	var $name = 'CalendarEvents';
	var $uses = array('CalendarEvent', 'User', 'Registration', 'Location', 'EventType');
	var $helpers = array('Form', 'Html', 'Formy', 'rss', 'Format', 'Javascript');
	var $components = array('DateFormatter');
	var $layout = 'calendar';
	var $pageTitle = 'Tapahtumakalenteri';

	/**
	* Callback-funktio, joka asettaa halutuille metodeille kirjautumispakon <br/>ja vaaditun käyttäjäroolin
	* @author Lauri Auvinen
	*/
	function beforeFilter() {

// $starttime = microtime();
// $startarray = explode(" ", $starttime);
// $starttime = $startarray[1] + $startarray[0];
// $dt_file = fopen('/tmp/app_controller::beforeFilter.txt','a');

		AppController::beforeFilter();

// $endtime = microtime();
// $endarray = explode(" ", $endtime);
// $endtime = $endarray[1] + $endarray[0];
// $totaltime = $endtime - $starttime;
// $totaltime = round($totaltime,5);
// fwrite($dt_file, "calendar_events_controller::beforeFilter, line AppController::beforeFilter(); completed at $totaltime seconds.\n");

		$this->requireLogin(a('delete', 'modify', 'create', 'manage'));

// $endtime = microtime();
// $endarray = explode(" ", $endtime);
// $endtime = $endarray[1] + $endarray[0];
// $totaltime = $endtime - $starttime;
// $totaltime = round($totaltime,5);
// fwrite($dt_file, "calendar_events_controller::beforeFilter, line \$this->requireLogin... completed at $totaltime seconds.\n");

		$this->requireRole('virkailija', a('create', 'modify', 'delete', 'manage'));

// $endtime = microtime();
// $endarray = explode(" ", $endtime);
// $endtime = $endarray[1] + $endarray[0];
// $totaltime = $endtime - $starttime; 
// $totaltime = round($totaltime,5);
// fwrite($dt_file, "calendar_events_controller::beforeFilter, line \$this->requireRole... completed at $totaltime seconds.\n");
// fclose($dt_file);

	}

	/**
	* Hakee kaikki tapahtumat ja välittää ne näkymälle, joka näyttää valinnan mukaan joko menneet tai tulevat
	* @author Lauri Auvinen
	*/
	function index() {
 		$type = $this->data;
		$eventStatus = $type['CalendarEvents']['type'];
		$this->set('status', $eventStatus);
		$user = $this->currentUser();
//		$conditions = array("calendar_events.deleted" => 0, "calendar_events.template" => 0);
		$conditions = null;
// No longer needed / MP 12.2.2010
//		$this->set('events', $this->CalendarEvent->findAll($conditions, null, 'starts'));
	}

	/**
	* Näyttää tapahtumat RSS-feedinä
	* @author Lauri Auvinen
	*/
	function rss() {
		$this->layout = 'rss';
		$this->set('url', Router::url('/view/'));
// No longer needed / MP 12.2.2010
//		$this->set('events', $this->CalendarEvent->findAll(null,null, 'starts'));
	}
 
	/**
	* Näyttää tapahtuman tiedot ja ilmoittautumistoiminnot.
	* @param int $id tapahtuman tunniste.
	* @author Juhani Markkula, Lauri Auvinen
	*/
	function view($id = null) {
		$current_user = $this->currentUser();
		$event = $this->CalendarEvent->findById($id);
		if(!$event) {
			$this->goToFrontpageWithMessage('Tapahtumaa ei löytynyt.');
		}
		if ($event['CalendarEvent']['deleted'] == 1) {
			$this->flashError('Tapahtuma on poistettu ja sitä ei voi katsella.');
			$this->redirect('/calendar_events');
		}
		$registration = $this->Registration->findByUserIdAndCalendarEventId($current_user['id'], $id);
		$registrableNow = $this->CalendarEvent->isRegistrableNow($event);
		$cancelable = $this->CalendarEvent->isCancelableNow($event);
		
		$showMemberReg = $registrableNow && $current_user;
		if($event['CalendarEvent']['membership_required']) {
//			$showMemberReg = $showMemberReg && $this->User->canRegister($current_user);
			$showMemberReg = $showMemberReg && $this->User->canParticipateEvent($current_user);
		}
		$showPublicReg = $registrableNow && !$current_user && $event['CalendarEvent']['outsiders_allowed'];
		$showCancellation = $cancelable && $current_user && !empty($registration);
		$registered = $current_user && !empty($registration);
		
		$this->set('event', $event);
		$this->set('selectedEventId', $id);
		$this->set('showMemberReg', $showMemberReg);
		$this->set('showPublicReg', $showPublicReg);
		$this->set('showCancel', $showCancellation);
		$this->set('registered', $registered);		

		$this->set('outsidersAllowed', $event['CalendarEvent']['outsiders_allowed']);
		$this->set('membersOnly', $event['CalendarEvent']['membership_required']);
		
		$this->set('isRegistrationOn', $registrableNow);
		$this->set('isRegistrable', $event['CalendarEvent']['registration_starts'] != NULL);
//		$this->set('isMember', $current_user && $this->User->canRegister($current_user));
		$this->set('isMember', $current_user && $this->User->canParticipateEvent($current_user));
		
		if ($this->User->compareUserRole($current_user['role'], 'virkailija') >= 0) {
			$this->set('adminMode','1');
		}
		else {
			$this->set('adminMode','0');
		}
		
		$action = 'participate/'.$event['CalendarEvent']['id'];
		
		// Ilmon muokkaus:
		if($current_user && !empty($registration)) {
			$action = 'edit/'.$registration['Registration']['id'];
			
			$this->data = $registration;
			$avec = $this->Registration->findByAvecId($registration['Registration']['id']);
			if($avec) {
				$this->data['Avec'] = $avec;
				$this->data['Avec']['CustomField'] = array();
				foreach($avec['CustomFieldAnswer'] as $answer) {
					$this->data['Avec']['CustomField']['field_'.$answer['custom_field_id']] = $answer['value'];
				}
			}
			
			//$this->data['Registration'] = $registration['Registration'];
			$this->data['CustomField'] = array();
			foreach($registration['CustomFieldAnswer'] as $answer) {
				$this->data['CustomField']['field_'.$answer['custom_field_id']] = $answer['value'];
			}
			
		}
		$this->set('action', $action);
	}

	/**
	* Luo uuden tapahtuman joko syötettynä tai tapahtumapohjasta
	* @author Lauri Auvinen
	*/
	function create() {
		$current_user = $this->currentUser();
		$this->set('userId', $current_user['id']);
		$this->set('userName', $current_user['username']);
		$this->set('givenDate', null);
		$this->set('givenTime', null);
		$this->set('locations', $this->Location->findAll(null,null, 'name'));
		$this->set('eventTypes', $this->EventType->findAll(null,null, 'name'));
		$this->set('templates', $this->CalendarEvent->findAllByTemplate('1'));

		// Haetaan tapahtumapohjan tiedot lomakkeelle
		if (isset($this->data['CalendarEvent']['templateId']) && $this->data['CalendarEvent']['templateId'] != NULL) {
			$templateId = $this->data['CalendarEvent']['templateId'];
			$event = $this->CalendarEvent->findById($templateId);
			$event['CalendarEvent']['template'] = 0;
			$this->data = $event;
			$this->set('event', $event);
			$this->data['CalendarEvent']['templateId'] = NULL;
		}
		else if (!empty($this->data)) {
			if ($this->CalendarEvent->save($this->data)) {
				// Lisätään vakiopaikka ja mahdollinen karttalinkki tietokantaan, jos sitä ei löydy ennestään sieltä
				if (!$this->Location->findByName($this->data['CalendarEvent']['location'])) {
					$this->Location->add($this->data['CalendarEvent']['location']);
					if ($this->data['CalendarEvent']['map']) {
						$this->Location->setMaplink($this->data['CalendarEvent']['map']);
					}
				}
				// Lisätään tapahtumatyyppi tietokantaan, jos sitä ei löydy ennestään sieltä
				if (!$this->EventType->findByName($this->data['CalendarEvent']['category'])) {
					$this->EventType->add($this->data['CalendarEvent']['category']);
				}
				// Talletetaan lisätietokentät
				foreach ($this->data['CustomField'] as $id => $data) {
					if (!empty($data['type'])) {
						$this->CalendarEvent->CustomField->create();
						$data['calendar_event_id'] = $this->CalendarEvent->id;
						$this->CalendarEvent->CustomField->save(array('CustomField' => $data));
					}
				}

				// Lisätään tapahtumapohjana
				if($this->data['CalendarEvent']['template'] == 1) {
					$this->flashSuccess('Tapahtumapohja lisätty tietokantaan.');
					$this->redirect('/calendar_events/manage');
					exit();
				}
				// Lisätään tapahtumana
				else {
					$str = "\"" . $this->data['CalendarEvent']['name'] . "\" lisätty tietokantaan.";
					$str = $str . " Tapahtuman lyhytosoite on ";
					$str = $str . "http://domain.local/event/" . $this->CalendarEvent->id . ".";
					$this->flashSuccess($str);
					$this->redirect('/calendar_events');
					exit();
				}
			}
		}
	}

	/**
	* Muokkaa haluttua tapahtumaa tai tapahtumapohjaa
	* @param int $id tapahtuman tunniste.
	* @author Lauri Auvinen
	*/
	function modify($id = null) {
			// Yritetään käsitellä ja tallentaa muokattu tapahtuma tai tapahtumapohjaa
			if (!empty($this->data)) {
				$event = $this->data;
				
				// Haetaan vanhat tiedot kannasta ja tarkastetaan muuttuiko osallistujamäärä / MP
				$oldevent = $this->CalendarEvent->findById($event['CalendarEvent']['id']);
				$oldcap = $oldevent['CalendarEvent']['max_participants'];
				$newcap = $event['CalendarEvent']['max_participants'];
				
				$this->log($oldcap);
				$this->log($newcap);
				
				$this->CalendarEvent->id = $event['CalendarEvent']['id'];
				$this->set('givenDate', $event['CalendarEvent']['date']);
				$this->set('givenTime', $event['CalendarEvent']['time']);
				$this->set('first', 0);
 				$this->set('event', $event);
				$this->set('locations', $this->Location->findAll());
				$this->set('eventTypes', $this->EventType->findAll());
								
				if ($this->CalendarEvent->save($this->data)) {
					// Talletetaan lisätietokentät
					foreach ($this->data['CustomField'] as $id => $data) {
						if (!empty($data['type'])) {
							$this->CalendarEvent->CustomField->create();
							$data['calendar_event_id'] = $this->CalendarEvent->id;
							$this->CalendarEvent->CustomField->save(array('CustomField' => $data));
						}
						else if (isset($data['id'])) {
							$this->CalendarEvent->CustomField->delete($data['id']);
						}
					}
					if ($event['CalendarEvent']['template'] == 1) {
						$this->flashSuccess('Tapahtumapohjaa muokattu.');
						$this->redirect('/calendar_events/manage');
						exit();
					}
					else {
						$this->flashSuccess('Tapahtumaa muokattu.');
						
						//TODO: Send emails if max_participants is now larger.
						if($newcap > $oldcap){
							$this->handleMaxParticipantsIncreaseQueueMovement($event['CalendarEvent']['id'], $oldcap);
						}
						
						$this->redirect('/calendar_events/view/' . $event['CalendarEvent']['id']);
						exit();
					}
				}
				else {
					$this->flashError('Muokkaus epäonnistui, tarkasta kentät.');
				}
			}
			// Haetaan tapahtuman tiedot
			else {
				$event = $this->CalendarEvent->findById($id);
		
				if ($event['CalendarEvent']['id'] == NULL) {
					$this->flashError('Ei muokattavaa tapahtumaa.');
					$this->redirect('/calendar_events');
					exit();
				}
				$this->data = $event;
				// Haetaan tapahtuman tiedot muokkauslomakkeelle ensimmäistä kertaa
				if ($this->data['CalendarEvent']['registration_starts'] != null) {
					$this->data['CalendarEvent']['can_participate'] = 1;
				}
				else {
					$this->data['CalendarEvent']['can_participate'] = 0;
				}

				// Muunnetaan päivämäärät näkymää varten 
				$this->data['CalendarEvent']['date'] = $this->DateFormatter->date($this->data['CalendarEvent']['starts']);
				$this->data['CalendarEvent']['time'] = $this->DateFormatter->time($this->data['CalendarEvent']['starts']);
				$this->data['CalendarEvent']['registration_starts'] = $this->DateFormatter->dateTime($this->data['CalendarEvent']['registration_starts']);
				$this->data['CalendarEvent']['registration_ends'] = $this->DateFormatter->dateTime($this->data['CalendarEvent']['registration_ends']);
				$this->data['CalendarEvent']['cancellation_starts'] = $this->DateFormatter->dateTime($this->data['CalendarEvent']['cancellation_starts']);
				$this->data['CalendarEvent']['cancellation_ends'] = $this->DateFormatter->dateTime($this->data['CalendarEvent']['cancellation_ends']);

 				$this->set('event', $event);
				$this->set('givenDate', $event['CalendarEvent']['starts']);
				$this->set('givenTime', $event['CalendarEvent']['starts']);
				

				// Ollaan vasta hakemassa tietoja lomakkeelle, ei tallentamassa niitä
				$this->set('first', 1);
				$this->set('locations', $this->Location->findAll());
				$this->set('eventTypes', $this->EventType->findAll());
			}
	}

	/**
	* Poistaa halutun tapahtuman tai tapahtumapohjan
	* @param int $id tapahtuman tunniste.
	* @author Lauri Auvinen
	*/
	function delete($id = null) {
		$event = $this->CalendarEvent->findById($id);
		if ($event['CalendarEvent']['id'] == NULL) {
				$this->flashError('Ei poistettavaa tapahtumaa.');
				$this->redirect('/calendar_events');
		}
		else if ($this->CalendarEvent->delete($id)) {
			if ($event['CalendarEvent']['template'] == 1) {
				$this->flashSuccess('Tapahtumapohja poistettu.');
				$this->redirect('/calendar_events/manage');
			}
			else {
				$this->flashSuccess('Tapahtuma poistettu.');
				$this->redirect('/calendar_events');
			}
		}
	}

	/**
	* Hallitsee tapahtumapohjia, vakiopaikkoja ja tapahtumatyyppejä
	* @author Lauri Auvinen
	*/
	function manage() {
		$this->set('templates', $this->CalendarEvent->findAllByTemplate('1'));
		$this->set('locations', $this->Location->findAll());
		$this->set('eventTypes', $this->EventType->findAll());
	}
}
?>
