function check() {
  if (window.XMLHttpRequest) {
    xhttp = new XMLHttpRequest();
 } else {
    xhttp = new ActiveXObject("Microsoft.XMLHTTP"); // for older browsers
}
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    document.getElementById("checkstatus").innerHTML = this.responseText; // displays the check mark for new account
    }
  };
  var accountType = document.getElementById('accountType').options[document.getElementById('accountType').selectedIndex].value; // just to see whether the select value is 1 or 2
  var email = document.getElementById('email').value
  xhttp.open("GET", "checkaccount.php?accountType="+accountType+"&email="+email, true); // url to check
  xhttp.send();
}
function checkconfirm()
{
	if (document.getElementById("password").value != "" && document.getElementById("confirmpassword").value != "")
	{
		if (document.getElementById("password").value == document.getElementById("confirmpassword").value) // If password and confirm password match
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
	if (document.getElementById('firstName').value != "" && document.getElementById('lastName').value != "" && document.getElementById('email').value != "" && document.getElementById('password').value != "" && (document.getElementById('accountType').options[document.getElementById('accountType').selectedIndex].value == 1 || document.getElementById('accountType').options[document.getElementById('accountType').selectedIndex].value == 2)) // all that to check if valid data
	{
		if (document.getElementById("accountExistence")) // only continue if that image with a check mark is there
		{
		var splitStr = document.getElementById("accountExistence").src.split("/") // just to figure out if it's a check mark
			if (splitStr[splitStr.length - 1] == "checkmark.png")
			{
				if (document.getElementById("passwordmatch")) // same check mark for matching passwords
				{
					var splitStr2 = document.getElementById("passwordmatch").src.split("/")
					if (splitStr2[splitStr2.length - 1] == "checkmark.png")
					{
						return true; // submit the form
					}
				}
			}
		}
	}
	return false;
}