function check() {
  if (document.getElementById("newpassword").value != "" && document.getElementById("confirmpassword").value != "")
	{
		if (document.getElementById("newpassword").value == document.getElementById("confirmpassword").value) // if passwords are not blank and match
		{
			document.getElementById("checkpassword").innerHTML = "<img src=\"images/checkmark.png\" alt=\"Passwords match\" id=\"passwordmatch\" title=\"Passwords match\">";
		}
		else
		{
			document.getElementById("checkpassword").innerHTML = "<img src=\"images/redx.png\" alt=\"Passwords do not match\" title=\"Passwords do not match\">";
		}
	}
}
function valid()
{
	if (document.getElementById('currentpassword').value != "" && document.getElementById('newpassword').value != "" && document.getElementById('confirmpassword').value != "") // if none of the fields are blank
	{
		if (document.getElementById("passwordmatch")) // if the check mark image is on the page
		{
			var splitStr = document.getElementById("passwordmatch").src.split("/")
			if (splitStr[splitStr.length - 1] == "checkmark.png") // if the image is correct.
			{
				return true; // submit the form
			}
		}
	}
	return false;
}