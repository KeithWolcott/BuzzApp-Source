function editDate(button)
{
	var div = $(button).parent().find("#dayinfo"); // find the div containing the button
	// Check if time is there yet
	if (div.hasClass("greyout"))
	{
		$(button).val("Cancel Edit");
		div.removeClass("greyout");
	}
	else
	{
		$(button).val("Edit");
		div.addClass("greyout");
	}
}
function checkdate()
{
	var valid = true;
	var d = new Date($("#month").val());
	document.getElementById('datedetail').innerHTML = "";
	if (d == "Invalid Date") 
	{
		valid = false;
		document.getElementById('datedetail').innerHTML = "Invalid date";
	}
	if (valid)
	{
		var today = new Date();
		if (today.getMonth() > d.getMonth())
		{
			valid = false;
			document.getElementById('datedetail').innerHTML = "Date is in the past.";
		}
	}
	return valid;
}
function revealAppointment(sp)
{
	var review = $(sp).parent().find("div[name='contents']");
	$(review).slideToggle("slow");
}
function confirmDeletion(f)
{
	var reason = $(f).find("textarea[name='reason']");
	reason.removeClass("invalid");
	if (reason.val().length <= 0 || reason.val().length > 500)
	{
		reason.addClass("invalid");
	}
	else
	{
		if (confirm("Are you sure?"))
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
	return false;
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