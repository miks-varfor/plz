<?php 
/**
* authentications_controller.php
*
* @author Juhani Markkula, Niko Kiirala
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Ohjainluokka käyttäjän session käsittelyyn.
* 
* @author Juhani Markkula, Niko Kiirala
* @package Kurre
*/
class AuthenticationsController extends AppController {
	var $name = 'Authentications';
	var $uses = 'User';

	/**
	* Kirjaa käyttäjän sisään kirjautumislomakkeelta.
	* Kirjautumisen jälkeen käyttäjä siirretään järjestelmän etusivulle tai 
	* data[Authentication][redirect] -lomakekentässä annetulle sivulle.
	* Jos käyttäjän viimeisimmästä tietojen muutoksesta on yli 180
	* päivää, käyttäjä ohjataan kuitenkin välissä omien tietojen
	* tarkistamiseen.
	* 
	* @author Juhani Markkula, Niko Kiirala
	*/
	function login() {
		if(!empty($this->data))	{
			if(strlen($this->data['Authentication']['username']) > 0 &&
				($user = $this->User->validateLogin($this->data['Authentication'])) == true) {
				$this->loginUser($user);
				if (strtotime($user['modified']) + 180 * 24 * 60 * 60 < time()) {
					$diversion = '/users/edit';
					$this->flashSuccess('Viimeisestä tietojesi muutoksesta on yli 180 päivää. Ole hyvä ja tarkista sekä tallenna tietosi.');
				}
				else {
					$this->flashSuccess('Olet kirjautunut sisään!');
				}
			}
			else {
				$this->flashError('Antamasi tunnus tai salasana on väärin.');
			}
		}
		
		if(strlen($this->data['Authentication']['redirect']) > 0) {
			$redirect = $this->data['Authentication']['redirect'];
		}
		else {
			$redirect = '/';
		}

		if (isset($diversion)) {
			$this->Session->write('diversion', $redirect);
			$this->redirect($diversion);
		}
		else {
			$this->redirect($redirect);
		}
	}
	
	/**
	* Kirjaa käyttäjän ulos ja siirtää hänet etusivulle.
	* 
	* @author Juhani Markkula
	*/
	function logout() {
		$this->Session->destroy();
		$this->flashSuccess('Olet kirjautunut ulos.');
		$this->redirect('/');
	}
	
}

?>
