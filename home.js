document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.querySelector('.logout');
    const courseCreateButton = document.querySelector('.create');
    const homeButton = document.querySelector('.home');
    const accountButton = document.querySelector('.account');
    const courseContainer = document.querySelector('.course-container');
    const tileContainer = document.querySelector('.tile-container');

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

    if (accountButton) {
        accountButton.addEventListener('click', function() {
            window.location.href = 'account.php';
        });
    } else {
        console.error('Could not redirect to account page.');
    }

    if (homeButton) {
        homeButton.addEventListener('click', function() {
            window.location.href = 'home.php';
        });
    } else {
        console.error('Could not redirect to home page.');
    }

    //Create course tiles.
    function createCourseTiles(container) {
        //NOTE: current_courses has been passed to this Javascript file already from home.php.
        container.innerHTML = '';
        current_courses.forEach(course => {
            const tile = document.createElement('div');
            tile.classList.add('course-tile');
            tile.innerHTML = `
                <h2>${course.course_name}</h2>
                <p>Instructor: <em>${course.instructor_name}</em></p>
                <p>${course.course_description}</p>
            `;

            tile.addEventListener('click', function() {
                window.location.href = `course.php?course_id=${$course.course_id}`;
            });
        });
        container.appendChild(tile);
    }

    createCourseTiles(tileContainer);
    courseContainer.style.display = 'block';
    tileContainer.style.display = 'block';
});