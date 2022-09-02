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
  xhttp.open("GET", "checkaccount2.php?accountType="+accountType+"&email="+email, true); // url to check
  xhttp.send();
}
function valid()
{
	if (document.getElementById('email').value != "" && (document.getElementById('accountType').options[document.getElementById('accountType').selectedIndex].value == 1 || document.getElementById('accountType').options[document.getElementById('accountType').selectedIndex].value == 2))
	{
		if (document.getElementById("accountExistence")) // only continue if that image with a check mark is there
		{
			var splitStr = document.getElementById("accountExistence").src.split("/") // just to figure out if it's a check mark
			if (splitStr[splitStr.length - 1] == "checkmark.png")
			{
				return true; // submit the form
			}
		}
	}
	return false;
}