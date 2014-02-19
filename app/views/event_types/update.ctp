<?php
/**
 * views/event_types/update.ctp
 * Näyttää tiedot tapahtumatyypin muokkauksesta
 *
 * Parametrit:
 * @param string $action tehty muokkaus, joko 'rename' tai 'delete'.
 * Jos toimintoa ei ole asetettu tai se ei ole jompi kumpi näistä,
 * tapahtumatyypin uusia tietoja ei näytetä.
 * @param array $location tapahtumatyypin uudet tiedot, CakePHP:n find-funktion
 * antamassa muodossa
 * @param string $errorMessage mahdollinen virheilmoitus
 *
 * @author Niko Kiirala
 * @package kurre
 * @license GNU General Public License v2
 */

/**
 * Tulostaa tapahtumatyypin tiedot.
 * @param array $location tapahtumatyypin tiedot
 * @author Niko Kiirala
 */
function print_type($type) {
  echo "<dl>\n";
  echo "    <dt>Nimi</dt>\n";
  echo '    <dd>'.htmlspecialchars($type['EventType']['name'])."</dd>\n";
  echo "</dl>\n";
}

if (isset($action)) {
  switch($action) {
  case 'rename':
    echo "<h1>Nimeä tapahtumatyyppi uudelleen</h1>\n";
    if (isset($event_type)) {
      echo "<p>Uudet tiedot:</p>\n";
      print_type($event_type);
    }
    break;
  case 'delete':
    echo "<h1>Poista tapahtumatyyppi</h1>\n";
    if (isset($event_type)) {
      echo "<p>Poistettu tyyppi:</p>\n";
      print_type($event_type);
    }
    break;
  }
}

if (isset($errorMessage)) {
  echo '<p><strong>' . htmlspecialchars($errorMessage) . "</strong></p>\n";
}

echo $html->link('Takaisin', '/calendar_events/manage');

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
