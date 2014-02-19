<?php
/**
* event_type.php
*
* @author Lauri Auvinen
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka tapahtumatyypeille
*
* @author Lauri Auvinen
* @package Kurre
*/
class EventType extends AppModel {
	var $name = 'EventType';

	/**
	* Lisää tapahtumatyypin tapahtumaa luodessa, jos sitä ei löydy tietokannasta
	* @param string $name tapahtumatyyppi
	* @return bool onnistuiko tapahtumatyypin lisääminen tietokantaan
	* @author Lauri Auvinen
	*/
	function add($name) {
		return $this->save(aa('name',$name));
	}
}
?>