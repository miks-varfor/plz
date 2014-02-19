<?php 
App::import('Model', 'User');

class UserTest extends User {
	var $name = 'User';
	var $useDbConfig = 'test_suite';
	var $hasAndBelongsToMany = array();	
}

class UserTestCase extends CakeTestCase {
	var $fixtures = array( 'user_test' );
	
	function testCorrectLogin() {
		$this->UserTest =& new UserTest();

		$data = array('username' => 'matti', 'password' => 'kekbur42');
		$result = $this->UserTest->validateLogin($data);
		$this->assertFalse(empty($result));
		$this->assertEqual($result['name'], 'Matti Mainio Meikäläinen');
	}

	function testLoginWrongPassword() {
		$this->UserTest =& new UserTest();

		$data = array('username' => 'matti', 'password' => 'kekburX');
		$result = $this->UserTest->validateLogin($data);
		$this->assertFalse($result);
	}
	
	function testLoginWrongUser() {
		$this->UserTest =& new UserTest();

		$data = array('username' => 'NykasenMasa', 'password' => 'kekbur42');
		$result = $this->UserTest->validateLogin($data);
		$this->assertFalse($result);
	}
	
	function testGenerateSalt() {
		$this->UserTest =& new UserTest();
		
		$salt = $this->UserTest->generateSalt();
		$this->assertTrue(strlen($salt) == 20);
		
		// Suolan uniikkius
		$salt2 = $this->UserTest->generateSalt();
		$this->assertNotEqual($salt, $salt2);
	}
	
	function testEncryptPasswordWithSalt() {
		$this->UserTest =& new UserTest();
		$expected = '82ec32a1d03ca19c962997a0b8f2407eee610904';

		// Oikea salasana ja suola
		$password = 'kekbur42'; 
		$salt = '13dd0611da78d0bf58ab';
		$result = $this->UserTest->encryptPasswordWithSalt($password, $salt);
		$this->assertEqual($result, $expected);

		// Väärä suola
		$salt = 'abcdefg1da78d0bf58ab';
		$result = $this->UserTest->encryptPasswordWithSalt($password, $salt);
		$this->assertNotEqual($result, $expected);
	}
	
	function testEncryptPassword() {
		$this->UserTest =& new UserTest();

		$result = $this->UserTest->encryptPassword('salakala123');
		$this->assertTrue(strlen($result['hashed_password']) == 40);
		$this->assertTrue(strlen($result['salt']) == 20);

		$result = $this->UserTest->encryptPassword('h2o');
		$this->assertTrue(strlen($result['hashed_password']) == 40);
		$this->assertTrue(strlen($result['salt']) == 20);
	}
	
	function testCompareUserRole() {
		$u =& new UserTest();

		$this->assertTrue($u->compareUserRole('kayttaja', 'kayttaja') == 0);
		$this->assertTrue($u->compareUserRole('yllapitaja', 'kayttaja') > 0);
		$this->assertTrue($u->compareUserRole('kayttaja', 'yllapitaja') < 0);
		$this->assertTrue($u->compareUserRole('virkailija', 'jasenvirkailija') < 0);
		$this->assertTrue($u->compareUserRole('virkailija', 'kayttaja') > 0);
		$this->assertTrue($u->compareUserRole('jasenvirkailija', 'yllapitaja') < 0);
	}

	function testSave() {
		$u =& new UserTest();

		$valid = array('User' =>
					   array('username' => 'asdfghjk',
							 'name' => 'Testiö Tapani Käyttäjä',
							 'screen_name' => 'Testiö Käyttäjä',
							 'email' => 'tes_ti+kayttaja@foo.example.com',
							 'residence' => 'HelsinkiäöÄÖ',
							 'phone' => '+358 40 123 4567',
							 'hyy_member' => '1',
							 'membership' => 'ei-jasen',
							 'role' => 'kayttaja',
							 'hashed_password' => '99808101915c2e276799cebb55b640cd42070acf',
							 'salt' => 'f8d3777af6a55113219f',
							 'tktl' => '0',
							 'deleted' => false));

		$errors = array(array('username' => 'asdf('),
						array('username' => 'asdfghjkl'),
						array('name' => ''),
						array('name' => 'Foo'),
						array('screen_name' => ''),
						array('screen_name' => 'bar'),
						array('email' => ''),
						array('email' => 'jotain'),
						array('email' => 'http://example.com'),
						array('email' => '09 123 4567'),
						array('residence' => ''),
						array('residence' => 'A'),
						array('phone' => 'http://example.com'),
						array('phone' => '1234A5678'),
						array('hyy_member' => 'A'),
						array('hyy_member' => 'Moo'),
						array('hyy_member' => '2'),
						array('membership' => 'Isokiho'),
						array('membership' => '123456'),
						array('role' => 'mestari'),
						array('role' => '123456'),
						array('hashed_password' => 'yber-salasana'),
						array('hashed_password' => ''),
						array('hashed_password' => '99808101915c2e276799cebb55b640cd42070ac'),
						array('hashed_password' => '99808101915c2e276799cebb55b640cd42070acfa'),
						array('tktl' => 'A'),
						array('tktl' => 'Moo'),
						array('tktl' => '2') );

		foreach($errors as $error) {
			$bad = $valid;
			foreach ($error as $id => $value) {
				$bad['User'][$id] = $value;
			}
			$u->create();
			$this->assertFalse($u->save($bad), $id . ' => ' . $value);
		}

		/* Eka kerta oikeilla tiedoilla pitäisi onnistua nätisti */
		$u->create();
		$this->assertTrue($u->save($valid));
		/* Toiseen kertaan ei pitäisi pystyä samoja tietoja tallettamaan */
		$u->create();
		$this->assertFalse($u->save($valid));
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
