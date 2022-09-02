function loadMessages()
{
	if (document.getElementById('messages'))
	{
		$("#messages").load("getMessages.php");
	}
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
					loadMessages();
				}
			  });
	}
}