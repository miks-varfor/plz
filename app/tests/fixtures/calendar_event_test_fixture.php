<?php 
class CalendarEventTestFixture extends CakeTestFixture {
	var $name = 'CalendarEvent';
	var $import = array('table' => 'calendar_events', 'connection' => 'default');

	var $records = array(
		array ('id' => 1, 
		       'user_id' => 1,
		       'name' => 'Jonkunlaiset sitsit',
		       'created' => '2008-03-21 12:00:23',
		       'starts' => '2008-04-22 16:00:00',
		       'registration_starts' => '2008-03-21 12:00:00',
		       'registration_ends' => '2008-04-21 16:00:00',
		       'cancellation_starts' => '2008-03-21 12:00:00',
		       'cancellation_ends' => '2008-04-21 16:00:00',
		       'location' => 'Hämeentie 10',
		       'category' => 'Sitsit',
		       'description' => 'Höpöpöpöpöpöp öpöpöpöpö pöpöpöpöpöp höh.',
		       'price' => '15e kaikille jäsenille',
		       'map' => '',
		       'max_participants' => 40,
		       'realised_participants' => null,
		       'membership_required' => true,
		       'outsiders_allowed' => false,
		       'template' => false,
		       'responsible' => 'Teppo Teikari, 122345',
		       'show_responsible' => true,
		       'avec' => true,
		       'deleted' => false)
	);
}

/*
Local variables:
mode:php
c-file-style:"bsd"
End:
*/
?> 
