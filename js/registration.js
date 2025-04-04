$(document).ready(function() {
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.post('register.php', formData, function(response) {
            alert(response.message);
        }, 'json');
    });
});
