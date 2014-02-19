<?php

class FormyHelper extends AppHelper {
	var $helpers = array('Html');
	
	function options($label, $extra = null) {
		if (!is_array($extra))
					{
			$extra = array();
		}
		$extra['after'] = "\n";
		$extra['label'] = $label;
		$extra['div'] = false;
		return $extra;
	}
	
	/**
	 * Luo yksinkertaisen html-napin, joka vie selaimen annettuun osoitteeseen.
	 * Formi käyttää oletusarvoisesti POST-metodia kutsuun, mutta se voidaan
	 * myös korvata GET-metodilla. Tätä ei kuitenkaan suositella.
	 * Form-elementillä on luokka 'button_to', jolla siitä voi tehdä esimerkiksi
	 * inline-tyyppisen.
	 *
	 * @param string $name napin näkyvä nimike
	 * @param string $url sisäinen osoite tiettyyn actioniin
	 * @param string $method kutsun http-metodi, jonka oletusarvona POST
	 * @return tulostaa html-lomakkeen, jossa yksi nappi.
	 * @author Juhani Markkula
	 */
	function buttonTo($name, $url, $method = 'post', $buttonclass = '') {
		echo '<form method="'.$method.'" action="'.$this->Html->url($url).'" class="button_to">';
		echo '<button type="submit" class="'.$buttonclass.'">'.$name.'</button>';
		echo '</form>';
	}
	
	
	
}	
?>
