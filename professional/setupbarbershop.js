function verifyHours()
{
	var days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
	var valid = true;
	for (var i = 0; i < days.length; i++)
	{
		var day = days[i];
		document.getElementById(day + "openhour").className = "";
		document.getElementById(day + "closehour").className = "";
		if (!document.getElementById(day).checked)
		{
			var date1 = false;
			var date2 = false;
			if (document.getElementById(day + "openhour").value.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(document.getElementById(day + "openhour").value)))
			{
				date1 = checktime(document.getElementById(day + "openhour"),document.getElementById(day + "openampm"));
			}
			else
			{
				document.getElementById(day + "openhour").className = "invalid";
				valid = false;
			}
			if (document.getElementById(day + "closehour").value.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(document.getElementById(day + "closehour").value)))
			{
				date2 = checktime(document.getElementById(day + "closehour"),document.getElementById(day + "closeampm"));
			}
			else
			{
				document.getElementById(day + "closehour").className = "invalid";
				valid = false;
			}
			if (date1 != false && date2 != false)
			{
				var diff = date2 - date1;
				if (diff <= 0)
				{
					document.getElementById(day + "openhour").className = "invalid";
					document.getElementById(day + "closehour").className = "invalid";
					valid = false;
				}
			}
		}
	}
	return valid;
}
function verifyField(elem)
{
	if (elem.value == "")
	{
		elem.className = "invalid";
		return false;
	}
	return true;
}
function verifyZip()
{
	var numPattern = /^[0-9\-]+$/; //Source: https://stackoverflow.com/questions/17004790/checking-for-numbers-and-dashes
	if (document.getElementById('zip').value == "" || !numPattern.test(document.getElementById('zip').value) || document.getElementById('zip').value.length < 5)
	{
		document.getElementById('zip').className = "invalid";
		return false;
	}
	return true;
}
function verifyState()
{
	var stateCodes = ["AL","AK","AZ","AR","CA","CO","CT","DE","DC","FL","GA","HI","ID","IL","IN","IA","KS","KY","LA","ME","MD","MA","MI","MN","MS","MO","MT","NE","NV","NH","NJ","NM","NY","NC","ND","OH","OK","OR","PA","PR","RI","SC","SD","TN","TX","UT","VT","VA","VI","WA","WV","WI","WY"];
	if (stateCodes.indexOf(document.getElementById('state').options[document.getElementById('state').selectedIndex].value) == -1)
	{
		document.getElementById('state').className = "invalid";
		return false;
	}
	return true;
}
function verifyFields()
{
	document.getElementById('barbershopName').className = "";
	document.getElementById('address').className = "";
	document.getElementById('city').className = "";
	document.getElementById('state').className = "";
	document.getElementById('zip').className = "";
	return (verifyField(document.getElementById('barbershopName')) && verifyField(document.getElementById('address')) && verifyField(document.getElementById('city')) && verifyZip() && verifyState());
}
function verify()
{
	if (verifyFields() && verifyHours())
	{
		return true;
	}
	return false;
}