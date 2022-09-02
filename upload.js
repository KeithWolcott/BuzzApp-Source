function checkFile(oForm, required)
{
	// Source: https://stackoverflow.com/questions/4234589/validation-of-file-extension-before-uploading-file
	var _validFileExtensions = [".jpg", ".jpeg", ".gif", ".png"];   
	var arrInputs = oForm.getElementsByTagName("input");
    for (var i = 0; i < arrInputs.length; i++) {
        var oInput = arrInputs[i];
        if (oInput.type == "file") {
            var sFileName = oInput.value;
            if (sFileName.length > 0) {
                var blnValid = false;
                for (var j = 0; j < _validFileExtensions.length; j++) {
                    var sCurExtension = _validFileExtensions[j];
                    if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
                        blnValid = true;
                        break;
                    }
                }
                
                if (!blnValid) {
                    alert("Sorry, that is an invalid file type. Must be JPG, JPEG, GIF, or PNG.");
                    return false;
                }
            }
			else if (required)
			{
				alert("Choose an image to upload.");
				oInput.focus();
				return false;
			}
        }
    }
	var returnValue = false;
	var options = {
            uploadProgress: OnProgress,  // pre-submit callback
			complete: function() { returnValue = finishUploading; }
        };
	var percentVal = 0;
	var bar = document.getElementById('progressbar');
	var percent = document.getElementById('statustxt');
	if (blnValid)
	{
		document.getElementById('progressbox').style.display = "block";
		var percentVal = '0%';
		bar.style.width = percentVal;
		percent.innerHTML = percentVal;
	}
	var returnValue = $(oForm).ajaxSubmit(options);
	return returnValue;
	
}
function OnProgress(event, position, total, percentComplete)
{
    var percent = (event.loaded / event.total) * 100;
	var progressbar = document.getElementById('progressbar');
	var statustxt = document.getElementById('statustxt');
    progressbar.style.width = percentComplete + '%' //update progressbar percent complete
    statustxt.innerHTML = percentComplete + '%'; //update status text
    if(percentComplete>50)
	{
		statustxt.style.color = '#fff'; //change status text to white after 50%
	}
}
function finishUploading(responseText, statusText, xhr, $form)
{
	return xhr;
}