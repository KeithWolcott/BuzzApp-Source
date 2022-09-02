function fixDate(inp,month)
{
	if (month)
	{
		inp += 1;
	}
	while (inp.length < 2)
		inp = "0" + inp;
	return inp;
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
		var date = new Date($("#date").val());
		if (date == "Invalid Date")
		{
			$("#appointmentstatus").html("Invalid day.");
		}
		else
		{
			if ($(f).attr("id") == "reschedule")
			{
					$.get('checkClosed.php?id=' + id.val() + '&year=' + date.getFullYear() + '&month=' + fixDate(date.getMonth(), true) + '&day=' + fixDate(date.getDate(), false) + "&mode=2", function(result){
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
				$.get('checkClosed.php?id=' + id.val() + '&year=' + date.getFullYear() + '&month=' + fixDate(date.getMonth(), true) + '&day=' + fixDate(date.getDate(), false) + "&mode=1", function(result){
					var obj = $(result).find('body');
					$("#appointmentstatus").html("");
					if (result == "closed")
						$("#appointmentstatus").html("Sorry, barbershop is closed that day.");
					else if (result == "invalid")
						$("#appointmentstatus").html("Unable to continue.");
					else if (result == "past")
						$("#appointmentstatus").html("Date is before today.");
					else
						f.submit();
				});
			}
			}
	}
	else
	{
		$("#appointmentstatus").html("Need to select a service.");
	}
	return false;
}
function revealAppointment(sp)
{
	var review = $(sp).parent().find("div[name='contents']");
	$(review).slideToggle("slow");
}
function enableButton(opt)
{
	var butn = $(opt).parent().parent().parent().find("input[name='book']");
	$(butn).prop("disabled", false);
}
function enableChange(radio)
{
	var div = $(radio).parent().parent().parent().find("div[name='chooseDay']");
	if ($(radio).val() == "reschedule")
	{
		$(div).slideDown("fast");
	}
	else
	{
		$(div).slideUp("fast");
	}
	var txt = $(radio).parent().parent().parent().find("textarea[name='reason']");
	checkLength(txt);
	$(txt).on('input', function()
	{
		checkLength(txt);
	});
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
function confirmDeletion(f)
{
	var id = $(f).find("input[name='id']");
	var reason = $(f).find("textarea[name='reason']");
	reason.removeClass("invalid");
	if (reason.val().length <= 0 || reason.val().length > 500)
	{
		reason.addClass("invalid");
	}
	else
	{
		var reschedule = $("#reschedule" + id.val());
		if ($(reschedule).is(":checked"))
		{
			f.action = "rescheduleAppointment.php";
			f.submit();
		}
		else
		{
			if (confirm("Are you sure? You won't receive a refund."))
			{
				var div = $(f).parent();
				var div2 = $(div).parent();
				$.ajax({
					type: 'post',
					url: 'cancelAppointment.php',
					data: $(f).serialize(),
					success: function () {
						div.slideUp("fast", function() {
						  div2.remove();
						});
					}
				  });
			}
		}
	}
	return false;
}
function getcurrentlocation(submitForm) {
	jQuery.post("https://www.googleapis.com/geolocation/v1/geolocate?key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ", function(success) {
		var geocoder = new google.maps.Geocoder;
		var latlng = {lat: success.location.lat, lng: success.location.lng};
        geocoder.geocode({'location': latlng}, function(results, status) {
          if (status === 'OK') {
            if (results[0]) {
              document.getElementById('addressInput').value = results[0].formatted_address;
			  if (submitForm)
			  {
				  document.getElementById('barbershop').submit();
              }
			}
		  }
		 });
  });
}
function donothing()
{
	
}
function validVIP()
{
	
}
function revealReview(sp)
{
	var review = $(sp).parent().find("div[name='contents']");
	var link1 = $(sp).parent().find("span[name='review1']");
	var link2 = $(sp).parent().find("span[name='review2']");
	var form = $(review).find("form:first");
	$(review).slideToggle("slow");
	if ($(link1).hasClass("unread"))
	{
		$.ajax({
			type: 'post',
			url: 'readReview.php',
			data: $(form).serialize(),
			success: function () {
				unread = unread - 1;
				if (unread >= 0)
				{
					$("#unreadMsg").html("(" + unread + " unread)");
				}
				link1.removeClass("unread");
				link2.removeClass("unread");
			}
		  });
	}
}
function validate()
{
	if (document.getElementById('addressInput').value == "")
	{
		getcurrentlocation(true);
		return false;
	}
	return true;
}