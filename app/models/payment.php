<?php
/**
* payment.php
*
* @author Juha-Pekka Järvenpää
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka jäsenmaksuille
*
* @author Juha-Pekka Järvenpää
* @package Kurre
*/
class Payment extends AppModel {
	var $name = 'Payment';
	
	/**
	 * Lisää tilisiirrolla maksettavan jäsenmaksulaskun kantaan.
	 * @param int $payerid maksajan jäsennumero
	 * @param int $seasons maksettavien kausien määrä
	 * @param int $membership jäsentyyppi
	 * @return mixed laskun id-numero, FALSE jos lisäys ei onnistunut
	 * @author Juha-Pekka Järvenpää
	 */
	function addBankPayment($payerid,$seasons,$membership,$paid = null) {
		$price = Pricing::getPrice($seasons,$membership);
		
		$createdsql = "NOW()";
		if ($paid != null) {
			$createdsql = $this->getUnixTimeSqlFromArray($paid);
		}
		
		// Ensin lisätään kantaan lasku jotta saadaan id-numero.
		$query = "INSERT INTO payments SET
			payer_id=".mysql_real_escape_string($payerid).",
			created=".$createdsql.",
			amount=".mysql_real_escape_string($price).",
			payment_type='tilisiirto',
			valid_until=
				DATE_ADD(
					DATE_SUB(
						'".Pricing::getNextSeasonStartDate()." 23:59:59',
					INTERVAL 1 DAY),
				INTERVAL ".($seasons-1)." YEAR)
		";
		$this->query($query);
		if($this->getAffectedRows() == 0) {
			return FALSE;
		}
		
		// Sitten päivitetään laskulle viitenumero.
		// Viitteen tulee olla 4-20 numeroa pitkä.
		// Olkoon viite vaikkapa '10nt', jossa n on laskun id ja t tarkistusnumero.
		// Näin saadaan aina vähintään 4 merkkiä pitkiä viitteitä.
		
		$paymentId = $this->getLastInsertID();
		$referenceNumber = '10'.$paymentId.
			$this->getReferenceNumberSuffix('10'.$paymentId);
		$query = "UPDATE payments SET reference_number=".$referenceNumber.
			" WHERE id=".$paymentId;
		$this->query($query);
		
		if($this->getAffectedRows() == 0) {
			return FALSE;
		}

		return $paymentId;
	}
	
	/**
	 * Lisää käteisellä maksettavan jäsenmaksulaskun kantaan.
	 * @param int $payerid maksajan jäsennumero
	 * @param int $confirmerid kirjaajan jäsennumero
	 * @param int $seasons maksettavien kausien määrä
	 * @param int $membership jäsentyyppi
	 * @return mixed laskun id-numero, FALSE jos lisäys ei onnistunut
	 * @author Juha-Pekka Järvenpää
	 */
	function addCashPayment($payerid, $confirmerid, $seasons,$membership, $paid = null) {
		$price = Pricing::getPrice($seasons,$membership);
		
		$paidsql = "NOW()";
		if ($paid != null) {
			//$this->log(print_r($paid, true));
			$paidsql = $this->getUnixTimeSqlFromArray($paid);
		}
		
		// Lisätään lasku kantaan
		$query = "INSERT INTO payments SET
			payer_id=".mysql_real_escape_string($payerid).",
			created=NOW(),
			paid=".$paidsql.",
			amount=".mysql_real_escape_string($price).",
			payment_type='kateinen',
			confirmer_id=".mysql_real_escape_string($confirmerid).",
			valid_until=
				DATE_ADD(
					DATE_SUB(
						'".Pricing::getNextSeasonStartDate()." 23:59:59',
					INTERVAL 1 DAY),
				INTERVAL ".($seasons-1)." YEAR)
		";
		$this->query($query);
		if($this->getAffectedRows() == 0) {
			return FALSE;
		}
		else {
			return $this->getLastInsertId();
		}
	}
 
	function makeCashPaid($id, $payerid, $confirmerid) {
		$payment = new Payment();
		$payment->read('',$id);
		if ($payment->data['Payment']['payer_id'] != $payerid ||
			!empty($payment->data['Payment']['paid']) ||
			$payment->data['Payment']['payment_type'] != 'tilisiirto') {
			return false;
		}

		$retval = $payment->updateAll(
				array('Payment.confirmer_id' => $confirmerid,
				      'Payment.paid' => "'".date('Y-m-d H:i:s')."'",
				      'Payment.payment_type' => "'kateinen'"),
				array('Payment.id' => $id));

		return $retval !== false;
	}

	/**
	 * Korvaa AppModel::getLastInsertID():n koska se ei näytä toimivan oikein
	 * @return int tietokantayhteyden last insert id:n
	 * @author Juha-Pekka Järvenpää
	 */
	function getLastInsertId() {
		$r = $this->query("SELECT LAST_INSERT_ID() AS l");
		return $r[0][0]['l'];
	}
	
	/**
	 * Laskee pankkisiirron viitenumeron tarkistenumeron
	 * @param string $baseNumber viitteen perusosa
	 * @return int tarkistenumero
	 * @author Juha-Pekka Järvenpää
	 */
	function getReferenceNumberSuffix($baseNumber) {
		// Viitenumeron määritelmä:
		// http://koti.mbnet.fi/~thales/tarkmerk.htm#viitenumero
		
		// Suomalainen viitenumero on aina 4 - 20 merkkiä pitkä.
		// Perusosa voi siis olla 3-19 merkkiä pitkä.
		if(strlen($baseNumber) < 3 || strlen($baseNumber) > 19) {
			return FALSE;
		}

		settype($baseNumber,'string');
		
		// kerrotaan lukuja oikealta vasemmalle luvuilla 7,3,1
		$multipliers = array(7,3,1,7,3,1,7,3,1,7,3,1,7,3,1,7,3,1,7);	// tasan 19 kpl
		$j = 0;
		$sum = 0;
		
		for($i = strlen($baseNumber)-1; $i >= 0; $i--) {
			$sum += $baseNumber[$i]*$multipliers[$j++];
		}
		
		// tarkistenumero on se luku, mikä pitää lisätä saatuun
		// summaan jotta se olisi jaollinen kymmenellä
		$ref = (10-($sum%10))%10;

		return $ref;
	}
	
	/**
	 * Palauttaa laskun luontipäivän.
	 * @param int $paymentId Laskun id-numero.
	 * @return string Laskun luontipäivä
	 * @author Juha-Pekka Järvenpää
	 */
	function getCreateDate($paymentId) {
		$query = "SELECT DATE(created) AS create_date FROM payments WHERE id=".
			mysql_real_escape_string($paymentId);
		$r = $this->query($query);
		return $r[0][0]['create_date'];
	}
	
	/**
	 * Palauttaa laskun eräpäivän.
	 * Eräpäivä on laskun luontipäivä + 14 päivää.
	 * Muutettu eräpäiväksi HETI / 20110929 / wox
	 * @param int $paymentId Laskun id-numero.
	 * @return string Laskun eräpäivä
	 * @author Juha-Pekka Järvenpää
	 */
	function getDueDate($paymentId) {
		/*
		$query = "SELECT DATE(DATE_ADD(created,INTERVAL 14 DAY)) AS due_date FROM 
			payments WHERE id=".mysql_real_escape_string($paymentId);
		$r = $this->query($query);
		return $r[0][0]['due_date'];
		*/
		return 'HETI';
	}
	
	/**
	 * Palauttaa päivän, johon asti laskulla maksettava jäsenyys on voimassa.
	 * @param int $paymentId Laskun id-numero
	 * @return string Jäsenmaksun viimeinen voimassaolopäivä
	 * @author Juha-Pekka Järvenpää
	 */
	function getLastValidDate($paymentId) {
		$query = "SELECT DATE(valid_until) AS last_valid_date FROM payments WHERE	
			id=".mysql_real_escape_string($paymentId);
		$r = $this->query($query);
		return $r[0][0]['last_valid_date'];
	}
	
	/**
	 * Vahvistaa maksetun jäsenmaksulaskun.
	 * @param int $paymentId Jäsenmaksulaskun id.
	 * @param int $confirmerId Vahvistajan käyttäjätunnuksen id.
	 * @return boolean Onnistuiko vahvistaminen
	 * @author Juha-Pekka Järvenpää
	 */
	function confirmPayment($paymentId,$confirmerId,$paid = null) {
		$paidsql = "NOW()";
		if ($paid != null) {
			$paidsql = $this->getUnixTimeSqlFromArray($paid);
		}
		$query = "UPDATE payments SET paid=".$paidsql.",confirmer_id=".$confirmerId." WHERE
			id=".mysql_real_escape_string($paymentId);
		$this->query($query);
		return($this->getAffectedRows() == 1);
	}
	
	/**$
	 * Poistaa jäsenmaksulaskun.
	 * @param int $paymentId Jäsenmaksulaskun id.
	 * @return boolean Onnistuiko jäsenmaksulaskun poistaminen
	 * @author Juha-Pekka Järvenpää
	 */
	function deletePayment($paymentId) {
		$query = "DELETE FROM payments WHERE id=".mysql_real_escape_string($paymentId);
		$this->query($query);
		return($this->getAffectedRows() == 1);
	}
	
	/**
	 * Kertoo, onko maksu maksettu.
	 * @param int $id Maksun id-numero
	 * @return boolean TRUE, mikä maksu on maksettu, muuten FALSE
	 * @author Juha-Pekka Järvenpää
	 */
	function isPaid($id) {
		$query = "SELECT paid IS NOT NULL AS is_paid FROM payments WHERE id=".
			mysql_real_escape_string($id);
		$result = $this->query($query);
		return($result['0']['0']['is_paid']=='1');
	}

	/**
	 * Palauttaa taulukon vahvistamattomista jäsenmaksuista.
	 * @param string $startDate Näytä vain jäsenmaksulaskut
	 * alkaen tästä päivästä (VVVV-KK-PP)
	 * @param string $endDate Näytä vain jäsenmaksulaskut
	 * päättyen tähän päivään (VVVV-KK-PP)
	 * @return array Taulukko vahvistamattomista jäsenmaksuista.
	 * @author Juha-Pekka Järvenpää
	 */
	function getUnpaid($startDate=null,$endDate=null) {
		$query = "
			SELECT
				p.id,
				DATE(p.created) AS date_created,
				p.reference_number,
				p.amount,
				DATE(p.paid) AS date_paid,
				CONCAT(pu.firstname,' ',pu.lastname) AS payer_name,
				pu.id AS payer_id
			FROM payments AS p
			LEFT JOIN users AS pu ON (p.payer_id=pu.id)
			WHERE
				p.paid IS NULL
			ORDER BY pu.lastname
		";
		
		if(isset($startDate) && isset($endDate)) {
			$query .= " AND DATE(p.created) >= '".mysql_real_escape_string($startDate)."'
				AND DATE(p.created) <= '".mysql_real_escape_string($endDate)."'";
		}
		
		return $this->query($query);
	}
	
	/**
	 * Tarkistaa, onko annettu syöte päivämäärä muodossa VVVV-KK-PP.
	 * Ei tarkista päivämärään järkevyyttä.
	 * @param string $date Tarkistettava syöte
	 * @return boolean Onko syöte muotoa VVVV-KK-PP
	 * @author Juha-Pekka Järvenpää
	 */
	function isDate($date) {
		return(ereg("^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$",$date));
	}
	
	/**
	 * Palauttaa taulukon käteisellä maksetuista jäsenmaksuista.
	 * @param string $startDate Näytä vain jäsenmaksulaskut
	 * alkaen tästä päivästä (VVVV-KK-PP)
	 * @param string $endDate Näytä vain jäsenmaksulaskut
	 * päättyen tähän päivään (VVVV-KK-PP)
	 * @return array Taulukko käteisellä maksetuista jäsenmaksuista.
	 * @author Juha-Pekka Järvenpää
	 */
	function getCashPaid($startDate=null,$endDate=null) {
		$query = "
			SELECT
				p.id,
				p.amount,
				DATE(p.paid) AS date_paid,
				CONCAT(pu.firstname,' ',pu.lastname) AS payer_name,
				pu.id AS payer_id,
				cu.screen_name AS confirmer_name,
				cu.id AS confirmer_id
			FROM payments AS p
			LEFT JOIN users AS pu ON (p.payer_id=pu.id)
			LEFT JOIN users AS cu ON (p.confirmer_id=cu.id)
			WHERE
				payment_type='kateinen' AND
				p.paid IS NOT NULL
		";
		
		if(isset($startDate) && isset($endDate)) {
			$query .= " AND DATE(p.created) >= '".mysql_real_escape_string($startDate)."'
				AND DATE(p.created) <= '".mysql_real_escape_string($endDate)."'";
		}
		$query .= " ORDER BY p.paid DESC";		
		return $this->query($query);
	}
	
	/**
	 * Palauttaa taulukon tilisiirrolla maksetuista jäsenmaksuista.
	 * @param string $startDate Näytä vain jäsenmaksulaskut
	 * alkaen tästä päivästä (VVVV-KK-PP)
	 * @param string $endDate Näytä vain jäsenmaksulaskut
	 * päättyen tähän päivään (VVVV-KK-PP)
	 * @return array Taulukko tilisiirrolla maksetuista jäsenmaksuista.
	 * @author Juha-Pekka Järvenpää
	 */
	function getBankPaid($startDate=null,$endDate=null) {
		$query = "
			SELECT
				p.id,
				DATE(p.created) AS date_created,
				p.reference_number,
				p.amount,
				DATE(p.paid) AS date_paid,
				CONCAT(pu.firstname,' ',pu.lastname) AS payer_name,
				pu.id AS payer_id,
				cu.screen_name AS confirmer_name,
				cu.id AS confirmer_id
			FROM payments AS p
			LEFT JOIN users AS pu ON (p.payer_id=pu.id)
			LEFT JOIN users AS cu ON (p.confirmer_id=cu.id)
			WHERE
				payment_type='tilisiirto' AND
				p.paid IS NOT NULL
		";

		if(isset($startDate) && isset($endDate)) {
			$query .= " AND DATE(p.paid) >= '".mysql_real_escape_string($startDate)."'
				AND DATE(p.paid) <= '".mysql_real_escape_string($endDate)."'";
		}
		$query .= " ORDER BY p.created DESC";
		return $this->query($query);
	}
	
	/**
	 * Korvaa CakePHP:n funktion AppModel::exists()
	 * koska se ei näytä toimivan oikein.
	 * Tarkistaa, löytyykö parametrina annettu jäsenmaksulasku tietokannasta.
	 * @param int $paymentId Jäsenmaksulaskun id.
	 * @return boolean Onko jäsenmaksulaskua annetulla id-numerolla olemassa.
	 */
	function exists($paymentId = false) {
		if ($paymentId === false) {
			$paymentId = $this->id;
		}
		$result = $this->query("SELECT COUNT(1) AS c FROM payments WHERE
			id=".mysql_real_escape_string($paymentId));
		return($result[0][0]['c'] > 0);
	}
	
	/**
	 * Tarkistaa, löytyykö käyttäjältä jo voimassaolevaa jäsenmaksulaskua.
	 * Jäsenmaksulasku on voimassa jos sen jäsenkauden päättymispäivä on
	 * vielä tulevaisuudessa.
	 * @param int $userid Käyttäjän id.
	 * @return mixed Jäsenmaksulaskun id tai FALSE jos jäsenmaksulaskua ei löydy.
	 */
	function getUserPayment($userid) {
		$query = "SELECT id FROM payments WHERE payer_id=".
			mysql_real_escape_string($userid)." AND valid_until >= NOW()";
		$result = $this->query($query);

		if(isset($result[0]['payments']['id']) &&
			is_numeric($result[0]['payments']['id'])) {
			return $result[0]['payments']['id'];	
		}
		else {
			return FALSE;
		}
	}
	
	function getUnixTimeSqlFromArray($paid) {
		if ($paid['meridian'] == 'am' && $paid['hour'] == 12) {
			$paid['hour'] = 0;
		}
		else if ($paid['meridian'] == 'pm'&& $paid['hour'] < 12) {
			$paid['hour'] = $paid['hour'] + 12;
		}
		$paidsql = "FROM_UNIXTIME(".mktime($paid['hour'], $paid['min'], 0, $paid['month'], $paid['day'], $paid['year']).")";
		return $paidsql;
	}

}

?>
