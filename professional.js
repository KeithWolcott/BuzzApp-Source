function acceptRequest(button)
{
var div = $(button).parents("div:first");
var f = div.find("form");
$.ajax({
		type: 'post',
		url: 'accept.php',
		data: f.serialize(),
		success: function () {
			div.slideUp('fast', function() {
			  div.remove();
			});
		}
	  });
}
function decline(button)
{
	if (confirm("Are you sure?"))
	{
		var div = $(button).parents("div:first");
		var f = div.find("form");
		$.ajax({
				type: 'post',
				url: 'decline.php',
				data: f.serialize(),
				success: function () {
					div.slideUp('fast', function() {
					  div.remove();
					});
				}
			  });
	}
}
function changeHours2()
{
$.ajax({
	type: "GET" ,
	url: "professional/getHours.php",
	dataType: "xml" ,
	success: function(xml) {
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
			var openhour = $("#" + day + "openhour");
			var closehour = $("#" + day + "closehour");
			var opensplit = openhour.val().split(" ");
			var opentime = opensplit[0];
			var openampm = opensplit[1];
			var closesplit = closehour.val().split(" ");
			var closetime = closesplit[0];
			var closeampm = closesplit[1];
			openhour.removeClass("invalid");
			closehour.removeClass("invalid");
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
		if (valid)
		{
			$.ajax({
			type: 'post',
			url: 'professional/changeProfessionalSchedule.php',
			data: $("#professionalhoursform").serialize(),
			success: function () {
				$("#professionalhours").html("<a href=\"professional/addLunchBreak.php\">Now go here to add a break for each day.</a>");
			}
		  });
		}
	}
});
return false;
}
function remindCancellation(f)
{
	var div = $(f).parent();
	$.ajax({
			type: 'post',
			url: 'professional/dontRemindCancellation.php',
			data: $(f).serialize(),
			success: function () {
				div.slideUp('fast', function() {
				  div.remove(); // After submitting the form on the side, remove the form.
				});
			}
		  });	
	return false;
}