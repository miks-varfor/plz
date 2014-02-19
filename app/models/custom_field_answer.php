<?php
/**
* custom_field_answer.php
*
* @author Juhani Markkula, Samu Kytöjoki
* @package Kurre
* @version 1.0
* @license GNU General Public License v2
*/

/**
* Malliluokka lisätietokenttien vastauksille
*
* @author Juhani Markkula, Samu Kytöjoki
* @package Kurre
*/
class CustomFieldAnswer extends AppModel {
	var $name = 'CustomFieldAnswer';
	var $order = "CustomFieldAnswer.custom_field_id ASC";
	
	var $belongsTo = array(
		'CustomField',
		'Registration'
	);
	
	/**
	* Tallentaa yhden lisätietokentän vastauksen tai muokkaa
	* olemassa olevaa vastausta
	* Ei tarkista onko varsinaista ilmoittautumista olemassa
	*
	* @param int $registration ilmoittautumisen id-numero
	* @param int @field lisätietokentän id-numero
	* @param string $value uusi arvo
	* @return arvon 0 jos tallennus onnistui
	*         arvon 1 jos lisätietokentän arvo on virheellinen
	*         arvon 2 jos lisätietokenttää ei löydy
	* @author Samu Kytöjoki
	*/	
	function saveAnswer($registration, $field, $value) {

		// Tarkista löytyykö kenttä
		if ($field = $this->CustomField->findById($field)) {
		
			// Tarkista onko käyttäjä jo antanut tämän tiedon
			if (($data = $this->findByRegistrationIdAndCustomFieldId(
				$registration, $field['CustomField']['id'])) == null) {

				// Aseta tiedot tallentamista varten
				$data['CustomFieldAnswer']['id'] = null;
				$data['CustomFieldAnswer']['registration_id'] = $registration;
				$data['CustomFieldAnswer']['custom_field_id'] = $field;
			}

			// Tarkista onko uusi arvo sallittu vastaus
			$valueOk = false;
			if ($field['CustomField']['type'] == 'checkbox' &&
				($value == '1' || $value == '0')) {
				$valueOk = true;
			}
			if ($field['CustomField']['type'] == 'radio' &&
				in_array($value, explode(';', $field['CustomField']['options']))) {
				$valueOk = true;
			}
			if ($field['CustomField']['type'] == 'text' ||
				$field['CustomField']['type'] == 'textarea') {
				$valueOk = true;
			}

			// Tallenna lisätietokentän vastaus
			if ($valueOk) {
				$data['CustomFieldAnswer']['value'] = $value;
				$this->save($data);
				return 0;
			} else {
				return 1;
			}
		} else {
			return 2;
		}
	}
	
	/**
	 * Tallentaa kaikki lisätietokenttien vastaukset annetusta datasta
	 * 
	 * @param array $data lomakkeelta saatu ilmoittautumisdata
	 * @param array $customFields tapahtuman lisätietokentät
	 * @param int $registrationId ilmoittautumisen tunniste
	 * @author Juhani Markkula
	 */
	function saveAllAnswers($data, $customFields, $registrationId) {
		foreach($customFields as $field) {
			$htmlField = 'field_'.$field['id'];
			$value = 'error';
			$answerData = array();
			
			if(in_array($htmlField, array_keys($data['CustomField']))) {
				$dataField = $data['CustomField']['field_'.$field['id']];
				if($field['type'] == 'checkbox')
					$value = count($dataField) > 1 ? substr(join(';', $dataField), 1) : '';
				else
					$value = $dataField;
			}
			
			$answerData['CustomFieldAnswer'] = array(
				'custom_field_id' => $field['id'],
				'registration_id' => $registrationId,
				'value' => $value
			);
			
			$this->id = null;
			$this->save($answerData);
		} 
	}
	
	/**
	 * Poista kaikki lisätietokenttien vastaukset annetusta ilmoittautumisesta.
	 * Tällä nollataan vastaukset ennen uusien vastausten tallentamista.
	 * 
	 * @param array $registration ilmoittautumisdatat
	 * @author Juhani Markkula
	 */
	function deleteOldAnswers($registration) {
		foreach($registration['CustomFieldAnswer'] as $answer) {
			$this->del($answer['id']);
		}
	}	
}
?>
