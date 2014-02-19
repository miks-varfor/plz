<?php
App::import('Controller', 'App');

class AppControllerTest extends CakeTestCase {

	function testParseDate() {
		$ac =& new AppController();

		/* Vuoden ensimmäinen päivä */
		$ret = $ac->parseDateTime('1.1.2008');
		$this->assertEqual($ret, '2008-01-01 00:00:00');

		$ret = $ac->parseDateTime('1.1.2008 00:00');
		$this->assertEqual($ret, '2008-01-01 00:00:00');

		$ret = $ac->parseDateTime('1.1.2008 00:00:00');
		$this->assertEqual($ret, '2008-01-01 00:00:00');

		$ret = $ac->parseDateTime('1.1.2008 00.00.00');
		$this->assertEqual($ret, '2008-01-01 00:00:00');

		/* Alkupäivän ilta */
		$ret = $ac->parseDateTime('1.1.2008 23:59');
		$this->assertEqual($ret, '2008-01-01 23:59:00');

		$ret = $ac->parseDateTime('1.1.2008 23:59:59');
		$this->assertEqual($ret, '2008-01-01 23:59:59');

		$ret = $ac->parseDateTime('1.1.2008 23.59');
		$this->assertEqual($ret, '2008-01-01 23:59:00');

		$ret = $ac->parseDateTime('1.1.2008 23.59.59');
		$this->assertEqual($ret, '2008-01-01 23:59:59');

		/* Vuoden viimeinen päivä */
		$ret = $ac->parseDateTime('31.12.2008');
		$this->assertEqual($ret, '2008-12-31 00:00:00');

		/* Oletuskellonajat */
		$ret = $ac->parseDateTime('31.12.2008', 'start');
		$this->assertEqual($ret, '2008-12-31 00:00:00');

		$ret = $ac->parseDateTime('31.12.2008', 'end');
		$this->assertEqual($ret, '2008-12-31 23:59:59');

		$ret = $ac->parseDateTime('31.12.2008', 'mid');
		$this->assertEqual($ret, '2008-12-31 12:00:00');

		/* Oletusvuosi */
		$year = date('Y');

		$ret = $ac->parseDateTime('1.1.');
		$this->assertEqual($ret, $year . '-01-01 00:00:00');

		$ret = $ac->parseDateTime('31.12.');
		$this->assertEqual($ret, $year . '-12-31 00:00:00');

		$ret = $ac->parseDateTime('12.3. 10:23');
		$this->assertEqual($ret, $year . '-03-12 10:23:00');

		$ret = $ac->parseDateTime('12.3. 10:23:45');
		$this->assertEqual($ret, $year . '-03-12 10:23:45');

		/* Epäkelpoja syötteitä */
		$ret = $ac->parseDateTime('');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('asdf');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('32.1.');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('1-1-2008');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('30.2.2008');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('29.2.2009');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('31.6.2008');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('0.2.2008');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('4.5.2008 3');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('5.6.20.08');
		$this->assertEqual($ret, '');

		$ret = $ac->parseDateTime('5.6.2008 1.2.3.4');
		$this->assertEqual($ret, '');
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
