document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.querySelector('.logout');
    //TEMPORARY COURSE BUTTON -- TO BE DELETED LATER.
    const courseButton = document.querySelector('.course');

    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            window.location.href = 'logout.php';
        });
    } else {
        console.error('You have not yet created a LOGOUT button.');
    }
});