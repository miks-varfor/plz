<?php
/**
* custom_field.php
*
* @author Juhani Markkula
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka lisätietokentille
*
* @author Juhani Markkula
* @package Kurre
*/
class CustomField extends AppModel {
	var $name = 'CustomField';
	
	var $hasMany = 'CustomFieldAnswer';
	var $belongsTo = 'CalendarEvent';
}

?>
