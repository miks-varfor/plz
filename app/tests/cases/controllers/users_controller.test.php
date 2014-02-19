<?php

class UsersControllerTest extends CakeTestCase
{
	var $fixtures = array('Users');

	function startCase()
	{
		echo '<h1>Starting Test Case</h1>';
	}
	function endCase()
	{
		echo '<h1>Ending Test Case</h1>';
	}
	function startTest($method)
	{
		echo '<h3>Starting method ' . $method . '</h3>';
	}
	function endTest($method)
	{
		echo '<hr />';
	}

	function testNewUserForm()
	{
		$result = $this->testAction('/users/add');
		debug($result);
	}

	function testBadData()
	{
		$data = array('User' => array('username' => 'abcdefghj',
					      'given_names' => '1',
					      'last_name' => '-',
					      'call_name' => '/',
					      'email' => 'foo',
					      'residence' => '123',
					      'phone' => 'numero',
					      'password' => 'a',
					      'password2' => 'b',
					      'hyy_member' => 'f',
					      'tktl' => 'o',
					      'group_1' => '1'));
		$result = $this->testAction('/users/add',
					    array('method' => 'post',
						  'data' => $data));
	}
						  
}

/*
Local variables:
mode:php
c-file-style:"bsd"
End:
*/
?> 
