$(document).ready(function() {

	$("form").submit(function() {
		var th = $(this);
		$.ajax({
			type: "POST",
			url: "mail.php",
			data: th.serialize()
		}).done(function() {
			th.trigger("reset");
		});
		return false;
	});

});
