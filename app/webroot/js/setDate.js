function lz(n) {
        if (n < 10)
                return "0" + n;
        return n;
}
function setDate() {
        var d = new Date();
        var cb = document.getElementById("CalendarEventCanParticipate");
//        if (CalendarEventCanParticipate.checked == true) {
        if(cb.checked == true){
                var starts = document.getElementById("CalendarEventRegistrationStarts");
                //Oletusalkuaika tulevaisuuteen
                d.setHours(d.getHours() + 1);
                starts.value = lz(d.getDate()) +
                        "." + lz(d.getMonth()+1) +
                        "." + d.getFullYear() +
                        " " + lz(d.getHours()) +
                        ":" + lz(d.getMinutes());
                var ends = document.getElementById("CalendarEventRegistrationEnds");
                var date = document.getElementById("CalendarEventDate");
                var time = document.getElementById("CalendarEventTime");
                ends.value = date.value + " " + time.value;
        } else {
                var starts = document.getElementById("CalendarEventRegistrationStarts");
                var ends = document.getElementById("CalendarEventRegistrationEnds");
                starts.value = "";
                ends.value = "";
//                CalendarEventRegistrationStarts.value = "";
//                CalendarEventRegistrationEnds.value = "";
        }
}
function setCanCancel(canCancel) {
	var cancStarts = document.getElementById("CalendarEventCancellationStarts");
	var cancEnds = document.getElementById("CalendarEventCancellationEnds");
	var regStarts = document.getElementById("CalendarEventRegistrationStarts");

	if (canCancel) {
		cancStarts.value = regStarts.value;
		var regEnds = document.getElementById("CalendarEventRegistrationEnds");
		cancEnds.value = regEnds.value;
	}
	else {
		cancStarts.value = regStarts.value;
		cancEnds.value = regStarts.value;
	}
}
