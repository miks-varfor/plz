<?php 
App::import('Model', 'Registration');

class RegistrationTest extends Registration {
	var $name = 'Registration';
	var $useDbConfig = 'test_suite';
	var $hasMany = array();
	var $belongsTo = array();
}

class RegistrationTestCase extends CakeTestCase {
	var $fixtures = array( 'registration_test' );
	var $dbFormat = '%Y-%m-%d %R';
	
	function testSetPaid() {
		$this->RegistrationTest =& new RegistrationTest();
		$originallyPaid = $this->RegistrationTest->field('paid', 'id=1');
		$this->assertNull($originallyPaid);
		$paid = $this->RegistrationTest->setPaid(1);
		$nowPaid = $this->RegistrationTest->field('paid', 'id=1');
		$this->assertNotNull($nowPaid);
		$this->assertEqual(strftime('%Y-%m-%d %R:00', $paid), $nowPaid);
	}
	
}

?>
