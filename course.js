document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.querySelector('.logout');
    const courseCreateButton = document.querySelector('.create');
    const homebutton = document.querySelector('.home');

    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            window.location.href = 'logout.php';
        });
    } else {
        console.error('You have not yet created a LOGOUT button.');
    }

    if (courseCreateButton) {
        courseCreateButton.addEventListener('click', function () {
            window.location.href = 'course_create.php';
        });
    } else {
        console.error('Could not redirect to course_create.php');
    }

    if (homebutton) {
        homeButton.addEventListener('click', function() {
            window.location.href = 'home.php';
        });
    } else {
        console.error('Could not redirect to home page.');
    }
});