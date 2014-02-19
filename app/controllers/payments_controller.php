<?php

App::Import('Model', 'Pricing');

/**
* payments_controller.php
*
* @author Juha-Pekka Järvenpää
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Ohjainluokka jäsenmaksujen hallintaa varten
* 
* @author Juha-Pekka Järvenpää
* @package Kurre
*/
class PaymentsController extends AppController {

	var $name = 'Payments';
	var $uses = array('Payment','Pricing','User');
	var $pageTitle = 'Jäsentiedot';

	/**
	 * Ennen mitään muita toimintoja suoritettavat asiat.
	 * Määrittää käyttäjien oikeudet järjestelmän eri toimintoihin.
	 * @author Juha-Pekka Järvenpää
	 */
	function beforeFilter() {
		AppController::beforeFilter(false);
		$this->requireLogin(
			array('newInvoice','createInvoice','showInvoice','newPricings',
					'createPricings','payByCash','payByBank','listUnpaid',
					'listCashPaid','listBankPaid'));
		$this->requireRole('virkailija',array('payByCash'));
		$this->requireRole('jasenvirkailija',
			array('newPricings','createPricings','payByBank',
			'listUnpaid','listCashPaid','listBankPaid'));
	}

	/**
	 * Hakee kannasta nykyisen käyttäjän jäsentyyppiä
	 * vastaavan voimassa olevan hinnaston.
	 * Näyttää näkymän 'Jäsenmaksu'.
	 * @author Juha-Pekka Järvenpää
	 */
	function newInvoice() {
		$currentUser = $this->currentUser();
		
		if($paymentId = $this->Payment->getUserPayment($currentUser['id'])) {
			$this->redirect('showInvoice/'.$paymentId);
			exit();
		}	
		else {
			$pricingModel = new Pricing();
//			$this->set('pricings', $pricingModel->findAll("membership='".
//				(($currentUser['membership']=='ulkojasen')?'ulkojasen':'jasen').
//				"'",null,"seasons"));
// Markuksen korjaama:
/*
                        $this->set('pricings', $pricingModel->findAll("membership='".
                                (($currentUser['membership']=='ulkojasen')?'ulkojasen':'jasen').
				//"' AND starts < now()",null,"seasons"));
                                "' AND DATEDIFF('" . date('Y-m-d') .
				"', Pricing.starts) BETWEEN 0 AND 365",
				null,"seasons"));
*/
// Markuksen uudestaan korjaama (20100909):
			$membership = ($currentUser['membership']=='ulkojasen')?'ulkojasen':'jasen';
			$pmodel = $pricingModel->find('all', array('conditions' => 
                                                        array('membership' => $membership,
                                                              'DATEDIFF(\''.date('Y-m-d').'\', Pricing.starts) BETWEEN ? AND ?' =>
                                                              array(0,365)), 'order' => array("seasons")));
			$this->set('pricings', $pmodel);

		}
	}
	
	/**
	 * Luo nykyiselle käyttäjälle lomakkeen lähettämien tietojen
	 * mukaisen jäsenmaksulaskun (Payment) kantaan.
	 * Ohjaa lomakkeenkäsittelijään PaymentsController::showInvoice()
	 * luodun jäsenlaskumaksun tunnisteen kanssa.
	 * @author Juha-Pekka Järvenpää
	 */
	function createInvoice() {
		$currentUser = $this->currentUser();

		if(isset($_POST['seasons']) && is_numeric($_POST['seasons'])) {
			$paymentId = $this->Payment->addBankPayment(
				$currentUser['id'],$_POST['seasons'],'jasen');
			
			if($paymentId > 0) {
				// $this->flash('Jäsenmaksu luotu','/payments/showInvoice/'.$paymentId);
				$this->redirect('/payments/showInvoice/'.$paymentId);
			}
			else {
				$this->flash('Jäsenmaksua ei voitu luoda','/payments/newInvoice');
			}
		}
		else {
			$this->redirect('/payments/newInvoice');
		}
	}
	
	/**
	 * Näyttää jäsenmaksulaskun tiedot.
	 * @param int $id Jäsenmaksulaskun id.
	 * @author Juha-Pekka Järvenpää
	 */
	function showInvoice($id) {
		$currentUser = $this->currentUser();
		
		if(! $payment = $this->Payment->findById($id)) {
			$this->set('error_message','Jäsenmaksulaskua ei löytynyt');	
		}
		else {
			if($currentUser['role'] == 'jasenvirkailija' ||
				$currentUser['role'] == 'yllapitaja' ||
				$payment['Payment']['payer_id'] == $currentUser['id']) {
				
				$this->set('amount',$payment['Payment']['amount']);
				$this->set('reference_number',$payment['Payment']['reference_number']);
				$this->set('create_date',$this->Payment->getCreateDate($id));
				$this->set('due_date',$this->Payment->getDueDate($id));
				$this->set('last_valid_date',$this->Payment->getLastValidDate($id));
				$this->set('is_paid', $this->Payment->isPaid($id));		
		
				$payer = $this->User->findById($payment['Payment']['payer_id']);
				
				$this->set('payer_name',$payer['User']['firstname'].' '.$payer['User']['lastname']);
			}
			else {
				$this->set('error_message','Jäsenmaksulaskun näyttäminen estetty');
			}
		}
	}
	
	/**
	 * Luo lomakkeen, jolla jäsenmaksukausien hinnat syötetään.
	 * @author Juha-Pekka Järvenpää
	 */
	function newPricings() {
		$this->set('nextSeasonStartDate',$this->Pricing->getNextSeasonStartDate());

		if($this->Pricing->hasNextSeasonPricings()) {
			// Haetaan tiedot seuraavan kauden jäsenmaksuista
			$pricings = $this->Pricing->getNextSeasonPricings();
			for ($i = 0 ; $i < 3 ; $i++) {
				$pricings[] = array('jasen' => null, 'ulkojasen' => null);
			}
			$this->set('pricings', $pricings);
		}
		else {
			// Luodaan tyhjä taulukko jos jäsenmaksuja ei ole vielä syötetty
			$nullPricings = array();
			for($n = 1; $n <= 4; $n++) {
				$nullPricings[$n]['jasen'] = null;
				$nullPricings[$n]['ulkojasen'] = null;
			}
			$this->set('pricings',$nullPricings);
		}
		
	}
	
	/**
	 * Tallentaa jäsenmaksukaudet tietokantaan.
	 * @author Juha-Pekka Järvenpää
	 */
	function createPricings() {
		if(isset($_POST['seasons']) && is_array($_POST['seasons']) && count($_POST['seasons']) > 0) {
			$this->Pricing->deleteNextSeasonPricings();
			$ok = TRUE;
			foreach(array_keys($_POST['seasons']) as $s) {
				/* Korjaus: ei anneta virhettä tyhjästä rivistä --kiirala */
				if(empty($_POST['member_prices'][$s])
				   || empty($_POST['nonmember_prices'][$s])) {
					continue;
				}
				if(! $this->Pricing->registerPricing(
						$this->Pricing->getNextSeasonStartDate(),
						$_POST['seasons'][$s],
						$_POST['member_prices'][$s],
						$_POST['nonmember_prices'][$s])) {
					$ok = FALSE;
				}
			}
		}
		else {
			$this->flash('Et antanut yhtään jäsenyysjaksoa');
		}
		
		if($ok) {
			$this->flash('Jäsenyysjaksot tallennettu','newPricings');
		}
		else {
			$this->flash('Jäsenyysjaksojen tallentaminen ei onnistunut','newPricings');
		}
	}
	
	/**
	 * Kirjaa yhden tai useamman jäsenmaksun maksetuksi käteisellä
	 * @param array $payments Kaksiulotteinen taulukko kirjattavista jäsenmaksuista
	 * Taulukon muoto: ( (user_id=>1, seasons=>2), (user_id=>2, seasons=>1), ... )
	 * @return boolean TRUE mikäli kirjaus onnistui, muuten FALSE
	 * @author Juha-Pekka Järvenpää
	 */
	function addCashPayment($payments) {
		if(isset($payments) && is_array($payments) && count($payments) > 0) {
			$currentUser = $this->currentUser();
			foreach($payments as $p) {
				if(isset($p['seasons']) && isset($p['user_id'])) {
					$user = $this->User->findById($p['user_id']);
					$membership = $this->User->paymentMembership($user['User']['membership']);
					$paid = null;
					if (isset($p['paid'])) {
						$paid = $p['paid'];
					}
					if(! $this->Payment->addCashPayment(
						$p['user_id'], $currentUser['id'], $p['seasons'], $membership, $paid)) {
						return FALSE;
					}
				}
			}
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Kirjaa käyttäjälle käteisellä maksetun jäsenmaksun ja ohjaa takaisin
	 * kyseisen käyttäjän muokkaussivulle.
	 * @param int $id muokattavan käyttäjän tunniste
	 * @author Niko Kiirala
	 */
	function payByCash($id = null) {
		$new_payment = null;
		if ($id == null || !is_numeric($id)) {
			$this->goToFrontpageWithMessage('Käyttäjän tunniste puuttuu');
		}

		if (isset($this->data['Payment']['id'])) {
			if (!is_numeric($this->data['Payment']['id'])) {
				$this->flashError('Virhe laskun tunnisteessa');
				$this->redirect('/users/edit/' . $id);
			}
			$user = $this->currentUser();
			$result = $this->Payment->makeCashPaid($this->data['Payment']['id'], $id, $user['id']);
		}
		else {
			if (!isset($this->data['Payment']['seasons']) ||
				!is_numeric($this->data['Payment']['seasons'])) {
				$this->flashError('Virheellinen jäsenkausien määrä');
				$this->redirect('/users/edit/' . $id);
			}
			$new_payment = array('user_id' => $id,
								 'seasons' => $this->data['Payment']['seasons']);
			if (isset($this->data['Payment']['paid'])) {
				$new_payment['paid'] = $this->data['Payment']['paid'];
			}
			
			$result = $this->addCashPayment(array($new_payment));
		}
		if ($result === true) {
			$this->flashSuccess('Maksu kirjattu');
		}
		else {
			$this->flashError('Ei voitu kirjata maksua');
		}
		$this->redirect('/users/edit/' . $id);
	}
	
	function createPaidBankTransfer($id = null) {
		$new_payment = null;
		if ($id == null || !is_numeric($id)) {
			$this->goToFrontpageWithMessage('Käyttäjän tunniste puuttuu');
		}
		if (!isset($this->data['Payment']['seasons']) ||
			!is_numeric($this->data['Payment']['seasons'])) {
			$this->flashError('Virheellinen jäsenkausien määrä');
			$this->redirect('/users/edit/' . $id);
		}
		$new_payment = array('user_id' => $id,
							 'seasons' => $this->data['Payment']['seasons']);

		$user = $this->User->findById($id);
		$membership = $this->User->paymentMembership($user['User']['membership']);
		$paid = $this->data['Payment']['paid'];
		$seasons = $this->data['Payment']['seasons'];
		$currentUser = $this->currentUser();
		
		$paymentId = $this->Payment->addBankPayment($id,$seasons,$membership,$paid);
		
		if(!is_numeric($paymentId)) {
			$this->flashError('Laskun luonti epäonnistui');
			$this->redirect('/users/edit/' . $id);
		}
		
		if($this->Payment->confirmPayment($paymentId, $currentUser['id'], $paid)) {
			$this->flashSuccess('Maksu kirjattu');
		}
		else {
			$this->flashError('Ei voitu kirjata maksua');
		}
		$this->redirect('/users/edit/' . $id);
	}

	/**
	 * Hyväksyy tai poistaa yhden tai useampia jäsenmaksuja.
	 * Vaatii kirjautuneen käyttäjän jäsenvirkailijan oikeuksin.
	 * @author Juha-Pekka Järvenpää
	 */
	function payByBank() {
		
		$currentUser = $this->currentUser();
		
		$messages = array();
		
		if(isset($_POST['selected_payments']) &&
			is_array($_POST['selected_payments']) &&
			count($_POST['selected_payments'])) {
			foreach(array_keys($_POST['selected_payments']) as $paymentId) {
				if($this->Payment->exists($paymentId)) {
					if(! $this->Payment->isPaid($paymentId)) {
						if(isset($_POST['confirm'])) {
							if(! $this->Payment->confirmPayment($paymentId,$currentUser['id'])) {
								array_push(
									$messages,'Jäsenmaksulaskua '.$paymentId.' ei voitu kirjata');
							}
						}
						elseif(isset($_POST['delete'])) {
							if(! $this->Payment->deletePayment($paymentId)) {
								array_push(
									$messages,'Jäsenmaksulaskua '.$paymentId.' ei voitu poistaa');
							}
						}
					}
					else {
						array_push(
							$messages,'Jäsenmaksulasku '.$paymentId.' on jo merkitty maksetuksi');
					}
				}
				else {
					array_push($messages,'Jäsenmaksulaskua '.$paymentId.' ei löytynyt');
				}
			}
		}
		else {
			array_push($messages,'Et valinnut yhtään jäsenmaksulaskua');
		}
		
		if(count($messages) == 0) {
			if(isset($_POST['confirm'])){
				$this->flashSuccess('Maksu(t) kirjattu.');
			}
			else{
				$this->flashSuccess('Maksu(t) poistettu.');
			}
		}
		else{
			$this->flashError(implode('. ',$messages).'.');
		}
		
		$this->redirect('/payments/listUnpaid');
	}
	
	/**
	 * Listaa käyttäjät joilla on maksamaton jäsenmaksulasku.
	 * Ottaa $_GET-taulukossa kaksi valinnaista parametriä startDate ja endDate,
	 * joilla voidaan rajoittaa listaus näiden päivämäärien välille.
	 * Päivämäärät annetaan muodossa VVVV-KK-PP.
	 * @author Juha-Pekka Järvenpää
	 */
	function listUnpaid() {
		$queryParts = array();
		
		if(isset($_GET['startDate']) && isset($_GET['endDate']) &&
			$this->Payment->isDate($_GET['startDate']) &&
			$this->Payment->isDate($_GET['endDate'])) {
			$startDate = $_GET['startDate'];
			$endDate = $_GET['endDate'];
			$this->set('startDate',$startDate);
			$this->set('endDate',$endDate);
			$queryParts['startDate'] = $startDate;
			$queryParts['endDate'] = $endDate;
		}
		else {
			$startDate = NULL;
			$endDate = NULL;
			$this->set('startDate',NULL);
			$this->set('endDate',NULL);
		}
	
		$this->set('text',(isset($_GET['format']) && $_GET['format'] == 'text'));		
		$this->set('results',$this->Payment->getUnpaid($startDate,$endDate));
		$this->set('queryParts',$queryParts);
	}
	
	/**
	 * Listaa käyttäjät joilla on käteisellä maksettu jäsenmaksu.
	 * Ottaa $_GET-taulukossa kaksi valinnaista parametriä startDate ja endDate,
	 * joilla voidaan rajoittaa listaus näiden päivämäärien välille.
	 * Päivämäärät annetaan muodossa VVVV-KK-PP.
	 * @author Juha-Pekka Järvenpää
	 */
	function listCashPaid() {
		$queryParts = array();
		
		if(isset($_GET['startDate']) && isset($_GET['endDate']) &&
			$this->Payment->isDate($_GET['startDate']) &&
			$this->Payment->isDate($_GET['endDate'])) {
			$startDate = $_GET['startDate'];
			$endDate = $_GET['endDate'];
			$this->set('startDate',$startDate);
			$this->set('endDate',$endDate);
			$queryParts['startDate'] = $startDate;
			$queryParts['endDate'] = $endDate;
		}
		else {
			$startDate = NULL;
			$endDate = NULL;
			$this->set('startDate',NULL);
			$this->set('endDate',NULL);
		}
		
		$this->set('text',(isset($_GET['format']) && $_GET['format'] == 'text'));		
		$this->set('results',$this->Payment->getCashPaid($startDate,$endDate));
		$this->set('queryParts',$queryParts);	
	}
	
	/**
	 * Listaa käyttäjät joilla on tilisiirrolla maksettu jäsenmaksu.
	 * Ottaa $_GET-taulukossa kaksi valinnaista parametriä startDate ja endDate,
	 * joilla voidaan rajoittaa listaus näiden päivämäärien välille.
	 * Päivämäärät annetaan muodossa VVVV-KK-PP.
	 * @author Juha-Pekka Järvenpää
	 */
	function listBankPaid($startDate='',$endDate='') {
		$queryParts = array();
		
		if(isset($_GET['startDate']) && isset($_GET['endDate']) &&
			$this->Payment->isDate($_GET['startDate']) &&
			$this->Payment->isDate($_GET['endDate'])) {
			$startDate = $_GET['startDate'];
			$endDate = $_GET['endDate'];
			$this->set('startDate',$startDate);
			$this->set('endDate',$endDate);
			$queryParts['startDate'] = $startDate;
			$queryParts['endDate'] = $endDate;
		}
		else {
			$startDate = NULL;
			$endDate = NULL;
			$this->set('startDate',NULL);
			$this->set('endDate',NULL);
		}
		
		$this->set('text',(isset($_GET['format']) && $_GET['format'] == 'text'));		
		$this->set('results',$this->Payment->getBankPaid($startDate,$endDate));
		$this->set('queryParts',$queryParts);
	}

}

?>
