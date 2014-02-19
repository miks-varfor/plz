<?php
/**
 * views/users/add.ctp
 * Käyttäjien lisäyksen näkymä. Näkyy kirjautumattomille otsikolla
 * "Liity jäseneksi" ja kirjautuneille virkailijoista ylöspäin otsikolla
 * "Lisää käyttäjä".
 *
 * Pakolliset parametrit:
 * @param boolean $user_logged onko käyttäjä kirjautunut
 *
 * Valinnaiset parametrit:
 * @param string $errorMessage sivun alussa näytettävä virheilmoitus
 * @param array $mailing_lists sähköpostilistat, joille käyttäjä voi liittyä,
 * CakePHP:n antamassa muodossa, kun hakee taulun groups sisällön
 *
 * @author Niko Kiirala
 * @package kurre
 * @license GNU General Public License v2
 */

echo $form->create('User', array('type' => 'post', 'action' => 'add'));

/**
 * Oletusparametrit syötekentille. Ei aseta parametreja mitenkään
 * automaattisesti, vaan tätä kutsutaan jokaisen syötekentän luonnin
 * yhteydessä.
 * @param string $label syötekentän otsikko
 * @param array $extra lisäparametrit syötekentälle
 * @return array $form->input:lle annettavat parametrit
 * @author Niko Kiirala
 */
function options($label, $extra = null) {
	if (!is_array($extra))
        {
		$extra = array();
	}
	$extra['before'] = "<tr>\n<td>";  
	$extra['between'] = "</td><td>"; 
	$extra['after'] = "</td>\n</tr>\n";
	$extra['label'] = $label;
	$extra['div'] = false;
	return $extra;
}

/* Asetataan sopiva otsikko sen perusteella, onko käyttäjä kirjautunut */
if ($user_logged) {
  echo "<h1>Lisää käyttäjä</h1>\n";
}
else {
  echo "<h1>Liity jäseneksi</h1>\n";
  echo "</p>\n";
  echo "<p>Jäsenmaksun suuruus: 1 vuosi 2,00 EUR.</p>\n";
}

/* Näytetään mahdollinen virheilmoitus */
if (isset($errorMessage)) {
	echo '<p><strong>'.htmlspecialchars($errorMessage)."</strong></p>\n";
}

/* Näytetään varsinainen lomake */
?>

<p>Tähdellä (*) merkityt kentät ovat pakollisia.</p>
  <fieldset>
 
    <legend>Henkilötiedot</legend>
    <table>
<?php
echo $form->input('username', options('Käyttäjätunnus: *',
				      array('size' => '50')));
echo "<tr><td colspan='2'>Tällä\n";
echo "tunnuksella kirjaudut myöhemmin sisään tähän järjestelmään.</td></tr>\n";
echo $form->input('firstname', options('Etunimet: *',
					 array('size' => '50')));
echo $form->input('call_name', options('Kutsumanimi: *',
				       array('size' => '50')));
echo $form->input('lastname', options('Sukunimi: *',
				       array('size' => '50')));

/* Järjestelmän sisäinen muoto nimien tallentamiseen on eri kuin tässä
 * lomakkeella, joten virheilmoitukset pitää näyttää erikseen */
$name_error = $form->error('name', 'Kirjoita etunimesi ja sukunimesi');
if ($name_error) {
  echo $name_error;
  echo $form->error('screen_name', 'Kirjoita kutsumanimesi');
}
else {
  echo $form->error('screen_name', 'Kirjoita kutsumanimesi ja sukunimesi');
}

echo $form->input('email', options('Sähköpostiosoite: *',
				   array('size' => '50')));

echo $form->input('residence', options('Kotikunta: *',
				       array('size' => '50')));
echo $form->input('phone', options('Puhelinnumero:',
				   array('size' => '50')));
echo $form->input('faculty', options('Tiedekunta:',
			       array('size' => '50')));

/* BUGBUG: CakePHP:n pitäisi dokumentaationsa mukaan tehdä tämä
 * automaattisesti, kun asettaa $form->input:lle parametrin 'empty' => true */
unset($form->data['User']['password1']);
unset($form->data['User']['password2']);

echo $form->input('password1',
		  options('Salasana: *',
			  array('type' => 'password', 'empty' => true)));
echo $form->input('password2',
		  options('Salasana uudelleen: *',
			  array('type' => 'password', 'empty' => true)));
/* Jos salasanat eivät olleet samat, kontrolleri ei luo hajautusarvoa
 * salasanalle */
echo $form->error('hashed_password', 'Antamasi salasanat eivät olleet samat');
?>
    </table>
  </fieldset>
  <br/>
  <fieldset>
    <legend>Muut tiedot</legend>
	<table>
    <?= $form->input('hyy_member', options('Olen HYY:n jäsen.')) ?>
	</table>
<?php
/* Jos järjestelmässä on sähköpostilistoja, annetaan käyttäjän valita niistä
 * haluamansa. */
if (!empty($mailing_lists)) {
?>
<table>
<p>Liity sähköpostilistoille</p>
    <?php
	foreach ($mailing_lists as $info) {
		$list_id = 'list_' . $info['Group']['id'];
		$description = $info['Group']['description'];
		echo $form->input($list_id,
				  options($description,
					  array('type' => 'checkbox')));
	}
	echo "</table>\n";
} /* end if (!empty($mailing_lists)) */
    ?>

  </fieldset>
<p><input type="submit" value="Tallenna"></input>

<?php echo $html->link('Rekisteriseloste', '/pages/register_description'); ?>

</p>
<?php
echo $form->end();

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
