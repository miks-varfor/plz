<?php
/**
* locations_controller.php
*
* @author Niko Kiirala
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Ohjainluokka vakiopaikkojen muokkaamiseen.
*
* @author Niko Kiirala
* @package Kurre
*/
class LocationsController extends AppController {
	var $name = 'Locations';
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
	 * Muokkaa vakiopaikkaa. Riippuen lomakkeelta tulleesta käytetyn
	 * submit-painikkeen nimestä, muokkaa paikan nimeä, muokkaa paikan
	 * karttalinkkiä tai poistaa paikan.
	 * @author Niko Kiirala
	 */
	function update() {
		if (!isset($this->data['Location'])
			|| !isset($this->data['Location']['id'])
			|| empty($this->data['Location']['id'])) {
			$this->set('errorMessage', 'Muokattavaa paikkaa ei määritelty');
			return;
		}
		$this->Location->id = $this->data['Location']['id'];
		$location = $this->Location->read();

		if (isset($this->data['rename'])) {
			$this->set('action', 'rename');
			if (isset($this->data['value']) && !empty($this->data['value'])) {
				$location['Location']['name'] = $this->data['value'];
			}
			else {
				$this->set('errorMessage', 'Uusi nimi puuttuu');
			}

			if ($this->Location->save($location)) {
				$this->set('location', $location);
			}
			else {
				$this->set('errorMessage', 'Muutosten tallettaminen ei onnistunut');
			}
		}
		else if (isset($this->data['map'])) {
			$this->set('action', 'map');
			if (isset($this->data['value']) && !empty($this->data['value'])) {
				$location['Location']['map'] = $this->data['value'];
			}
			else {
				$location['Location']['map'] = '';
			}

			if ($this->Location->save($location)) {
				$this->set('location', $location);
			}
			else {
				$this->set('errorMessage', 'Muutosten tallettaminen ei onnistunut');
			}
		}
		else if (isset($this->data['delete'])) {
			$this->set('action', 'delete');
			$this->set('location', $location);
			if (!$this->Location->delete()) {
				$this->set('errorMessage', 'Paikan poistaminen ei onnistunut');
			}
		}
		else {
			$this->set('errorMessage', 'Tuntematon toiminto');
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
