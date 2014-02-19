<?php
/**
* mailer_controller.php
*
* @author Tia Määttänen
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Ohjainluokka sähköpostin lähetystä varten
*
* @author Tia Määttänen
* @package Kurre
*/

class MailerController extends AppController {

	var $name = 'Mailer';
	var $uses = array('User', 'Registration', 'Group','Payment');
	var $components = array ('Email');	
	var $layout = 'membership';
	
	/**
	 * SMTP-yhteyden asetukset sähköpostien lähetystä varten.
	 */
	var $smtpOptions = array(
		'port' => 25,
		'host' => 'localhost',
		//'host' => 'smtp.welho.com',
		'timeout' => '15'
	); 
	 
	/**
	 * Ottaa vastaan sähköpostin lähetyslomakkeen tiedot
	 * Lomakkeelta saadaan otsikon, vastaanottajien ja viestin
	 * lisäksi käyttäjätunnus ja salasana,joita käyttäen sähköposti lähetetään
	 * @author Tia Määttänen
	 */
	
	function sendMail() {
		
		
		if(isset($this->data)) {
			$email = $this->data['User']['email'];
			$user = $this->User->findByEmail($email);
			$subject = $this->data;
			$message = $this->data;
			$this->Email->from = 'MIKS <admin@domain.local>';
			$this->Email->to = '<'.$email.'>'; 
			$this->Email->subject = '$subject'; 
			$this->Email->replyTo = 'noreply@domain.local';
		
			if ( $this->Email->send('$message')){
				$this->flashSuccess('Viesti lähetetty');
			}	else	{
					$this->flashError('Viestiä ei lähetetty');
				}
			$this->redirect('/');
		}
	}
	
	/**
	 * Näyttää ja käsittelee lomakkeen kadonneen salasanan palauttamiselle. 
	 * Jos kyseinen sähköpostiosoite löytyy kannasta, arvotaan sen 
	 * käyttäjälle uusi salasana, joka lähetetään hänen sähköpostiosoitteeseensa.
	 *
	 * @author Juhani Markkula
	 */
	function forgotPassword() {
		$this->set('url', Router::url('/forgotPassword'));
		
		if(isset($this->data)) {
			$email = $this->data['User']['email'];
			$user = $this->User->findByEmail($email);
			
			if($user) {
				$password = $this->User->resetPassword($user);
				
				$this->Email->from = 'MIKS <admin@domain.local>';  
				$this->Email->to = '<'.$email.'>';
				$this->Email->subject = 'Uusi salasanasi';
				$this->Email->delivery = 'smtp';
				$this->Email->sendAs = 'text';
				$this->Email->replyTo =  'noreply@domain.local';
				$this->Email->smtpOptions = $this->smtpOptions;
				$message = 'Uusi salasanasi MIKSin tapahtumakalenteriin on: ' . $password;
				$this->Email->send($message);
				
				$this->set('errorMessage', $this->Email->smtpError);
				$this->flashSuccess('Uusi salasana on lähetetty antamaasi osoitteeseen.');
			} else {
				$this->flashError('Sähköpostiosoitetta ei löytynyt järjestelmästä. Kokeile eri vaihtoehtoja.');
			}	
		}
	}
	
	 
	/**
	 * Näyttää näkymän new_mail.ctp.eli tyhjän sähköpostin lähetyslomakkeen
	 * 
	 * @author Tia Määttänen
	 */
	
	function newMail() {
		$this->set('title', 'Lähetä sähköpostia');
	}
	
}

?>
