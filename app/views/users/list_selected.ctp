<?php
/**
 * views/users/list_selected.ctp
 * Käyttäjälistauksien näkymä. Näyttää listan annettujen käyttäjien annetuista
 * kentistä HTML- tai tekstimuodossa. HTML-muodossa näyttää myös lomakkeen,
 * jolta voi valita näytettävät kentät sekä mahdollisesti lomakkeen,
 * jolta voi valita haluamansa käyttäjät ja suorittaa näille jonkin
 * toiminnon.
 *
 * Pakolliset parametrit:
 * @param string $style haluttu esitysmuoto, 'normal' tai 'text'
 * @param string $query URLin kyselyosa, jolla on päästy tähän listaukseen,
 * esim. '/paid/nonmember'
 * @param array $fields näytettävät kentät, kukin muodossa Taulu.kenttä,
 * esim 'User.name'
 * @param string $listing_type listauksen otsikko, näytetään listauksen
 * alussa
 * @param array $results näytettävät käyttäjät, CakePHP:n
 * tietokantakyselyvastauksen muodossa tauluista users ja payments
 * 
 * Valinnaiset parametrit:
 * @param string $edit_link linkki valituille käyttäjille suoritettavan
 * toiminnon tekevälle sivulle
 * @param string $edit_text käyttälle näytettävä tekstimuotoinen kuvaus,
 * mitä valituille käyttäjille suoritettava toiminto tekee
 *
 * @author Niko Kiirala
 * @package kurre
 * @license GNU General Public License v2
 */

mb_internal_encoding('utf-8');

if ($style == 'normal') {
	/* HTML-tyylissä näytetään lomake, jolta voi valita näytettävät kentät */
	echo "<div style='float:right'>\n";

	echo $form->create('User',
					   array('type' => 'post',
							 'action' => 'listSelected' . $query));
	echo "<br/>";
	echo $form->input('fields',
					  array('options' =>
							array('User.username' => 'Käyttäjätunnus',
								  'User.firstname' => 'Etunimet',
								  'User.lastname' => 'Sukunimi',								
								  'User.screen_name' => 'Kutsumanimi',
								  'User.email' => 'Email',
								  'User.residence' => 'Kotipaikka',
								  'User.phone' => 'Puhelinnumero',
								  'User.hyy_member' => 'HYY:n jäsenyys',
								  'User.membership' => 'Jäsenyys',
								  'User.role' => 'Käyttäjätaso',
								  'User.created' => 'Liittymisaika',
								  'User.modified' => 'Muokkausaika',
								  'Payment.reference_number' => 'Jäsenmaksun viitenumero',
								  'Payment.amount' => 'Jäsenmaksun määrä',
								  'Payment.valid_until' => 'Jäsenkauden päättymispäivä',
								  'Payment.paid' => 'Jäsenmaksun maksupäivä',
								  'Payment.payment_type' => 'Jäsenmaksun maksutapa'
								  ),
							'multiple' => true,
							'label' => false,
							'selected' => $fields,
							'class' => 'list_selected'
							));
	echo "<input type='submit' name='data[normal]' value='Valitse kentät' />\n";
	echo "<input type='submit' name='data[text]' value='Näytä tekstinä' />\n";
	echo $form->end();
		?>
</div>
<p>Valitse näytettävät kentät listalta. Controlilla
(komento macissa) voit valita rivejä, jotka eivät ole alekkain.
Lopuksi paina Valitse kentät -nappia listan alapuolelta.</p>
<br clear='both' />

<?php
	  echo '<h1>' . htmlspecialchars($listing_type) . "</h1>\n";
} // end if ($style == 'normal')

/* Käyttäjälle näytettävät nimet kullekin kentälle */
$names = array('username' => 'Käyttäjätunnus',
			   'firstname' => 'Etunimet',
			   'lastname' => 'Sukunimi',
			   'screen_name' => 'Kutsumanimi',
			   'email' => 'Email',
			   'residence' => 'Kotipaikka',
			   'phone' => 'Puhelinnumero',
			   'hyy_member' => 'HYY:n jäsen',
			   'membership' => 'Jäsenyys',
			   'role' => 'Käyttäjätaso',
			   'created' => 'Liittymisaika',
			   'modified' => 'Muokkausaika',
			   
			   'reference_number' => 'Viitenumero',
			   'amount' => 'Määrä',
			   'valid_until' => 'Päättymispäivä',
			   'paid' => 'Maksupäivä',
			   'payment_type' => 'Maksutapa');

/* Tekstimuodossa näytetään joidenkin kenttien nimet lyhennettyinä */
if ($style == 'text') {
	$names['username'] = 'Tunnus';
	$names['hyy_member'] = 'HYY';
	$names['reference_number'] = 'Viite';
	$names['valid_until'] = 'Päättymisp';
	$names['paid'] = 'Maksup.';
}

/* Asetetaan muotoilufunktiot niille kentille, jotka halutaan näyttää
 * käyttäjäystävällisemmässä muodossa, kuin mitä kannasta saadaan suoraan */
$types = array('created' => array($format, 'dateTime'),
			   'modified' => array($format, 'dateTime'),
			   'valid_until' => array($format, 'date'),
			   'paid' => array($format, 'date'),
			   'hyy_member' => array($format, 'boolean'));

/* Taulukonluontifunktiot erikseen HTML- ja tekstityylille */
if ($style == 'normal') {
	/* HTML-tyyli */
	function table_start() {
		echo "<table id=\"user_list_admin\">\n";
	}
	
	function table_end() {
		echo "</table>\n";
	}
	
	function table_row_start() {
		echo "<tr>\n";
	}
	
	function table_row_end() {
		echo "</tr>\n";
	}
	
	function table_cell($name, $data) {
		if (empty($data)) {
			echo "    <td>&nbsp;</td>\n";
		}
		else if($name == 'email'){
			echo '<td class="email_address"><a href="mailto: '.htmlspecialchars($data).
			     '"><img src="/img/icons/email.png"" alt="'.htmlspecialchars($data).
			     '" title="'.htmlspecialchars($data).'" /></a></td>';
		}
		else {
			echo '    <td>' . htmlspecialchars($data) . "</td>\n";
		}
	}
	
	function table_cell_link($html, $name, $text, $target) {
		if (empty($data)) {
			$data = '&nbsp;';
		}
		echo '    <td>' . $html->link($text, $target) . "</td>\n";
	}
	
	function table_checkbox($form, $id) {
		echo '    <td>';
		echo $form->checkbox($id, array('value' => false));
		echo "</td>\n";
	}
	
	function table_heading($name = null, $data = null) {
		if (empty($data)) {
			echo "    <th>&nbsp;</th>\n";
		}
		else {
			echo '    <th>' . htmlspecialchars($data) . "</th>\n";
		}
	}
	
} else if ($style == 'text') {
	/* Tekstityyli */
	function print_len($text, $len) {
		if (mb_strlen($text) <= $len) {
			echo $text;
			for ($i = mb_strlen($text) ; $i < $len ; $i++) {
				echo ' ';
			}
		}
		else {
			echo mb_substr($text, 0, $len - 3);
			echo '...';
		}
		echo ' ';
	}

	function table_start() { }
	
	function table_end() { }
	
	function table_row_start() { }
	
	function table_row_end() {
		echo "\n";
	}
	
	function table_cell($name, $data) {
		/* Tekstimuodossa tulostetaan kukin kenttä vakiopituisena */
		static $field_len = array('username' => 10,
								  'firstname' => 25,
								  'lastname' => 20,
								  'screen_name' => 25,
								  'email' => 35,
								  'residence' => 10,
								  'phone' => 15,
								  'hyy_member' => 5,
								  'membership' => 10,
								  'role' => 8,
								  'created' => 16,
								  'modified' => 16,
								  
								  'reference_number' => 10,
								  'amount' => 6,
								  'valid_until' => 10,
								  'paid' => 10,
								  'payment_type' => 9
								  );
    
		if (isset($field_len[$name])) {
			$len = $field_len[$name];
		}		
		else {
			$len = 8;
		}

		print_len($data, $len);
	}

	function table_cell_link($html, $name, $text, $target) {
		table_cell($name, $text);
	}
	
	function table_checkbox($form, $id) { }
	
	function table_heading($name = null, $data = null) {
		if (!empty($data))
			table_cell($name, mb_strtoupper($data));
	}

	/* Tekstimuodossa ei ole mahdollista muokata käyttäjiä */
	unset($edit_link);
}

/* Luodaan lomake, jos valituille käyttäjille tehtävä toiminto on asetettu */
if (isset($edit_link)) {
	echo $form->create('User', array('type' => 'post',
									 'action' => $edit_link));
}

table_start();
table_row_start();

/* Luodaan otsikkorivi */
reset($results);
$first = current($results);
$link_field = null;

if (isset($edit_link)) {
	table_heading();
}

/* Ensin käsitellään käyttäjään itseensä liittyvät kentät */
if (isset($first['User'])) {
	foreach($first['User'] as $field_name => $field_data) {
		if ($field_name == 'id') {
			continue;
		}
		if ($link_field === null) {
			$link_field = $field_name;
		}
		if (isset($names[$field_name])) {
			table_heading($field_name, $names[$field_name]);
		}
		else {
			table_heading($field_name, $field_name);
		}
	}

	/* Valitaan, mistä kentästä tehdään linkki käyttäjän tietoihin */
	if (isset($first['User']['lastname'])) {
		$link_field = 'lastname';
	}
	else if (isset($first['User']['screen_name'])) {
		$link_field = 'screen_name';
	}
	else if (isset($first['User']['username'])) {
		$link_field = 'username';
	}
}

/* Jälkimmäiseksi käsitellään käyttäjän jäsenmaksuun liittyvät kentät */
if (isset($first['Payment'])) {
	foreach($first['Payment'] as $field_name => $field_data) {
		if ($field_name == 'id') {
			continue;
		}
		if (isset($names[$field_name])) {
			table_heading($field_name, $names[$field_name]);
		}
		else {
			table_heading($field_name, $field_name);
		}
	}
}

table_row_end();

/* Luodaan jokaiselle näytettävälle käyttäjälle oma taulukon rivi */
foreach($results as $user_data) {
	table_row_start();

	if (isset($edit_link)) {
		table_checkbox($form, $user_data['User']['id']);
	}
  
	$target = '/users/edit/' . $user_data['User']['id'];

	/* Ensin näytetään käyttäjän omat tiedot */
	if (isset($user_data['User'])) {
		foreach($user_data['User'] as $field_name => $field_data) {
			if ($field_name == 'id') {
				continue;
			}
			if (isset($types[$field_name])) {
				$field_data = call_user_func($types[$field_name], $field_data);
			}
			if ($field_name == $link_field) {
				table_cell_link($html, $field_name, $field_data, $target);
			}
			else {
				table_cell($field_name, $field_data);
			}
		}
	}
	
	/* Jälkimmäiseksi näytetään käyttäjän maksutiedot */
	if (isset($user_data['Payment'])) {
		foreach($user_data['Payment'] as $field_name => $field_data) {
			if ($field_name == 'id') {
				continue;
			}
			if (isset($types[$field_name])) {
				$field_data = call_user_func($types[$field_name], $field_data);
			}
			table_cell($field_name, $field_data);
		}
	}

	table_row_end();
}
table_end();

/* Jos oli mahdollisuus muokata käyttäjiä, laitetaan vielä submit-nappi
 * ja suljetaan lomake */
if (isset($edit_link)) {
  echo "<input type='submit' value='" . $edit_text . "' />\n";
  echo $form->end();
}

/* Tekstimuodossa näytetään myös listattujen käyttäjien yhteismäärä */
if ($style == 'text') {
  echo "\n";
  echo "Yhteensä " . count($results) . " riviä.\n";
} else {
  echo "<p> Yhteensä " . count($results) . " riviä.</p>\n";
}

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
