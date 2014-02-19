<?php 
class RegistrationTestFixture extends CakeTestFixture
{
	var $name = 'Registration';
	var $import = array('table' => 'registrations', 'connection' => 'default');

	var $records = array(
		array ('id' => 1, 
		       'user_id' => 1,
		       'calendar_event_id' => 1,
		       'avec_id' => null,
		       'created' => '2008-03-10 23:44:39',
		       'name' => 'Matti Meikäläinen',
		       'email' => 'matti@logic.fi',
		       'phone' => '112',
		       'paid' => null),
		array ('id' => 2, 
		       'user_id' => null,
		       'calendar_event_id' => 1,
		       'avec_id' => null,
		       'created' => '2008-03-11 14:32:01',
		       'name' => 'Teppo Unregged',
		       'email' => 'teppo@example.com',
		       'phone' => '12345',
		       'paid' => null)
	);
}

/*
Local variables:
mode:php
c-file-style:"bsd"
End:
*/
?> 
