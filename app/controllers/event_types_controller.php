<?php
/**
* event_types_controller.php
*
* @author Niko Kiirala
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Ohjainluokka tapahtumatyyppien muokkaamiseen.
*
* @author Niko Kiirala
* @package Kurre
*/
class EventTypesController extends AppController {
	var $name = 'EventTypes';
	var $layout = 'calendar';

	/**
	 * Callback-funktio, joka ajetaan luokkaa alustettaessa. Asettaa
	 * luokan toimintojen vaatimat oikeustasot.
	 * @author Niko Kiirala
	 */
	function beforeFilter() {
		AppController::beforeFilter();
		$this->requireRole('virkailija', a('update'));
	}

	/**
	 * Muokkaa tapahtumatyyppiä. Riippuen lomakkeelta tulleesta käytetyn
	 * submit-painikkeen nimestä, muokkaa tapahtumatyypin nimeä tai
	 * poistaa sen.
	 * @author Niko Kiirala
	 */
	function update() {
		if (!isset($this->data['EventType'])
			|| !isset($this->data['EventType']['id'])
			|| empty($this->data['EventType']['id'])) {
			$this->flashError('Muokattavaa tapahtumatyyppiä ei määritelty');
			$this->redirect('/calendar_events/manage');
			return;
		}
		$this->EventType->id = $this->data['EventType']['id'];
		$event_type = $this->EventType->read();

		if (isset($this->data['rename'])) {
			$this->set('action', 'rename');
			if (isset($this->data['value']) && !empty($this->data['value'])) {
				$event_type['EventType']['name'] = $this->data['value'];
			}
			else {
				$this->flashError('Uusi nimi puuttuu');
			}

			if ($this->EventType->save($event_type)) {
				$this->set('event_type', $event_type);
				$this->flashSuccess('Tapahtumatyypin nimeksi muutettiin "'.$this->data['value'].'".' );
			}
			else {
				$this->flashError('Muutosten tallettaminen ei onnistunut');
			}
		}
		else if (isset($this->data['delete'])) {
			$this->set('action', 'delete');
			$this->set('event_type', $event_type);
			if (!$this->EventType->delete()) {
				$this->flashError('Tapahtumatyypin poistaminen ei onnistunut');
			}
			else{
				$this->flashSuccess('Tapahtumatyyppi "'.$event_type['EventType']['name'].'" poistettu.');
			}
		}
		else {
			$this->flashError('Tuntematon toiminto');
		}
		$this->redirect('/calendar_events/manage');
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
