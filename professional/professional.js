function editHours(but)
{
	if (document.getElementById('hours').style.display == "none") // If not editing the hours of operation
	{
		$("#hourtable").slideUp("slow");
		$("#hours").slideDown("slow");
		
	}
	else
	{
		$("#hourtable").slideDown("slow");
		$("#hours").slideUp("slow");
	}
}
function checktime(elem,ampm)
{
	var sp = elem.split(":");
	var hour = 0;
	var min = 0;
	var valid = true;
	if (sp.length == 1) // if they didn't have a colon
	{
		var str = sp[0];
		if (str.length < 3) { // then it's like 10:00
			var str2 = parseInt(str);
			if (str2 > 0 && str2 < 13) {
				hour = str2;
			}
			else
			{
				valid = false;
			}
		}
		else if (str.length == 3) { // if they put it in like 100
			var str2 = parseInt(str.slice(0,1)); // then the first number is the hour
			var str3 = parseInt(str.slice(1));
			if (str2 > 0) {
				hour = str2;
			}
			else
			{
				valid = false;
			}
			if (str3 < 60) {
				min = str3;
			}
			else
			{
				valid = false
			}
		}
		else if (str.length == 4) { // if they put it in like 1100
			var str2 = parseInt(str.slice(0,2)); // if the first number is 0, like 01, it will fix it to just 1.
			var str3 = parseInt(str.slice(2));
			if (str2 > 0 && str2 < 13) {
				hour = str2;
			}
			else
			{
				valid = false;
			}
			if (str3 < 60) {
				min = str3;
			}
			else
			{
				valid = false;
			}
		}
		else
		{
			valid = false;
		}
	}
	else if (sp.length == 2) // so it's like 11:00
	{
		var str2 = parseInt(sp[0]); // if the first number is 0, like 01, it will fix it to just 1.
		var str3 = parseInt(sp[1]);
		if (str2 > 0 && str2 < 13) {
				hour = str2;
		}
		else
		{
			valid = false;
		}
		if (str3 < 60) {
			min = str3;
		}
		else
		{
			valid = false;
		}
	}
	else // if there's more than one colon
	{
		valid = false;
	}
	if (valid)
	{
		if (ampm == 'PM' && hour != 12) {
			hour = hour + 12; // as long as it's not 12:00 PM, then this puts it in 24 hour format
		}
		else if (ampm == 'AM' && hour == 12) { // 0:00 is the same as 12:00 AM
			hour = 0;
		}
		return new Date(2019, 03, 02, hour, min);
	}
	return false;
}
function allowFor(checkBox)
{
	// This greyouts the dialog if the user chooses "Close" on hours form
	var day = checkBox.id;
	if (checkBox.checked)
	{
		document.getElementById(day + "info").className = "greyout";
	}
	else
	{
		document.getElementById(day + "info").className = "";
	}
}
function showChangeHours()
{
	$("#changeHours").slideToggle("slow");
}
function updateCharactersLeft()
{
	var difference = 500 - document.getElementById('post').value.length;
	var disp = document.getElementById('charactersLeft')
	if (difference == 1)
		disp.innerHTML = "1 character left.";
	else if (difference < 0)
	{
		difference = -difference
		disp.innerHTML = difference + " characters too long.";
	}
	else
		disp.innerHTML = difference + " characters left.";
}
$(document).ready(function(){
  $("#showForm").click(function(){
    $("#messageForm").slideDown("slow");
  });
  $("#x").click(function(){
    $("#messageForm").slideUp("slow");
  });
});
$(function () {
        $('#postMessage').on('submit', function (e) {
          e.preventDefault(); // This keeps the page from refreshing.
		  if (document.getElementById('post').value.length > 0 && document.getElementById('post').value.length <= 500)
		  {
			  $.ajax({
				type: 'post',
				url: 'postMessage.php',
				data: $('#postMessage').serialize(),
				success: function () {
					$("#messageForm").slideUp("slow");
					$("#post").val(""); // Reset the form.
					generateMostRecent();
				}
			  });
		  }
        });
     });
function generateMostRecent()
{
	$("#mostRecent").load("mostRecentMessage.php");
}
function changeHours(f)
{
	var days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
	if (f.id == 'hoursform')
	{
		// This is for putting the hours of operation in a barbershop.
		var valid = true;
		for (var i = 0; i < days.length; i++)
		{
			var day = days[i];
			var openhour = $("#" + day + "openhour");
			var closehour = $("#" + day + "closehour");
			var opensplit = openhour.val().split(" ");
			var opentime = opensplit[0];
			var openampm = opensplit[1];
			var closesplit = closehour.val().split(" ");
			var closetime = closesplit[0];
			var closeampm = closesplit[1];
			openhour.removeClass("invalid");
			closehour.removeClass("invalid"); // reset the inputs if they were set to red.
			if (!document.getElementById(day).checked)
			{
				var date1 = false;
				var date2 = false;
				if (opentime.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(opentime)))
				{
					date1 = checktime(opentime,openampm);
				}
				else
				{
					openhour.addClass("invalid");
					valid = false;
				}
				if (closetime.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(closetime)))
				{
					date2 = checktime(closetime,closeampm);
				}
				else
				{
					closehour.addClass("invalid");
					valid = false;
				}
				if (date1 != false && date2 != false)
				{
					var diff = date2 - date1;
					if (diff <= 0)
					{
						openhour.addClass("invalid");
						closehour.addClass("invalid");
						return false;
					}
				}
				else
					valid = false;
				if (!valid)
					return false;
			}
		}
		$.ajax({
			type: 'post',
			url: 'changeSchedule.php',
			data: $("#hoursform").serialize(),
			success: function () {
				generateHours();
				if (document.getElementById('edithours'))
				{
					editHours(document.getElementById('edithours'));
					$("#allchangeHours").load("formtoChangeSchedule.php");
				}
			}
		  });
	}
	else 
	{
		// This is for putting the hours for a professional.
		$.ajax({
			type: "GET" ,
			url: "getHours.php",
			dataType: "xml" ,
			success: function(xml) { // This finds the hours of operation of the barbershop, because the professional's hours need to be a subset of the barbershop's hours
				var daynames = [];
				var openhours = [];
				var openmins = [];
				var closehours = [];
				var closemins = [];
				var valid = true;
				$(xml).find('day').each(function(){
					daynames.push($(this).attr('dayname'));
					openhours.push($(this).attr('openhour'));
					openmins.push($(this).attr('openmin'));
					closehours.push($(this).attr('closehour'));
					closemins.push($(this).attr('closemin'));
				});
				for(var i = 0; i < daynames.length; i++)
				{
					var day = daynames[i];
					if (!document.getElementById(day + "professional").checked)
					{
						var openhour = $("#" + day + "professionalopenhour");
						var closehour = $("#" + day + "professionalclosehour");
						var opensplit = openhour.val().split(" ");
						var opentime = opensplit[0];
						var openampm = opensplit[1];
						var closesplit = closehour.val().split(" ");
						var closetime = closesplit[0];
						var closeampm = closesplit[1];
						openhour.removeClass("invalid");
						closehour.removeClass("invalid"); // reset the inputs if they were set to red.
						var date1 = false;
						var date2 = false;
						if (opentime.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(opentime)))
						{
							date1 = checktime(opentime,openampm);
						}
						else
						{
							openhour.addClass("invalid");
							valid = false;
						}
						if (closetime.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(closetime)))
						{
							date2 = checktime(closetime,closeampm);
						}
						else
						{
							closehour.addClass("invalid");
							valid = false;
						}
						if (date1 != false && date2 != false)
						{
							var diff = date2 - date1;
							if (diff <= 0)
							{
								openhour.addClass("invalid");
								closehour.addClass("invalid");
								valid = false;
							}
							var date3 = new Date(2019, 03, 02, openhours[i], openmins[i]);
							var date4 = new Date(2019, 03, 02, closehours[i], closemins[i]);
							if (date1 < date3)
							{
								openhour.addClass("invalid");
								valid = false;
							}
							if (date2 > date4)
							{
								closehour.addClass("invalid");
								valid = false;
							}
						}
						else
							valid = false;
						if (!valid)
							break;
					}

				}
				if (valid)
				{
					if (document.getElementById("professionalhoursform"))
					{
					$.ajax({
						type: 'post',
						url: 'changeProfessionalSchedule.php',
						data: $("#professionalhoursform").serialize(),
						success: function () {
							$("#allchangeHours").load("formtoChangeSchedule.php");
						}
					  });
					}
					else if (document.getElementById("changeHoursForm"))
					{
					$.ajax({
						type: 'post',
						url: 'changeProfessionalSchedule.php',
						data: $("#changeHoursForm").serialize(),
						success: function () {
							$("#allchangeHours").load("formtoChangeSchedule.php");
						}
					  });
					}
				}
			}
		});
	}
	return false;
}
function generateHours()
{
	$("#hourtable").load("hours.php");
	$("#hours").load("hours2.php?professional=no");
	if (document.getElementById('professionalhours'))
		$("#professionalhours").load("hours2.php?professional=yes");
}
function revealAppointment(sp)
{
	var review = $(sp).parent().find("div[name='contents']");
	$(review).slideToggle("slow");
}
function confirmReschedule(f)
{
	var reason = $(f).find("textarea[name='reason']");
	reason.removeClass("invalid");
	if (reason.val().length <= 0 || reason.val().length > 500)
	{
		reason.addClass("invalid");
		return false;
	}
	else
	{
		return (confirm("Are you sure?"))
	}	
}
function checkLength(text)
{
	var submitButton = $(text).parent().find("input[name='submitForm']");
	if ($(text).val().length > 0 && $(text).val().length <= 500)
	{
		$(submitButton).prop("disabled", false);
	}
	else
	{
		$(submitButton).prop("disabled", true);
	}
}
function showReason(opt)
{
	var form = $(opt).parent().parent().parent().find("div[name='declineform']");
	var te = $(form).find("textarea[name='reason']");
	if (opt.value == "decline")
	{
		$(form).slideDown("slow");
	}
	else
	{
		$(form).slideUp("slow");
	}
	checkLength(te);
}
function confirmCancellation(f)
{
	var id = $(f).find("input[name='id']");
	var radio = $("#decline" + id.val());
	var te = $(f).find("textarea[name='reason']");
	$(te).removeClass("invalid");
	var valid = true;
	if ($(radio).is(":checked"))
	{
		if ($(te).val().length <= 0 || $(te).val().length > 500)
		{
			$(te).addClass("invalid");
			valid = false;
		}
	}
	if (valid)
	{
		var div = $(f).parent();
		$.ajax({
			type: 'post',
			url: 'confirmAppointment.php',
			data: $(f).serialize(),
			success: function () {
				div.slideUp('fast', function() {
				  div.remove(); // After submitting the form on the side, remove the form.
				});
			}
		  });
	}
	return false;
}
function continuetoMakeAppointment(f)
{
	var selectedService = false;
	var services = document.getElementsByName("service");
	if (services.length > 0 && services[0].type == "radio")
	{
		for (var i = 0; i < services.length; i++)
		{
			if (services[i].checked)
			{
				selectedService = true;
				break;
			}
		}
	}
	else
		selectedService = true;
	var id = $(f).find("input[name='id']");
	if (selectedService)
	{
			$.get('../client/checkClosed.php?id=' + id.val() + '&year=' + $("#year").val() + '&month=' + $("#month").val() + '&day=' + $("#day").val() + "&mode=2", function(result){
			var obj = $(result).find('body');
			$("#appointmentstatus").html("");
			if (result == "closed")
				$("#appointmentstatus").html("Sorry, barbershop is closed that day.");
			else if (result == "invalid")
				$("#appointmentstatus").html("Unable to continue.");
			else if (result == "past")
				$("#appointmentstatus").html("Invalid day.");
			else
				f.submit();
		});
	}
	else
	{
		$("#appointmentstatus").html("Need to select a service.");
	}
	return false;
}
function donothing()
{
	
}
function myBarbershop(img)
{
	$(img).parent().parent().submit();
}
function getcurrentlocation() {
	jQuery.post( "https://www.googleapis.com/geolocation/v1/geolocate?key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ&", function(success) {
		var geocoder = new google.maps.Geocoder;
		var latlng = {lat: success.location.lat, lng: success.location.lng};
        geocoder.geocode({'location': latlng}, function(results, status) {
          if (status === 'OK') {
            if (results[0]) {
              document.getElementById('addressInput').value = results[0].formatted_address;
              }
		  }
		 });
  });
}
function checkToEnable(tim)
{
	var li = $(tim).parent();
	var start = li.find("input:first");
	var end = li.find("input:eq(1)");
	var butn = li.find("input[type='button']");
	if (start.val() != "" && end.val() != "")
	{
		butn.prop("disabled", false);
	}
	else
	{
		butn.prop("disabled", true);
	}
}
function autoFillIn(row)
{
	var currentStart = $(row).parent().find("input:first");
	var currentEnd = $(row).parent().find("input:eq(1)");
	if (currentStart.val() != "" && currentEnd.val() != "")
	{
		// all of this is to make sure the time is in the right range for each new field
		var startsplit = currentStart.val().split(" ");
		var starttime = startsplit[0];
		var startampm = startsplit[1];
		var endsplit = currentEnd.val().split(" ");
		var endtime = endsplit[0];
		var endampm = endsplit[1];
		var startdate = checktime(starttime,startampm);
		var enddate = checktime(endtime,endampm);
		if (startdate != false && enddate != false)
		{
			var midnight = new Date(2019, 03, 02, 0, 0, 0);
			var startdistance = (startdate.getTime() - midnight.getTime()) / 1000;
			var enddistance = (enddate.getTime() - midnight.getTime()) / 1000;
			if (startdistance < enddistance)
			{
				var days = ["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];
				for (var i = 0; i < days.length; i++)
				{
					var day = days[i];
					if (document.getElementById(day + "breakstarthour") && document.getElementById(day + "breakendhour"))
					{
						var newopen = $("#" + day + "breakstarthour");
						var newend = $("#" + day + "breakendhour");
						if (newopen != currentStart)
						{
							minTime = newopen.timepicker("option","minTime");
							maxTime = newend.timepicker("option","maxTime");
							if (minTime <= startdistance)
								newopen.val(currentStart.val());
							if (maxTime >= enddistance)
								newend.val(currentEnd.val());
							checkToEnable(newopen);
						}
					}
				}
			}
		}
	}
}
function validateBreak()
{
	var days = ["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];
	var valid = true;
	var overallvalid = true;
	for (var i = 0; i < days.length; i++)
	{
		var day = days[i];
		if (document.getElementById(day + "breakstarthour") && document.getElementById(day + "breakendhour"))
		{
			var openhour = $("#" + day + "breakstarthour");
			var closehour = $("#" + day + "breakendhour");
			openhour.removeClass("invalid");
			closehour.removeClass("invalid");
			if (openhour.val() == "" && closehour.val() == "")
				continue;
			if (openhour.val() == "")
			{
				valid = false;
				overallvalid = false;
				openhour.addClass("invalid");
			}
			if (closehour.val() == "")
			{
				valid = false;
				overallvalid = false;
				closehour.addClass("invalid");
			}
			if (!valid)
				continue;
			var startsplit = openhour.val().split(" ");
			var starttime = startsplit[0];
			var startampm = startsplit[1];
			var endsplit = closehour.val().split(" ");
			var endtime = endsplit[0];
			var endampm = endsplit[1];
			var startdate = checktime(starttime,startampm);
			var enddate = checktime(endtime,endampm);
			if (startdate ==  false && enddate != false)
			{
				valid = false;
				overallvalid = false;
				openhour.addClass("invalid");
			}
			if (closehour.val() == "")
			{
				valid = false;
				overallvalid = false;
				closehour.addClass("invalid");
			}
			if (!valid)
				continue;
			var midnight = new Date(2019, 03, 02, 0, 0, 0);
			var startdistance = (startdate.getTime() - midnight.getTime()) / 1000;
			var enddistance = (enddate.getTime() - midnight.getTime()) / 1000;
			if (startdistance >= enddistance)
			{
				openhour.addClass("invalid");
				closehour.addClass("invalid");
				overallvalid = false;
				valid = false;
			}
			if (!valid)
				continue;
			minTime = openhour.timepicker("option","minTime");
			maxTime = closehour.timepicker("option","maxTime");
			if (startdistance < minTime)
			{
				openhour.addClass("invalid");
				overallvalid = false;
				valid = false;
			}
			if (enddistance > maxTime)
			{
				closehour.addClass("invalid");
				overallvalid = false;
				valid = false;
			}
			if (!valid)
				continue;
			valid = true;
		}
	}
	return overallvalid;
}