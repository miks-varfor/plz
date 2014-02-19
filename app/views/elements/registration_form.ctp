<? $prefix = $avec ? 'Avec.' : ''; ?>

			<? if($showPublicReg && !$avec): ?>
				<strong style="color: #c00000;">
				Mikäli sinulla on käyttäjätunnus<br />
				järjestelmään, kirjauduthan ensin<br />
				sisään. Alla oleva lomake on<br />
				rekisteröitymättömille käyttäjille. </strong><br /><br />
			<? endif; ?>
				

			<? if(($showPublicReg || count($event['CustomField']) > 0) && !$avec){ ?>
				<strong>Omat tiedot</strong><br /><br />
			<? } ?>

			<? if($showPublicReg && !$avec): ?>
				<?= $form->label($prefix.'Registration.name', '* Nimi:') ?><br />
				<?= $form->input($prefix.'Registration.name', aa('label', false)) ?><br />
				<?= $form->label($prefix.'Registration.email', '* Sähköposti:') ?><br />
				<?= $form->input($prefix.'Registration.email', aa('label', false)) ?><br />
				<?= $form->label($prefix.'Registration.phone', 'Puhelin:') ?><br />
				<?= $form->input($prefix.'Registration.phone', aa('label', false)) ?><br />
			<? elseif($avec): ?>
				<strong>Avecin tiedot</strong><br /><br />
                                <?= $form->label($prefix.'Registration.name', '* Nimi:') ?><br />
                                <?= $form->input($prefix.'Registration.name', aa('label', false)) ?><br />
                                <?= $form->label($prefix.'Registration.email', 'Sähköposti:') ?><br />
                                <?= $form->input($prefix.'Registration.email', aa('label', false)) ?><br />
			<? else: ?>
				<?= $form->hidden('Registration.name', aa('value', $currentUser['screen_name'])) ?>
				<?= $form->hidden('Registration.email', aa('value', $currentUser['email'])) ?>
				<?= $form->hidden('Registration.phone', aa('value', $currentUser['phone']))?>
			<? endif; ?>
			
			<!-- Lisätietokentät -->
			<? 
				foreach($event['CustomField'] as $field) {
					$fieldName = $prefix.'CustomField.field_'.$field['id'];
					$fieldTitle = $field['name'];
					
					?>
					<div class="customField" style="margin: 10px 0;">
					<?
					echo $form->label($fieldName, $fieldTitle.':<br />');
					
					switch($field['type']) {
						case 'text':
							echo $form->text($fieldName) . '<br />';
							break;
						case 'textarea':
							echo $form->textarea($fieldName) . '<br />';
							break;
						case 'radio':
							$choises = a();
							foreach(split(';', $field['options']) as $choise)
								$choises[$choise] = $choise;
							echo $form->radio($fieldName, $choises, aa('legend', false)) . '<br />';
							break;
						case 'checkbox':
							$choises = split(';', $field['options']);
							$madeChoises = a();
							if($avec && isset($this->data['Avec']['CustomField']['field_'.$field['id']])) {
								$madeChoises = split(';', $this->data['Avec']['CustomField']['field_'.$field['id']]);
							}
							else if(!$avec && isset($this->data['CustomField']['field_'.$field['id']])) {
								$madeChoises = split(';', $this->data['CustomField']['field_'.$field['id']]);
							}
							$checked = 'checked="checked"';
							$fieldName = 'data'.($avec ? '[Avec]' : '').'[CustomField][field_'.$field['id'].'][]';
							?>
							<input type="hidden" name="<?= $fieldName ?>" />
							<?
							foreach($choises as $choise) {
								$isChecked = in_array($choise, $madeChoises) ? $checked : '';;
							?>
								<input type="checkbox" <?= $isChecked ?> name="<?= $fieldName ?>" value="<?= $choise ?>"/> <?= $choise ?><br />
							<? }
							break;
						default:
							echo "Virheellinen kenttä<br />";
							break;
					}
					?>
					</div>
					<?
				} 
			?>

