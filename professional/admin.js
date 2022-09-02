function toggleUpload()
 {
	 $('#uploadNewImage').slideToggle();
 }
function deleteMessage(button)
{
	if (confirm("Are you sure?"))
	{
		var f = $(button).parents("form:first");
		$.ajax({
				type: 'post',
				url: 'deleteMessage.php',
				data: f.find("input[name=post]").serialize(),
				success: function () {
					generateMostRecent();
				}
			  });
	}
}
function displayName()
{
	$("#newname").toggle();
	$("#curname").toggle();
	document.getElementById('newname').focus();
}
function changeName(o)
{
	var f = $(o).parents("form:first");
	f.on("submit", function (e) {
		e.preventDefault();
	});
	$.ajax({
		type: 'post',
		url: 'changeBarbershopDetails.php',
		data: f.serialize(),
		success: function (txt) {
			if (txt == "changed")
			{
				$("#curname").html($("#newname").val());
				displayName();
			}
		}
	  });
}
function displayAddress()
{
	$("#newaddress").toggle();
	$("#curaddress").toggle();
	document.getElementById('newaddress').focus();
}
function changeAddress(o)
{
	var f = $(o).parents("form:first");
	f.on("submit", function (e) {
		e.preventDefault();
	});
	$.ajax({
		type: 'post',
		url: 'changeBarbershopDetails.php',
		data: f.serialize(),
		success: function (txt) {
			if (txt == "exists")
			{
				alert("A barbershop already exists with that address.");
			}
			else if (txt.slice(0,7) == "changed")
			{
				$("#curaddress").html(txt.slice(7));
				$("#newaddress").val(txt.slice(7));
				displayAddress();
			}
			else if (txt == "didntchange")
			{
				displayAddress();
			}
			else
			{
				alert("Invalid address. It must be in the format \"Street Address, City, State Zip\".");
			}
		}
	  });
}
function checkSubmit()
{
	if (document.getElementById('password').value == "" || document.getElementById('confirmpassword').value == "")
	{
		document.getElementById('deletebutton').disabled = true;
	}
	else
	{
		document.getElementById('deletebutton').disabled = false;
	}
}