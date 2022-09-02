function valid()
{
	if (document.getElementById('email').value != "" && document.getElementById('password').value != "" && (document.getElementById('accountType').options[document.getElementById('accountType').selectedIndex].value == 1 || document.getElementById('accountType').options[document.getElementById('accountType').selectedIndex].value == 2))
	{
		return true; // submit the form if valid
	}
	return false;
}