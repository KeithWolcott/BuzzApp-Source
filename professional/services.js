function validnew()
{
	var valid = true;
	document.getElementById('serviceName').className = "";
	if (document.getElementById('serviceName').value == "")
	{
		document.getElementById('serviceName').className = "invalid";
		valid = false;
	}
	document.getElementById('price').className = "";
	if (!$.isNumeric($("#price").val()))
	{
		document.getElementById('price').className = "invalid";
		valid = false;
	}
	if (valid)
	{
		 $.ajax({
				type: 'post',
				url: 'addService.php',
				data: $('#newservice').serialize(),
				success: function () {
					$("#serviceName").val("");
					$("#price").val("10.00");
					$("#description").val("");
					generateServices();
				}
			  });
	}
	return false;
}
function updateService(f)
{
	var valid = true;
	var serviceName = $(f).find("#serviceName");
	serviceName.removeClass("invalid");
	if (serviceName.val() == "")
	{
		serviceName.addClass("invalid");
		valid = false;
	}
	$("#price").removeClass("invalid");
	if (!$.isNumeric($("#price").val()))
	{
		$("#price").addClass("invalid");
		valid = false;
	}
	if (valid)
	{
		$.ajax({
				type: 'post',
				url: 'modifyService.php',
				data: $(f).serialize(),
				success: function () {

				}
			  });
	}
	return false;
}
function generateServices()
{
	if (document.getElementById('services'))
	{
		$("#services").load("getServices.php");
	}
}