<?php
/**
* group.php
*
* @author Projektiryhmä
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka postituslistalle
*
* @author Projektiryhmä
* @package Kurre
*/
class Group extends AppModel {
	var $name = 'Group';
	
	var $hasAndBelongsToMany = 'User';
}

?>
