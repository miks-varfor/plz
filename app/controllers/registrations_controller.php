<?php
/**
* registrations_controller.php
*
* @author Juhani Markkula
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Ohjainluokka kalenteritapahtumien ilmoittautumisten käsittelyyn.
* 
* @author Juhani Markkula
* @package Kurre
*/

class RegistrationsController extends AppController {
	var $name = 'Registrations';
	var $uses = array(
		'Registration', 'User', 'CalendarEvent', 
		'CustomField', 'CustomFieldAnswer'
	);
	var $layout = 'calendar';
	var $helpers = array('Formy', 'Format');
	
	/**
	 * Ajetaan ennen jokaista tämän luokan actionia.
	 */
	function beforeFilter() {
		AppController::beforeFilter();
		$this->requireLogin(a('cancel', 'cancelAny', 'payEvent', 'edit'));
		$this->requireRole('virkailija', a('cancelAny', 'payEvent'));
	}
	
	/**
	 * Kirjaa kirjautuneen käyttäjän tapahtumaan osallistujaksi.
	 * 
	 * @param int $id kalenteritapahtuman tunniste
	 * @author Juhani Markkula
	 */
	function participate($id = null) {
		$event = $this->CalendarEvent->findById($id);
		if(!$event) {
			$this->goToFrontpageWithMessage('Tapahtumaa ei löytynyt.');
		}
		$user = $this->currentUser();
		
		// Tapahtumaan ei voi ilmottautua
		if(!$this->CalendarEvent->isRegistrableNow($event)) {
			$this->goToEventWithMessage($id, 'Tapahtumaan ei voi ilmottautua.');
		}
		
		$customFields = $event['CustomField'];
		$event = $event['CalendarEvent'];
		
		if($this->isLoggedIn()) {
			// Tapahtuma on vain jäsenille.
//			if($event['membership_required'] && !$this->User->canRegister($user)) {
			if($event['membership_required'] && !$this->User->canParticipateEvent($user)) {
				$this->goToEventWithMessage($id, 'Tähän tapahtumaan ilmoittautuminen vaatii jäsenyyden.');
			}
		} 
		else {
			// Tapahtuma on vain kirjautuneille käyttäjille
			if(!$event['outsiders_allowed']) { 
				$this->goToEventWithMessage($id, 'Tähän tapahtumaan ilmoittautuminen vaatii kirjautumisen.');
			}
		}
		
		// Varmistetaan, että ilmodatalla on taulukko, vaikkei lisätietokenttiä olisi
		if(empty($this->data['Registration'])) {
			$this->data['Registration'] = array();
		}

		if($this->isLoggedIn()) {
			$this->data['Registration']['user_id'] = $user['id'];
		}
		
		$this->data['Registration']['calendar_event_id'] = $event['id'];
		
		// Tallenna normaali ilmodata
		if($this->Registration->save($this->data)) {
			$this->flashSuccess('Ilmoittauduttu!');
			
			// Tallenna lisätietokentät
			$this->CustomFieldAnswer->saveAllAnswers($this->data, $customFields, $this->Registration->id);
			
			// Avec
			if($event['avec'] && strlen($this->data['Avec']['Registration']['name']) > 0) {
				$originalRegistrationId = $this->Registration->id;
				$this->data['Avec']['Registration']['calendar_event_id'] = $event['id'];
				$this->data['Avec']['Registration']['avec_id'] = $originalRegistrationId;
				if(empty($this->data['Avec']['Registration']['email'])) // Jos avecin osoitetta ei ole annettu
                                         $this->data['Avec']['Registration']['email'] = $this->data['Registration']['email']; // Avecin email sama kuin pääimoittautujan
		

		
				// Tallenna normaali ilmodata
				$this->Registration->id = null;
				if($this->Registration->save($this->data['Avec'])) {
					$this->CustomFieldAnswer->saveAllAnswers($this->data['Avec'], $customFields, $this->Registration->id);
				} 
				else {
					// Molemmat tai ei kumpikaan...
					$this->Registration->del($originalRegistrationId, true);
					$this->Session->write('flashData', $this->data);
					$this->flashError('Ilmoittautuminen keskeytyi. Täytäthän lomakkeeseen nimen ja sähköpostiosoitteen.');
				}
				// Tallenna lisätietokentät
			}
		} 
		else {
			// Datat sessioon CalendarEventsControlleria varten
			$this->Session->write('flashData', $this->data);
			$this->flashError('Ilmoittautuminen keskeytyi. Täytäthän lomakkeeseen nimen ja sähköpostiosoitteen.');
		}
		
		$this->redirect('/calendar_events/view/' . $id);
	}
	
	/**
	 * Peruu kirjautuneen käyttäjän ilmoittautumisen tapahtumaan.
	 * Tämä peruu myös avecin ilmoittautumisen.
	 * 
	 * @param int $id kalenteritapahtuman tunniste
	 * @author Juhani Markkula
	 */
	function cancel($id = null) {
		$current_user = $this->currentUser();
		$event = $this->CalendarEvent->findById($id);
		$registration = $this->Registration->FindByCalendarEventIdAndUserId($id, $current_user['id']);
		if(!$registration) {
			$this->goToFrontpageWithMessage('Ilmottautumista ei löytynyt.');
		}
		
		if($this->CalendarEvent->isCancelableNow($event)) {
			$oldRegCount = count($event['Registration']);
			$idx = $this->findRegistrationIndex($event['Registration'], $registration['Registration']['id']);
			if($idx == -1){
				$this->flashError('Ilmoittautumista ei löytynyt tapahtuman ilmoittautumisista.');
				$this->redirect('/calendar_events/view/' . $id);
				return;
			}
			$this->Registration->del($registration['Registration']['id'], true);
			$avec = $this->Registration->findByAvecId($registration['Registration']['id']);
			if($avec) {
				$this->Registration->del($avec['Registration']['id'], true);
			}
			$this->flashSuccess('Ilmoittautuminen peruttu!');
			$this->handleCancellationQueueMovement($id, $oldRegCount, $idx);
		}	
		else {
			$this->flashError('Ilmoittautumista ei voi perua.');
		}
		
		$this->redirect('/calendar_events/view/' . $id);
	}
	
	/**
	 * Peruu minkä tahansa ilmoittautumisen tapahtumaan. 
	 * Vaatii vähintään virkailijan oikeustason.
	 * 
	 * @param int $id kalenteritapahtuman tunniste
	 * @author Juhani Markkula
	 */
	function cancelAny($id = null) {
		$registration = $this->Registration->FindById($id);
		if(!$registration) {
			$this->goToFrontpageWithMessage('Ilmottautumista ei löytynyt.');
		}
		$user = $this->currentUser();
		$event = $this->CalendarEvent->findById($registration['Registration']['calendar_event_id']);
		
		$oldRegCount = count($event['Registration']);
		$idx = $this->findRegistrationIndex($event['Registration'], $registration['Registration']['id']);
		
		$this->Registration->del($registration['Registration']['id'], true);
		$this->handleCancellationQueueMovement($event['CalendarEvent']['id'], $oldRegCount, $idx);
		$this->flashSuccess('Ilmoittautuminen on peruttu.');
		
		$this->redirect('/registrations/listParticipantsAdmin/'.$registration['Registration']['calendar_event_id']);
	}	
	
	/**
	 * Asettaa ilmoittautumisen maksetuksi.
	 * Vaatii vähintään virkailijan oikeustason.
	 * 
	 * @param int $id ilmoittautumisen tunniste
	 * @author Juhani Markkula
	 */
	function payEvent($id = null) {
		$registration = $this->Registration->FindById($id);
		if(!$registration) {
			$this->goToFrontpageWithMessage('Ilmottautumista ei löytynyt.');
		}
		$user = $this->currentUser();
		
		$this->Registration->setPaid($registration['Registration']['id']);
		$this->redirect('/registrations/listParticipantsAdmin/' . $registration['Registration']['calendar_event_id']);
	}
	
	/**
	 * Lorem ipsum
	 * 
	 * @param int $id kalenteritapahtuman tunniste
	 * @author Juhani Markkula
	 */
	function edit($id = null) {
		$user = $this->currentUser();
		$registration = $this->Registration->FindByIdAndUserId($id, $user['id']);
		if(!$registration) {
			$this->goToFrontpageWithMessage('Ilmottautumista ei löytynyt.');
		}
		$event = $this->CalendarEvent->findById($registration['Registration']['calendar_event_id']);
		$customFields = $event['CustomField'];
		$event = $event['CalendarEvent'];
		
		$this->Registration->id = $registration['Registration']['id'];
		
		if($this->Registration->save($this->data)) {
			$this->flashSuccess('Ilmoittauduttu!');
			
			// Tallenna lisätietokentät
			$this->CustomFieldAnswer->deleteOldAnswers($registration);
			$this->CustomFieldAnswer->saveAllAnswers($this->data, $customFields, $this->Registration->id);
			
			// Avec
			if($event['avec'] && strlen($this->data['Avec']['Registration']['name']) > 0) {
				$avec = $this->Registration->findByAvecId($registration['Registration']['id']);
				if(!$avec) {
					$this->data['Avec']['Registration']['calendar_event_id'] = $event['id'];
					$this->data['Avec']['Registration']['avec_id'] = $id;
					$this->Registration->id = null;
				} 
				else {
					$this->Registration->id = $avec['Registration']['id'];
					$this->CustomFieldAnswer->deleteOldAnswers($avec);
				}
				
				// Tallenna normaali ilmodata
				if($this->Registration->save($this->data['Avec'])) {
					$this->CustomFieldAnswer->saveAllAnswers($this->data['Avec'], $customFields, $this->Registration->id);
				}
				else {
					// Varsinainen ilmo voidaan päivittää joka tapauksessa
					$this->Session->write('flashData', $this->data);
					$this->flashError('Ilmoittautumisen muokkaus keskeytyi. Täytäthän lomakkeeseen nimen ja sähköpostiosoitteen.');
				}
				// Tallenna lisätietokentät
			}
		} 
		$this->redirect('/calendar_events/view/' . $event['id']);
	}
	
	/**
	 * Listaa kalenteritapahtuman ilmoittautumiset.
	 * Vaatii vähintään virkailijan oikeustason.
	 * 
	 * @param int $id kalenteritapahtuman tunniste
	 * @author Juhani Markkula
	 */
	function listParticipants($id = null) {
		$event = $this->CalendarEvent->findById($id);
		if(!$event) {
			$this->goToFrontpageWithMessage('Tapahtumaa ei löytynyt.');
		}
		$user = $this->currentUser();
		
		$this->set('event', $event['CalendarEvent']);
		$this->set('isAdmin', $this->User->compareUserRole($user['role'], 'virkailija') >= 0);
		$this->set('normal_registrations', $this->Registration->listParticipants($id));
		$this->render('listParticipants');
	}

	function listParticipantsAdmin($id = null) {
		$user = $this->currentUser();
		if(!$user || User::compareUserRole($user['role'], 'virkailija') < 0) {
			$this->goToFrontpageWithMessage('Ei oikeutta admin-näkymään.');
		}
		$event = $this->CalendarEvent->findById($id);
		if(!$event) {
                        $this->goToFrontpageWithMessage('Tapahtumaa ei löytynyt.');
                }
                $this->set('event', $event['CalendarEvent']);
                $this->set('fields', $event['CustomField']);
                $this->set('registrations', $this->Registration->find('all', 
					array('conditions' => array('Registration.calendar_event_id' => $id), 'order' => array('Registration.id'))));
                $this->set('isAdmin', $this->User->compareUserRole($user['role'], 'virkailija') >= 0);
                $this->render('listParticipantsAdmin');
	}

	function listParticipantsExport($id = null) {
		$user = $this->currentUser();
		if(!$user || User::compareUserRole($user['role'], 'virkailija') < 0) {
			$this->goToFrontpageWithMessage('Ei oikeutta admin-näkymään.');
		}
		$event = $this->CalendarEvent->findById($id);
		if(!$event) {
                        $this->goToFrontpageWithMessage('Tapahtumaa ei löytynyt.');
                }
                $this->set('event', $event['CalendarEvent']);
                $this->set('fields', $event['CustomField']);
                $this->set('registrations', $this->Registration->findAllByCalendarEventId($id));
                $this->set('isAdmin', $this->User->compareUserRole($user['role'], 'virkailija') >= 0);
                $this->render('listParticipantsExport');
	}

	/**
	 * Asettaa annetun flash-viestin, ohjaa selaimen määritetyn tapahtuman 
	 * näkymään ja keskeyttää actionin suorituksen.
	 *
	 * @param int $id tapahtuman tunniste
	 * @param string $msg flash-viesti
	 * @author Juhani Markkula
	 */
	function goToEventWithMessage($id, $msg = '') {
		$this->flashError($msg);
		$this->redirect('/calendar_events/view/' . $id);
		exit(0);
	}
	
}
