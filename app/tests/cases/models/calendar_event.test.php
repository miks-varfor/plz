<?php 
App::import('Model', 'CalendarEvent');

class CalendarEventTest extends CalendarEvent {
	var $name = 'CalendarEvent';
	var $useDbConfig = 'test_suite';
	var $hasMany = array();
	var $belongsTo = array();
}

class CalendarEventTestCase extends CakeTestCase {
	var $fixtures = array( 'calendar_event_test' );
	var $dbFormat = '%Y-%m-%d %R';
	
	function makeEventDates($reg_starts, $reg_ends, $cancel_starts = null, $cancel_ends = null) {
		$event = array();
		$event['registration_starts'] = strftime($this->dbFormat, $reg_starts);
		$event['registration_ends'] = strftime($this->dbFormat, $reg_ends);
		$event['cancellation_starts'] = strftime($this->dbFormat, $cancel_starts);
		$event['cancellation_ends'] = strftime($this->dbFormat, $cancel_ends);
		return $event;
	}
	
	function testIsRegistrableNow() {
		$this->CalendarEventTest =& new CalendarEventTest();
		
		$event = $this->makeEventDates(time() - 5*60*60, time() + 5*24*60*60);
		$this->assertTrue($this->CalendarEventTest->isRegistrableNow($event));
		
		$event = $this->makeEventDates(time(), time() + 10*60);
		$this->assertTrue($this->CalendarEventTest->isRegistrableNow($event));
		
		$event = $this->makeEventDates(time() + 60*60, time() + 10*24*60*60);
		$this->assertFalse($this->CalendarEventTest->isRegistrableNow($event));

		$event = $this->makeEventDates(time() - 10*24*60*60, time() - 5*60*60);
		$this->assertFalse($this->CalendarEventTest->isRegistrableNow($event));
	}
	
	function testIsCancelableNow() {
		$this->CalendarEventTest =& new CalendarEventTest();

		$event = $this->makeEventDates(null, null, time() - 5*60*60, time() + 5*24*60*60);
		$this->assertTrue($this->CalendarEventTest->isCancelableNow($event));
		
		$event = $this->makeEventDates(null, null, time(), time() + 10*60);
		$this->assertTrue($this->CalendarEventTest->isCancelableNow($event));
		
		$event = $this->makeEventDates(null, null, time() + 60*60, time() + 10*24*60*60);
		$this->assertFalse($this->CalendarEventTest->isCancelableNow($event));

		$event = $this->makeEventDates(null, null, time() - 10*24*60*60, time() - 5*60*60);
		$this->assertFalse($this->CalendarEventTest->isCancelableNow($event));
		
	}
}

?>
