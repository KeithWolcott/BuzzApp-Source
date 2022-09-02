function writeReview(button)
{
	// Show the review form for the corresponding visit.
	var f = $(button).parents("div:first");
	var div = f.find("#reviewForm");
	div.slideDown("slow");
}
function dontWriteReview(button)
{
	if (confirm("Are you sure? You won't be asked again.")) // First, ask the user. This continues if they say "Ok".
	{
		var div = $(button).parents("div:first");
		var f = div.find("form");
		$.ajax({
				type: 'post',
				url: 'client/dontPostReview.php',
				data: f.serialize(),
				success: function () {
					div.slideUp('fast', function() {
					  div.remove(); // After submitting the form on the side, remove the form.
					});
				}
			  });
	}
}
function closeReview(button)
{
	// Hide the review form for the corresponding visit.
	var f = $(button).parent().parent().parent();
	var div = f.find("#reviewForm");
	div.slideUp("slow");
}
function ranking(num,star)
{
	// Sets the number of stars correctly.
	var f = $(star).parent();
	var stars = f.children("[name='star']");
	var x = num - 1;
	for(var i = 0; i < stars.length;i++)
	{
		if (i > x)
		{
			stars[i].src = "images/blankstar.png";
		}
		else
		{
			stars[i].src = "images/star.png";
		}
	}
}
function updateCharactersLeft(textarea)
{
	var f = $(textarea).parent();
	var disp = f.find("#charactersLeft");
	var difference = 1000 - textarea.value.length;
	if (difference == 1)
		disp.text("1 character left.");
	else if (difference < 0)
	{
		difference = -difference
		disp.text(difference + " characters too long.");
	}
	else
		disp.text(difference + " characters left.");
}
function postreview(f)
{
	var div = $(f).parent().parent();
	var stars = $(f).children("[name='star']");
	var ranking = 0;
	for(var i = 0; i < stars.length;i++)
	{
		var sp = stars[i].src.split("/");
		if (sp[sp.length - 1] == "star.png")
		{
			ranking += 1;
		}
	}
	if (ranking < 1)
	{
		ranking = 3;
	} // make sure the star rating is valid.
	$(f).find("#rating").val(ranking);
	$.ajax({
				type: 'post',
				url: 'client/postReview.php',
				data: $(f).serialize(),
				success: function () {
					div.slideUp('fast', function() {
					  div.remove();
					});
				}
			  });
	return false;
}
function remindSchedule(f)
{
	var div = $(f).parent();
	$.ajax({
			type: 'post',
			url: 'client/dontRemindReschedule.php',
			data: $(f).serialize(),
			success: function () {
				div.slideUp('fast', function() {
				  div.remove(); // After submitting the form on the side, remove the form.
				});
			}
		  });
	return false;		  
}