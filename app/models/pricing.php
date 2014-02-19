<?php
/**
* pricing.php
*
* @author Juha-Pekka Järvenpää
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka jäsenmaksukausille
*
* @author Juha-Pekka Järvenpää
* @package Kurre
*/
class Pricing extends AppModel
{
	var $name = 'Pricing';
	
	/**
	 * Palauttaa nykyisen hinnan kausien määrän ja jäsentyypin mukaan
	 * @param int $seasons kausien määrä
	 * @param string $membership jäsentyyppi
	 * @return string hintatieto
	 * @author Juha-Pekka Järvenpää
	 */
	function getPrice($seasons,$membership) {
		//Markus lisäsi kyselyyn starts < now()
		$r = $this->query("SELECT price FROM pricings WHERE membership='".$membership."' AND seasons=".$seasons.
			" AND starts < now()");
		return($r[0]['pricings']['price']);
	}
	
	/**
	 * Kirjaa yksittäisen jäsenmaksun hinnan.
	 * @param $starts string Jäsenmaksukauden alkupäivämäärä muodossa YYYY-MM-DD
	 * @param $seasons int Jäsenmaksukauden pituus vuosissa
	 * @param $memberPrice float Jäsenmaksun hinta jäsenelle
	 * @param $nonMemberPrice float Jäsenmaksun hinta ulkojäsenelle
	 * @return bool Onnistuiko kirjaus
	 * @author Juha-Pekka Järvenpää
	 */
	function registerPricing($starts,$seasons,$memberPrice,$nonMemberPrice) {
		if(isset($starts) && ereg("^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$",$starts) &&
		   isset($seasons) && is_numeric($seasons) &&
		   isset($memberPrice) && is_numeric($memberPrice) &&
		   isset($nonMemberPrice) && is_numeric($nonMemberPrice)) {
		   
			$this->query("INSERT INTO pricings SET membership='jasen',seasons=".$seasons.",price=".$memberPrice.",starts='".$starts."'");
			$memberDone = ($this->getAffectedRows()==1);
			
			$this->query("INSERT INTO pricings SET membership='ulkojasen',seasons=".$seasons.",price=".$nonMemberPrice.",starts='".$starts."'");
			$nonMemberDone = ($this->getAffectedRows()==1);
				
			return($memberDone && $nonMemberDone);
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Tarkistaa, onko seuraavan jäsenmaksukauden hinnasto syötetty.
	 * @return bool TRUE, jos seuraavan jäsenmaksukauden hinnasto on syötetty, muuten FALSE
	 * @author Juha-Pekka Järvenpää
	 */
	function hasNextSeasonPricings() {
		$query = "SELECT COUNT(1) AS a FROM pricings WHERE DATE(starts)='".$this->getNextSeasonStartDate()."'";
		$results = $this->query($query);
		return($results['0']['0']['a']>0);
	}

	/**
	 * Palauttaa seuraavan jäsenmaksukauden alkamispäivän.
	 * @return string Seuraavan jäsenmaksukauden alkamispäivä muodossa YYYY-MM-DD
	 * @author Juha-Pekka Järvenpää
	 */
	function getNextSeasonStartDate() {
		$seasonStartMonth = 1;
		$seasonStartDay = 1;
		
		if(date('n') > $seasonStartMonth) {
			$y = date('Y')+1;
		}
		elseif(date('n') < $seasonStartMonth) {
			$y = date('Y');
		}
		elseif(date('j') < $seasonStartDay) {
			$y = date('Y');
		}
		else {
			$y = date('Y')+1;
		}
		
		return($y.'-'.str_pad($seasonStartMonth,2,'0',STR_PAD_LEFT).'-'.str_pad($seasonStartDay,2,'0',STR_PAD_LEFT));
	}

	/**
	 * Palauttaa seuraavan jäsenmaksukauden hinnaston.
	 * @return array Seuraavan jäsenmaksukauden hinnasto.
	 * @author Juha-Pekka Järvenpää
	 */
	function getNextSeasonPricings() {
		$query = "SELECT membership,seasons,price FROM pricings WHERE DATE(starts)='".$this->getNextSeasonStartDate()."' ORDER BY seasons";
		$results = $this->query($query);

		$pricings = array();

		foreach($results as $r) {
			$membership = $r['pricings']['membership'];
			$seasons = $r['pricings']['seasons'];
			$price = $r['pricings']['price'];
			
			$pricings[$seasons][$membership] = $price;
		}
		
		return $pricings;
	}

	/**
	 * Nollaa seuraavan jäsenmaksukauden hinnaston.
	 * @return boolean TRUE mikäli nollaus onnistui, muuten false.
	 * @author Juha-Pekka Järvenpää
	 */
	function deleteNextSeasonPricings() {
		$query = "DELETE FROM pricings WHERE DATE(starts)='".$this->getNextSeasonStartDate()."'";
		$this->query($query);
		return($this->affectedRows() > 0);
	}

}

/*
Local variables:
mode:php
c-file-style:"bsd"
End:
*/
?>
