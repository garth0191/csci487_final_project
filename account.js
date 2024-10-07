document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.querySelector('.logout');
    const courseCreateButton = document.querySelector('.create');
    const homeButton = document.querySelector('.home');
    const accountButton = document.querySelector('.account');
    const deleteAccount = document.querySelector('.delete-account');

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

    if (deleteAccount) {
        deleteAccount.addEventListener('click', function() {
            const confirmDelete = confirm('Are you sure you want to delete your account? This action cannot be reversed.');
            if (confirmDelete) {
                window.location.href = 'account_delete.php';
            }
        });
    } else {
        console.error('Could not delete account.');
    }
});