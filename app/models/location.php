<?php
/**
* location.php
*
* @author Lauri Auvinen
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka vakiopaikalle
*
* @author Lauri Auvinen
* @package Kurre
*/
class Location extends AppModel {
	var $name = 'Location';

	/**
	* Lisää vakiopaikan tapahtumaa luodessa, jos sitä ei löydy tietokannasta
	* @param string $name tapahtumapaikka
	* @return bool onnistuko tapahtumapaikan lisääminen
	* @author Lauri Auvinen
	*/
	function add($name) { 
		return $this->save(aa('name',$name));
	}

	/**
	* Lisää vakiopaikan karttalinkin uutta vakiopaikkaa luodessa
	* @param string $name tapahtumatyyppi
	* @return bool onnistuiko karttalinkin tallentaminen
	* @author Lauri Auvinen
	*/
	function setMaplink($map) {
		return $this->save(aa('map',$map));
	}
}
?>