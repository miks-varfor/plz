<?php
/**
* registration.php
*
* @author Juhani Markkula
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka tapahtumailmoittautumisille
*
* @author Juhani Markkula
* @package Kurre
*/
class Registration extends AppModel {
	var $name = 'Registration';
	
	var $hasMany = array(
		'CustomFieldAnswer' => array(
			'dependent' => true,
			'order' => 'CustomFieldAnswer.custom_field_id ASC'
		)
	);
	var $belongsTo = array(
		//'User',
		//'CalendarEvent',
		'Avec' => array('className' => 'Registration', 'foreignKey' => 'avec_id')
	);
	
	var $validate = array(
		'name' => array('rule' => array('minLength', '1'),
				'required' => true,
				'message' => 'Anna koko nimesi'),
		'email' => array('rule' => 'email',
				'required' => true,
				'message' => 'Anna sähköpostiosoitteesi')
		);
	/**
	* Asettaa ilmoittautumisen maksetuksi.
	* @param int $id ilmottautumisen tunniste
	* @return int maksun ajanhetki
	*/
	function setPaid($id) {
		$this->id = $id;
		$time = time();
		$this->saveField('paid', strftime('%Y-%m-%d %R', $time), false);
		return $time;
	}

	/** 
	* Hakee kaikki tapahtumaan ilmoittautuneet ilmoittautumisjärjestyksessä
	* @param int $id ilmoittautumisen tunniste
	* @return array Taulukko tapahtumaan ilmoittautuneista
	* @author Sampsa Lappalainen
	*/
	function listParticipants($id) {
		$query = "
			SELECT 
				user.name as name,
				avec.name as avecname,
				userdata.member_number as member_number 
			FROM registrations as user 
			LEFT JOIN users as userdata on userdata.id = user.user_id
			LEFT JOIN registrations as avec on avec.avec_id = user.id 
			WHERE user.id not in 
				(select id from registrations where avec_id is not null) 
			AND user.calendar_event_id=". $id; 
		$query .= " ORDER by user.created";
		return $this->query($query);
	}
}
?>
