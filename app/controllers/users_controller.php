<?php
/**
* users_controller.php
*
* @author Niko Kiirala
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Ohjainluokka käyttäjän tietojen käsittelyyn. Mahdollistaa uusien käyttäjien
* luomisen, olemassa olevien käyttäjien tietojen katselun ja muokkaamisen,
* käyttäjien etsimisen sekä poistamisen.
*
* @author Niko Kiirala
* @package Kurre
*/
class UsersController extends AppController
{
	var $name = 'Users';
	var $layout = 'membership';
	var $components = array('Session', 'Ldap');
	var $uses = array('User', 'Group', 'Pricing', 'Payment');
	var $pageTitle = 'Jäsentiedot';

	/* edit-funktion käyttämät käyttäjien muokkausoikeudet. Kussakin
	 * taulukossa listataan niiden tietojen nimet, joita voi muokata. */

	/** Vain katseluoikeudet (virkailija tutkii toisen tietoja) */
	var $modify_none = array();
	/** Oikeudet omien tietojen muokkaamiseen */
	var $modify_user = array('screen_name', 'email', 'residence',
							 'phone', 'firstname', 'lastname', 'username',
							 'hyy_member', 'tktl', 'groups',
							 'password', 'faculty');
	/** Jäsenvirkailijan muokkausoikeudet */
	var $modify_jv   = array('screen_name', 'email', 'residence',
							 'phone', 'firstname', 'lastname', 'username',
							 'hyy_member', 'tktl', 'membership',
							 'add_payment', 'password', 'faculty');
	/** Ylläpitäjän muokkausoikeudet */
	var $modify_admin = array('screen_name', 'email', 'residence',
							  'phone', 'firstname', 'lastname', 'username',
							  'hyy_member', 'tktl', 'membership',
							  'role', 'created', 'password', 'groups',
							  'password', 'add_payment', 'faculty', 'set_payment_date');

	/**
	 * Callback-funktio, joka ajetaan luokkaa alustettaessa. Asettaa
	 * luokan toimintojen vaatimat oikeustasot.
	 * @author Niko Kiirala
	 */
	function beforeFilter() {
		AppController::beforeFilter(false);
		$this->requireLogin(a('edit'));
		$this->requireRole('virkailija', a('listSelected', 'find'));
		$this->requireRole('jasenvirkailija', a('setMembership'));
		$this->requireRole('yllapitaja', a('delete'));
	}

	/**
	 * Lukee annetuista lomakkeelta tulleista parametreista, mitä ryhmiä
	 * käyttäjä on valinnut ja tuottaa niistä cakePHP:n tallennettavaksi
	 * sopivan taulukon.
	 * @param array $data lomakkeelta tulleet parametrit,
	 * yleensä $this->data['User']
	 * @return array valitut ryhmät
	 * @author Niko Kiirala
	 */
	private function readGroups($data) {
		$ret = array('Group' => array());
		foreach($data as $name => $val) {
			if (preg_match('/^list_(\d+)$/', $name, $matches) && $val !== '0') {
				$ret['Group'][] = $matches[1];
			}
		}
		return $ret;
	}

	/**
	 * Tunnusten luominen järjestelmään ja uuden käyttäjän luonti.
	 * Kirjautumattomalla käyttäjällä toimii jäseneksi liittymisenä,
	 * virkailijalla puolestaan uuden käyttäjän luomisena.
	 * Erona se, että virkailija voi valita maksun suorittamistavan ja
	 * jäsentyypin, kun taas kirjautumattoman käyttäjän on maksettava
	 * tilisiirrolla ja normaalikäyttäjän hinta.
	 * @author Niko Kiirala
	 */
	function add()
	{
		$group_model = new Group();
		$this->set('mailing_lists',
				   $group_model->find('all', array('recursive' => false)));
		$pricing_model = new Pricing();
		$this->set('pricings', $pricing_model->findAllByMembership('jasen'));

		$current_user = $this->currentUser();
		if ($current_user === false) {
			$user_logged = false;
		}
		else {
			if ($this->User->compareUserRole($current_user['role'], 'virkailija') < 0) {
				$this->goToFrontpageWithMessage('Olet jo rekisteröitynyt järjestelmään');
			}
			$user_logged = true;
		}
		$this->set('user_logged', $user_logged);

		if (!empty($this->data)) {
			/* Aseta oletustiedot, joihin käyttäjä ei voi vaikuttaa */
			$this->data['User']['membership'] = 'ei-jasen';
			$this->data['User']['role'] = 'kayttaja';
			$this->data['User']['deleted'] = false;
			
			/* Aseta aina tktl = true */
			$this->data['User']['tktl'] = true;

			/* Muodosta kutsumanimi etu- ja sukunimestä */
			$this->data['User']['screen_name'] = $this->data['User']['call_name'] . ' ' . $this->data['User']['lastname'];

			/* Tarkista, että annetut salasanat täsmäävät */
			if ($this->data['User']['password1'] == $this->data['User']['password2'])
			{
				$pass = $this->User->encryptPassword($this->data['User']['password1']);
				$this->data['User'] = array_merge($this->data['User'], $pass);
			}

			/* Hae LDAP:sta tunnuksen tiedot tarkistusta varten */
			$ldap = $this->Ldap->findUser($this->data['User']['username']);
			if ($ldap !== false) {
				$this->data['User']['ldap'] = $ldap;
			}

			if ($this->User->save($this->data)) {
				$user = $this->User->read();
				/* Päivitä jäsennumeroksi suurin + 1 */
				$max = $this->User->query("SELECT MAX(member_number) as number FROM users", false);
				$max = $max[0][0]['number'];
				//$this->log("Max member: ".$max);
				
				$user['User']['member_number'] = $max + 1;
				$this->User->save($user);
				
				if (!$user_logged) {
					$this->loginUser($this->User->id);
				}

				/* Lue ryhmävalinnat */
				$group = $this->User->findById($this->User->id);
				$group['Group'] = $this->readGroups($this->data['User']);
				$this->User->save($group);
				
				if ($user_logged) {
					$this->redirect('/users/edit/' . $this->User->id);
				}
				else {
					$this->redirect('/payments/newInvoice');
				}
			}
			else {
				$this->render();
			}
		}
	}

	/**
	 * Näyttää käyttäjän muokkauslomakkeen tai käyttäjän tiedot,
	 * riippuen kuinka paljon nykyisellä käyttäjällä on oikeuksia
	 * tutkia asiakkaan tietoja.
	 * @param int $id Käyttäjän tunniste
	 * @author Niko Kiirala
	 */
	function edit($id = null) {
		$current_user = $this->currentUser();

		/* Jos ei ole annettu muokattavaa asiakasta, muokataan
		 * käyttäjää itseään */
		if ($id == null || !is_numeric($id)) {
			$id = $current_user['id'];
		}

		/* Haetaan listat mahdollisista valittavista postituslistoista
		 * jäsenmaksujaksoista */
		$group_model = new Group();
		$this->set('mailing_lists',
				   $group_model->find('all', array('recursive' => 0)));

		$membership = $current_user['membership'];
		if ($membership == 'ei-jasen' || $membership == 'erotettu') {
			$membership = 'jasen';
		}
		$pricing_model = new Pricing();
		$this->set('pricings',
				   $pricing_model->find('all', array('conditions' => 
							array('membership' => $membership,
						              'DATEDIFF(\''.date('Y-m-d').'\', Pricing.starts) BETWEEN ? AND ?' =>
							      array(0,365)), 'order' => array("seasons"))));

		/* Aseta teksti, muokataanko käyttäjän omia tietoja vai jonkun muun
		 * tietoja */
		if ($id == $current_user['id']) {
			$this->set('page_title', 'Muokkaa omia tietoja');
		}
		else {
			$this->set('page_title', 'Muokkaa käyttäjän tietoja');
		}

		$this->User->recursive = 0;

		/* Hae muokattavan käyttäjän tiedot */
		$this->User->id = $id;
		$user = $this->User->read();

		/* Jos tietoja ei löydy, pysäytetään tähän. Kuitenkin jos nykyinen
		 * käyttäjä on tavallinen käyttäjä, annetaan suorituksen valua
		 * funktion loppuun, missä ilmoitetaan riittämättömistä oikeuksista */
		if (empty($user) &&
			User::compareUserRole($current_user['role'], 'virkailija') >= 0) {
			$this->set('errorMessage', 'Virheellinen käyttäjän tunniste.');
			$this->set('modify', false);
			return;
		}

		/* Haetaan tieto, onko käyttäjällä maksettu jäsenmaksu.
		 * Jos on, näkymässä ei näytetä uuden maksun kirjaamista */
		$payment = $this->Payment->getUserPayment($id);
		if ($payment) $payment = $this->Payment->findById($payment);
		$this->set('payment', $payment);

		/* Jäsenvirkailijaa alemmilla tasoilla voi muokata vain omia
		 * tietojaan, siitä ylöspäin kaikkien tietoja. */
		if ($id == $current_user['id'] ||
		    User::compareUserRole($current_user['role'], 'jasenvirkailija') >= 0) {
			/* Valitse, mitä tietoja nykyinen käyttäjä saa muokata */
			if ($id == $current_user['id']) {
				$modify = $this->modify_user;
			}
			else {
				$modify = array();
			}
			if (($current_user['role'] == 'virkailija' || $current_user['role'] == 'tenttiarkistovirkailija') && $id != $current_user['id']) { // Omia käteismaksuja ei voi hyväksyä
				$modify[] = 'add_payment';
			}
			if ($current_user['role'] == 'jasenvirkailija') { 
				$modify = array_merge($modify, $this->modify_jv);
				if($id == $current_user['id']) { // omia käteismaksuja ei voi hyväksyä
					unset($modify[array_search('add_payment', $modify)]);
				}
			}
			if ($current_user['role'] == 'yllapitaja') {
				$modify = array_merge($modify, $this->modify_admin);
				 if($id == $current_user['id']) { // omia käteismaksuja ei voi hyväksyä
					unset($modify[array_search('add_payment', $modify)]);
                                }
			}
			$this->set('modify', $modify);

			if (!empty($this->data)) {
				/* Lomakkeelta on syötetty dataa, joten validoi ja talleta
				 * syötetty data. */

				/* Muokataan vain sallitut muokattavat. */
				foreach ($modify as $key) {
					if (isset($this->data['User'][$key])) {
						if ($key == 'created') {
							$user['User'][$key] = $this->parseDateTime($this->data['User'][$key]);
						}
						else if($key == 'role' && ($this->data['User']['membership'] == 'erotettu' || $this->data['User']['membership'] == 'ei-jasen')){
							$user['User'][$key] = 'kayttaja';
						}
						else {
							$user['User'][$key] = $this->data['User'][$key];
						}
					}
				}

				/* Jos lomakkeelta on annettu salasana ja annetut salasanat
				 * ovat samat, luo siitä hajautusarvo. */
				if (array_search('password', $modify) !== false
					&& !empty($this->data['User']['password1'])) {
					if ($this->data['User']['password1']
						== $this->data['User']['password2']) {
						$pass = $this->User->encryptPassword($this->data['User']['password1']);
						$user['User'] = array_merge($user['User'], $pass);
					}
					else {
						/* Jos salasanat eivät olleet samat, ei pidetä
						 * vanhaa salasanaa, aiheutetaan virheilmoitus */
						$user['User']['hashed_password'] = '';
						$user['User']['salt'] = '';
					}
				}

				/* Lue ryhmävalinnat */
				$user['Group'] = $this->readGroups($this->data['User']);

				/* Merkitse tiedot päivitetyiksi, vaikkei mitään olisi
				 * muutettu. Tarvitaan, jotta ajoittain vaaditun tietojen
				 * tarkistamisen yhteydessä ei ole pakko muuttaa mitään
				 * tietoa. */
				$user['User']['modified'] = date('Y-m-d H:i:s', time());

				if ($this->User->save($user)) {
					$this->flashSuccess('Käyttäjän tietojen muokkaus onnistui');
					if ($this->Session->check('diversion')) {
						$redir = $this->Session->read('diversion');
						$this->Session->delete('diversion');
						$this->redirect($redir);
					}
					else {
						$this->redirect('/users/edit/' . $id);
					}
				}
				else {
					$this->set('errorMessage', 'Tapahtui virhe.');
					$this->data = $user;
					$this->render();
				}
			}
			else {
				// Näytä muokkauslomake
				$this->data = $user;
				if (User::compareUserRole($current_user['role'], 'jasenvirkailija') >= 0) {
					$ldap = $this->Ldap->findUser($this->data['User']['username']);
					$this->data['ldap'] = $ldap;
				}
			}
		}
		else if (User::compareUserRole($current_user['role'], 'virkailija') >= 0) {
			/* Käyttäjällä ei ole oikeutta muokata tätä asiakasta,
			 * mutta voi kuitenkin katsella tietoja */
			$modify = $this->modify_none;
			$modify[] = 'add_payment';
			$this->set('modify', $modify);
			$this->data = $user;
		}
		else {
			/* Käyttäjällä ei ole mitään oikeuksia tämän asiakkaan
			 * tietoihin. */
			$this->set('errorMessage', 'Pääsy estetty. Sinulla ei ole oikeuksia muokata tämän käyttäjän tietoja.');
			$this->set('modify', false);
		}
	}

	/**
	 * Listaa annettujen hakuehtojen mukaiset käyttäjät. Jos hakuehtoja ei
	 * ole annettu, näytetään kaikki käyttäjät. Jos on annettu useita
	 * hakuehtoja, näytetään ne käyttäjät, joihin sopivat kaikki hakuehdot.
	 * Hakuehtojen keskinäinen järjestys ei ole merkitsevä.
	 * Tuetut hakuehdot: (funktiosta User::parse_search_condition)
	 *   paid - käyttäjän jäsenmaksu on voimassa
	 *   nonpaid - käyttäjän jäsenmaksu ei ole voimassa
	 *   nonmember - käyttäjä ei ole jäsen
	 *   member - käyttäjä on jäsen
	 *   revoked - käyttäjä on erotettu
	 * @param string $search1 ensimmäinen hakuehto
	 * @param string $search2 toinen hakuehto
	 * @author Niko Kiirala
	 */
	function listSelected($search1 = null, $search2 = null) {
		/* Valitaan, tuotetaanko tekstimuotoinen vai normaali listaus */
		if (isset($this->data['text'])) {
			$this->layout = 'userlist';
			$this->set('style', 'text');
			header('Content-Type: text/plain; charset=utf-8');
		}
		else {
			$this->set('style', 'normal');
		}

		/* Kasataan annetut hakuehdot taulukkoon sekä takaisin samaan hakuun
		 * viittaavaksi kyselyksi */
		$conditions = array();
		$query = '';
		if ($search1 !== null) {
			$conditions[] = $search1;
			$query .= '/' . $search1;
		}
		if ($search2 !== null) {
			$conditions[] = $search2;
			$query .= '/' . $search2;
		}
		$this->set('query', $query);

		/* Asetetaan listaukselle otsikko. Tunnetuille listauksille näytetään
		 * suomenkielinen otsikko, muille hakuehto sellaisenaan. */
		$list_names = array('' => 'Kaikki käyttäjät',
							'/paid' => 'Jäsenmaksun maksaneet käyttäjät',
							'/nonpaid' => 'Jäsenmaksuaan maksamattomat käyttäjät',
							'/member/nonpaid' => 'Jäsenmaksuaan maksamattomat jäsenet',
							'/paid/nonmember' => 'Jäseneksi hyväksymistä odottavat',
							'/paid_or_new/nonmember' => 'Jäseneksi hyväksymistä odottavat',
							'/revoked' => 'Erotetut käyttäjät',
							'/member' => 'Kaikki jäsenet');
		if (isset($list_names[$query])) {
			$this->set('listing_type', $list_names[$query]);
			//$this->set('title', $list_names[$query]);
		}
		else {
			$this->set('listing_type', $query);
			//$this->set('title', $query);
		}

		/* Tietyistä hauista pääsee suorittamaan erikoistoimintoja */
		if ($query == '/nonpaid' || $query == '/member/nonpaid') {
			$this->set('edit_link', 'setMembership/erotettu');
			$this->set('edit_text', 'Erota valitut jäsenet');
		}
		else if ($query == '/paid/nonmember' || $query == '/paid_or_new/nonmember') {
			$this->set('edit_link', 'setMembership/hyvaksy');
			$this->set('edit_text', 'Hyväksy valitut jäseniksi');
		}
		else if ($query == '/revoked') {
			$this->set('edit_link', 'delete');
			$this->set('edit_text', 'Poista henkilöt tietokannasta');
		}

		/* Lue, mitä kenttiä listauksessa pitäisi näyttää */
		if (!empty($this->data) && !empty($this->data['User']['fields'])) {
			$fields = array_merge(array('User.id'),
								  $this->data['User']['fields']);
		}
		else {
			$fields = array('User.id', 'User.firstname', 'User.lastname', 'User.email', 'Payment.paid', 'Payment.valid_until', 'User.membership');
		}
		$this->set('fields', $fields);

		/* Lopuksi suoritetaan itse haku */
		$results = $this->User->userList($conditions, $fields);
		$this->set('results', $results);
	}

	/**
	 * Etsii get-parametrina 'query' annettua kyselyä vastaavia käyttäjiä.
	 * Kysely vastaa käyttäjää, jos se esiintyy osana käyttäjän
	 * yliopistotunnusta, nimeä, näyttönimeä tai sahköpostiosoitetta.
	 * Jos löytyy vain yksi käyttäjä, ohjaa tämän käyttäjän muokkaukseen,
	 * jos useampia, näytää listan näistä käyttäjistä.
	 * @author Niko Kiirala
	 */
	function find() {
		$search = null;
		if (isset($this->params['url']['query'])) {
			$search = $this->params['url']['query'];
		}

		if (!empty($this->params['url']['query'])) {
			$like_cond = '%' . $search . '%';
			$condition = array('or' => array('User.username LIKE' => $like_cond,
							 'User.firstname LIKE' => $like_cond,
							 'User.lastname LIKE' => $like_cond,
							 'User.screen_name LIKE' => $like_cond,
							 'User.email LIKE' => $like_cond),
							 'User.deleted <>' => '1');
			$fields = array('User.id', 'User.username', 'User.screen_name',
							'User.email');
			$result = $this->User->findAll($condition, $fields,
										   'User.screen_name', 0);
			if (count($result) == 1) {
				$this->redirect('/users/edit/' . $result[0]['User']['id']);
				exit(0);
			}

			$this->set('users', $result);
		}
		else {
			$this->set('error', 'Anna hakuehto');
		}
	}

	/**
	 * Asettaa jäsentason käyttäjille, joiden id:t on annettu lomakkeelta.
	 * @param string $new_membership Uusi jäsentaso tai sana 'hyvaksy',
	 * jolloin TKTL:n opiskelijat asetetaan jäseniksi ja muut ulkojäseniksi
	 * @author Niko Kiirala
	 */
	function setMembership($new_membership = null) {
		if ($new_membership !== null) {
			$updated = 0;
			$modify = array();
			foreach($this->data['User'] as $id => $value) {
				if ($value == '1') {
					$modify[] = $id;
					$updated++;
				}
			}
			
			if ($new_membership == 'hyvaksy') {
				// BUGBUG? Ei tuollaisen pitäisi vaatia heittomerkkejä
				// ympärilleen
				$this->User->updateAll(array('membership' => "'jasen'"),
									   array('User.id' => $modify));
				// MIKSin kannassa tehdään kaikista varsinaisia jäseniä.
				// $this->User->updateAll(array('membership' => "'ulkojasen'"),
				// 					   array('User.id' => $modify,
				// 							 'User.tktl' => false));

				$this->set('new_membership', 'jäsen tai ulkojäsen');
			}
			else if($new_membership == "ei-jasen" || $new_membership == "erotettu"){
				$this->User->updateAll(array('membership' => '\'' . $new_membership . '\'',
									         'role' => "'kayttaja'"),
									   array('User.id' => $modify));
				$this->set('new_membership', $new_membership);
			}
			else {
				$this->User->updateAll(array('membership' => '\'' . $new_membership . '\''),
									   array('User.id' => $modify));
				$this->set('new_membership', $new_membership);
			}
			$this->set('update_count', $updated);
		}
		else {
			$this->set('error', 'Uusi jäsentaso puuttuu');
		}
	}

	/**
	 * Poistaa käyttäjät, joiden id:t on annettu lomakkeelta.
	 * @author Niko Kiirala
	 */
	function delete() {
		$delete_fields = array('username', 'name', 'screen_name', 'email',
							   'residence', 'phone', 'hyy_member',
							   'membership', 'role', 'hashed_password',
							   'salt', 'tktl');
		$updated = 0;
		foreach($this->data['User'] as $id => $value) {
			if ($value == '1') {
				$user = array();
				$user['User'] = array();
				$user['User']['id'] = $id;
				$user['User']['deleted'] = true;
				foreach($delete_fields as $name) {
					$user['User'][$name] = null;
				}
				$this->User->save($user, false);
				$updated++;					
			}
		}
		$this->set('update_count', $updated);
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
