document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.querySelector('.logout');
    const courseCreateButton = document.querySelector('.create');
    const homeButton = document.querySelector('.home');
    const accountButton = document.querySelector('.account');
    const adminButton = document.querySelector('.admin');

    if (adminButton) {
        adminButton.addEventListener('click', function() {
            window.location.href = 'admin_dashboard.php';
        });
    } else {
        console.error('No ADMIN DASHBOARD button is present on the page.');
    }

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

    if (homeButton) {
        homeButton.addEventListener('click', function() {
            window.location.href = 'home.php';
        });
    } else {
        console.error('Could not redirect to homepage.');
    }

    if (accountButton) {
        accountButton.addEventListener('click', function() {
            window.location.href = 'account.php';
        });
    } else {
        console.error('Could not redirect to account page.');
    }
});

function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
        event.preventDefault();
    }
}