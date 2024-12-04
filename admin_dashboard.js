document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.querySelector('.logout');
    const courseCreateButton = document.querySelector('.create');
    const homeButton = document.querySelector('.home');
    const accountButton = document.querySelector('.account');
    const adminButton = document.querySelector('.admin');
    const addInstructorButton = document.getElementById('add-instructor-button');
    const addInstructorModal = document.getElementById('add-instructor-modal');
    const closeButton = addInstructorModal.querySelector('.close-button');

    if (addInstructorButton) {
        addInstructorButton.addEventListener('click', function() {
            addInstructorModal.style.display = 'block';
        });
    }

    if (closeButton) {
        closeButton.addEventListener('click', function() {
            addInstructorModal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target == addInstructorModal) {
            addInstructorModal.style.display = 'none';
        }
    });

    if (typeof showModal !== 'undefined' && showModal) {
        addInstructorModal.style.display = 'block';
    }

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
});

function sortTable(n, tableName) {
    let table = document.getElementById(tableName);
    let switching = true;
    let rows, shouldSwitch, i, x, y, direction, switchCount = 0;

    // Get all header elements
    let headers = table.getElementsByTagName("th");
    let header = headers[n];

    // Get current direction and toggle
    direction = header.getAttribute('data-sort-direction') === 'asc' ? 'desc' : 'asc';
    header.setAttribute('data-sort-direction', direction);

    // Remove sort indicators and sort direction from other headers
    for (let j = 0; j < headers.length; j++) {
        if (j !== n) {
            headers[j].removeAttribute('data-sort-direction');
            headers[j].classList.remove('sort-asc', 'sort-desc');
        }
    }

    // Sorting loop
    while (switching) {
        switching = false;
        rows = table.rows;

        // Loop through all table rows (except the header)
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            // Get the two elements to compare
            x = rows[i].getElementsByTagName("td")[n];
            y = rows[i + 1].getElementsByTagName("td")[n];

            // Get the content to compare
            let xContent = x.textContent || x.innerText;
            let yContent = y.textContent || y.innerText;

            // Compare based on direction
            if (direction == "asc") {
                if (xContent.toLowerCase() > yContent.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else {
                if (xContent.toLowerCase() < yContent.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            // Swap the rows
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchCount++;
        }
    }

    // Update sort indicator
    header.classList.remove('sort-asc', 'sort-desc');
    header.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
}


function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
        event.preventDefault();
    }
}