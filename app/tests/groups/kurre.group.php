<?php
class KurreGroupTest extends GroupTest {
  var $label = 'kurren testit';

  function KurreGroupTest() {
    TestManager::addTestCasesFromDirectory($this, MODEL_TESTS);
    TestManager::addTestFile($this, CONTROLLER_TESTS . 'app_controller');
  }
}

/*
Local variables:
mode:php
c-basic-offset:4
tab-width:4
End:
*/
?>
