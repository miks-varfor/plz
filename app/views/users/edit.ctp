<?php
/**
 * views/users/edit.ctp
 * Käyttäjien muokkauksen näkymä. Kaikki kirjautuneet käyttäjät pääsevät
 * muokkaamaan ja katsomaan omia tietojaan, silloin tämä näkyy otsikolla
 * "Muokkaa omia tietoja". Virkailijasta ylöspäin käyttäjät voivat myös
 * katsoa tai muokata muiden tietoja, jolloin tämä näkyy nimellä
 * "Muokkaa käyttäjän tietoja".
 *
 * Pakolliset parametrit:
 * @param string $page_title sivun otsikko
 * @param mixed $modify lista niiden kenttien nimistä, joita käyttäjä saa
 * muokata tai false, jos käyttäjä ei saa edes nähdä asiakkaan tietoja
 *
 * Valinnaiset parametrit:
 * @param string $errorMessage sivun alussa näytettävä virheilmoitus
 * @param array $mailing_lists sähköpostilistat, joille käyttäjä voi liittyä,
 * CakePHP:n antamassa muodossa, kun hakee taulun groups sisällön
 * @param array $pricings asiakkaalle mahdolliset maksettavat
 * jäsenyysjaksot, cakePHP:n antamassa muodossa taulusta pricings
 * @param mixed $payment asiakkaan jäsenmaksun status, false tarkoittaa,
 * ettei asiakas ole maksanut jäsenmaksua kuluvalle kaudelle, jolloin
 * asiakkaalle on mahdollista kirjata jäsenmaksu käteisellä.
 *
 * @author Niko Kiirala
 * @package kurre
 * @license GNU General Public License v2
 */

/**
 * Oletusparametrit syötekentille. Ei aseta parametreja mitenkään
 * automaattisesti, vaan tätä kutsutaan jokaisen syötekentän luonnin
 * yhteydessä.
 * @param string $label syötekentän otsikko
 * @param array $extra lisäparametrit syötekentälle
 * @return array $form->input:lle annettavat parametrit
 * @author Niko Kiirala
 */
function options($label, $extra = null)
{
	if (!is_array($extra))
	{
		$extra = array();
	}
	$extra['before'] = "<tr>\n    <td>";  
	$extra['between'] = "</td>\n    <td>";  
	$extra['after'] = "</td>\n</tr>\n";
	$extra['label'] = $label;
	$extra['div'] = false;
	return $extra;
}

/**
 * Luo kentän asiakkaan tiedoista. Jos nykyisellä käyttäjällä
 * on oikeudet muokata asiakkaan kyseistä tietoa, näytetään muokattava
 * kenttä, muutoin näytetään kentän sisältö tekstinä.
 *
 * @param array $setup vähän myöhemmin koodissa luotava taulukko, jolla
 * välitetään tälle funktiolle tiedot muokattavista kentistä ja
 * FormHelper-oliosta
 * @param string $name järjestelmän käyttämä nimi kentästä, usein sama
 * kuin tietokannan vastaavan kentän nimi
 * @param string $label käyttäjälle näytettävä kentän nimi
 * @param array $options mahdolliset $form->input:lle annettavat lisäasetukset
 * @return string kentän luova HTML-koodipätkä
 * @author Niko Kiirala
 */
function form_input($setup, $name, $label, $options = null)
{
	$out = '';
	$modify =& $setup['modify'];
	$form =& $setup['form'];

	if (isset($modify) && $modify !== false)
	{
		if (array_search($name, $modify) !== false)
		{
			$out .= $form->input($name, options($label, $options));
		}
		else
		{
			$out .= "<tr>\n    <td>";
			$out .= htmlspecialchars($label);
			$out .= "</td>\n    <td>";
			if (isset($options['value'])) {
			  $out .= htmlspecialchars($options['value']);
			}
			else if (isset($options['options'])) {
			  $db_text = $form->data['User'][$name];
			  $out .= htmlspecialchars($options['options'][$db_text]);
			}
			else {
			  $out .= htmlspecialchars($form->data['User'][$name]);
			}
			$out .= "</td>\n</tr>\n";
		}
	}
	return $out;
}

/* Sivun varsinainen sisältö alkaa */
echo '<h1>' . htmlspecialchars($page_title) . "</h1>\n";

/* Näytetään mahdollinen virhailmoitus */
if (isset($errorMessage)) {
	echo "<p><strong>".htmlspecialchars($errorMessage)."</strong></p>\n";
}

/* $modify === false tarkoittaa, ettei käyttäjällä ole mitään oikeuksia
 * katsella tai muokata tämänhetkisen asiakkaan tietoja */
if ($modify !== false) {
	echo $form->create('User', array('type' => 'post', 'action' => 'edit'));
	$setup = array('modify' => &$modify, 'form' => &$form);
?>

<fieldset>
	<legend>Henkilötiedot</legend>
	<table id="user_personal_info">
<?php
	 echo form_input($setup, 'username', 'Käyttäjätunnus:');
	echo form_input($setup, 'member_number', 'Jäsennumero:');
    echo form_input($setup, 'firstname', 'Etunimet:');
    echo form_input($setup, 'lastname', 'Sukunimi:');
    echo form_input($setup, 'screen_name', 'Kutsumanimi:');

	/* Jos kontrollerista on annettu asiakkaan LDAP-tiedot, näytetään niitä */
    if(isset($form->data['ldap']) && !empty($form->data['ldap'])) {
        echo "<tr>\n";
		echo '    <td>Nimi (LDAP)</td><td>';
		echo htmlspecialchars($form->data['ldap']['name']);
		echo "</td>\n";
		echo "</tr>\n";
        echo "<tr>\n";
		echo '    <td>Sähköpostiosoite (LDAP)</td><td>';
		echo htmlspecialchars($form->data['ldap']['email']);
		echo "</td>\n";
		echo "</tr>\n";
        echo "<tr>\n";
		echo '    <td>Asema (LDAP)</td><td>';
		echo htmlspecialchars($form->data['ldap']['title']) . ', ';
		echo htmlspecialchars($form->data['ldap']['department']);
		echo "</td>\n";
		echo "</tr>\n";	
    }

    echo form_input($setup, 'email', 'Sähköpostiosoite:');
    echo form_input($setup, 'residence', 'Kotikunta:');
    echo form_input($setup, 'phone', 'Puhelinnumero:');
    echo form_input($setup, 'faculty', 'Tiedekunta:');
    echo form_input($setup, 'role', 'Käyttäjärooli:',
					array('options' =>
						  array('kayttaja' => 'Käyttäjä',
								'virkailija' => 'Virkailija',
								'jasenvirkailija' => 'Jäsenvirkailija',
								'yllapitaja' => 'Ylläpitäjä'),
						  'selected' => $form->data['User']['role']));
    echo form_input($setup, 'membership', 'Jäsentyyppi:',
					array('options' =>
						  array('ei-jasen' => 'Ei jäsen',
								'erotettu' => 'Erotettu',
								'ulkojasen' => 'Ulkojäsen',
								'jasen' => 'Jäsen',
								'kannatusjasen' => 'Kannatusjäsen',
								'kunniajasen' => 'Kunniajäsen'),
						  'selected' => $form->data['User']['membership']));
    echo form_input($setup, 'created', 'Liittymispäivä:',
					array('value' => $format->date($form->data['User']['created']),
						  'type' => 'text'));

    if (array_search('password', $modify) !== false) {
		/* BUGBUG: CakePHP:n pitäisi dokumentaationsa mukaan tehdä tämä
		 * automaattisesti, kun asettaa $form->input:lle parametrin
		 * 'empty' => true */
		unset($form->data['User']['password1']);
		unset($form->data['User']['password2']);
        echo $form->input('password1', options('Salasana:',
											   array('type' => 'password',
													 'empty' => true)));
        echo $form->input('password2', options('Salasana uudelleen:',
											   array('type' => 'password',
													 'empty' => true)));
    }
    echo $form->error('hashed_password', 'Antamasi salasanat eivät olleet samat');
?>

    </table>
  </fieldset>
  <br/>
  <fieldset>
    <legend>Muut tiedot</legend>
	<table>
<?php
	$disabled = (array_search('hyy_member', $modify) === false);
	echo $form->input('hyy_member',
					  options('Olen HYY:n jäsen',
							  array('disabled' => $disabled)));

	echo "</table>\n";

	/* Näytetään sähköpostilistat, jos niitä järjestelmästä löytyy */
	if (!empty($mailing_lists)) {
		echo "<p>Liity sähköpostilistoille</p>\n";
		echo "<table>\n";
		
		$disabled = (array_search('groups', $modify) === false);

		foreach ($mailing_lists as $info) {
			$list_id = "list_" . $info['Group']['id'];
			$description = $info['Group']['description'];
			$selected = false;
			foreach ($form->data['Group'] as $group) {
				if (isset($group['id'])
					&& $group['id'] == $info['Group']['id']) {
					$selected = true;
				}
			}
			echo $form->input($list_id, options($description, array('type' => 'checkbox', 'value' => $selected, 'disabled' => $disabled)));
		}
		echo "	</table>\n";
	}

	echo "</fieldset>\n";

	/* screen_name: sitä voivat muokata kaikki, jotka voivat muokata
	 * ylipäätään jotain */
    if (array_search('screen_name', $modify) !== false) {
        echo "<p><input type=\"submit\" value=\"Tallenna\"> ";
		echo $html->link('Rekisteriseloste', '/pages/register_description');
		echo "</p>\n";
    }
    echo $form->end();

	/* Jos käyttäjä saa kirjata käteismaksuja, näytetään lomake sitä varten */
    if (array_search('add_payment', $modify) !== false) {
		echo "<fieldset>\n";
		echo "<legend>Kirjaa käteisellä maksettu jäsenmaksu</legend>\n";
		if (!empty($pricings) && $payment === false) {
			$action = 'payByCash/' . $form->data['User']['id'];
			echo $form->create('Payment',
							   array('type' => 'post',
									 'action' => $action));
			echo "<table id=\"register_cash_payment\">\n";
			
			$pricing_list = array();
			foreach ($pricings as $price) {
				$seasons = $price['Pricing']['seasons'];
				$vuotta = ($seasons == 1 ? 'vuosi' : 'vuotta');
				$pricing_list[$seasons] = $seasons . ' ' . $vuotta . ', '
					. $price['Pricing']['price'] . ' euroa';
			}
			
			echo $form->input('seasons',
							  options('Valitse maksettu jäsenyysjakso',
									  array('options' => $pricing_list)));
			if (array_search('set_payment_date', $modify) !== false) {
				echo $form->input('paid', options('Maksupäivä', array('dateFormat' => 'DMY')));
			}

			echo "</table>\n";
			echo "<input type='submit' value='Kirjaa jäsenmaksu' />\n";
			echo $form->end();
		}
		else if ($payment !== false) {
			if (!empty($payment['Payment']['paid'])) {
				echo "<p>Käyttäjä on maksanut jäsenmaksun " . $format->date($payment['Payment']['valid_until']) . " asti.</p>\n";
			}
			else {
				echo "<p>Käyttäjällä on luotuna tilisiirrolla maksettava jäsenmaksulasku, jota ei ole kirjattu maksetuksi.</p>\n";
				echo "<table>\n";
				echo "<tr>\n";
				echo "  <td>Lasku luotu</td><td>" .  $format->date($payment['Payment']['created']) . "</td>\n";
				echo "</tr>\n<tr>\n";
				echo "  <td>Maksettavan kauden loppu</td><td>" . $format->date($payment['Payment']['valid_until']) . "</td>\n";
				echo "</tr>\n<tr>\n";
				echo "  <td>Summa</td><td>" . $payment['Payment']['amount'] . " &euro;</td>\n";
				echo "</tr>\n";
				echo "</table>\n\n";

				echo $form->create('Payment', array('type' => 'post', 'action' => 'payByCash/' . $form->data['User']['id']));
				echo $form->hidden('id', aa('value', $payment['Payment']['id']));
				echo "\n<input type='submit' value='Kirjaa lasku maksetuksi käteisellä' />\n";
				echo $form->end();
			}
		}
		else {
			echo "<p>Mahdollisia maksettavia jäsenkausia ei löytynyt.</p>\n";
		}
		echo "</fieldset>\n";
		
		// Mahdollisuus kirjata tilisiirto, vain ylläpitäjä
		if (!empty($pricings) && $payment === false && array_search('set_payment_date', $modify) !== false) {
			echo "<fieldset style='margin-top: 10px;'>\n";
			echo "<legend>Kirjaa tilisiirrolla maksettu jäsenmaksu</legend>\n";
			echo "<p><b>Huom! Maksu kirjautuu välittömästi maksetuksi. Käytä vain jos maksu on jo maksettu tilille.</b></p>\n";
			$action = 'createPaidBankTransfer/' . $form->data['User']['id'];
			echo $form->create('Payment',
							   array('type' => 'post',
									 'action' => $action));
			echo "<table id=\"register_cash_payment\">\n";
			
			$pricing_list = array();
			foreach ($pricings as $price) {
				$seasons = $price['Pricing']['seasons'];
				$vuotta = ($seasons == 1 ? 'vuosi' : 'vuotta');
				$pricing_list[$seasons] = $seasons . ' ' . $vuotta . ', '
					. $price['Pricing']['price'] . ' euroa';
			}
			
			echo $form->input('seasons',
							  options('Valitse maksettu jäsenyysjakso',
									  array('options' => $pricing_list)));
			if (array_search('set_payment_date', $modify) !== false) {
				echo $form->input('paid', options('Maksupäivä', array('dateFormat' => 'DMY')));
			}

			echo "</table>\n";
			echo "<input type='submit' value='Kirjaa jäsenmaksu' />\n";
			echo $form->end();
			echo "</fieldset>\n";
		}
		
    } /* end if (array_search('add_payment', $modify) !== false) */
} /* end if ($modify !== false) */

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
