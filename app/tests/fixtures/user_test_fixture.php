<?php 
class UserTestFixture extends CakeTestFixture
{
	var $name = 'User';
	var $import = array('table' => 'users', 'connection' => 'default');

	var $records = array(
		array ('id' => 1, 'username' => 'matti',
		       'name' => 'Matti Mainio Meikäläinen',
		       'screen_name' => 'Matti Meikäläinen',
		       'email' => 'matti@logic.fi',
		       'residence' => 'Helsinki',
		       'phone' => '112',
		       'hyy_member' => false,
		       'membership' => null,
		       'role' => 'kayttaja',
		       'created' => '2008-03-09 23:44:39',
		       'modified' => '2008-03-09 23:44:39',
		       'hashed_password' => '82ec32a1d03ca19c962997a0b8f2407eee610904',
		       'salt' => '13dd0611da78d0bf58ab',
		       'tktl' => false,
		       'deleted' => false),
		array ('id' => 2, 'username' => null,
		       'name' => null, 'screen_name' => null, 'email' => null,
		       'residence' => null, 'phone' => null,
		       'hyy_member' => null, 'membership' => null,
		       'role' => null,
		       'created' => '2008-03-10 12:23:23',
		       'modified' => '2008-03-10 12:23:23',
		       'hashed_password' => null, 'salt' => null,
		       'tktl' => null, 'deleted' => true),
	);
}

/*
Local variables:
mode:php
c-file-style:"bsd"
End:
*/
?> 
