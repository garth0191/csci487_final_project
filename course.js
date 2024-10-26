document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.querySelector('.logout');
    const courseCreateButton = document.querySelector('.create');
    const homeButton = document.querySelector('.home');
    const accountButton = document.querySelector('.account');

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
    let table, rows, switching, i, x, y, shouldSwitch, direction;
    let switchCount = 0;

    table = document.getElementById(tableName);
    let header = table.getElementsByTagName("th")[n];
    switching = true;

    //Sorting direction set to ASCENDING.
    direction = "asc";

    // Loops until no switching has been done.
    while (switching) {
        switching = false;
        rows = table.rows;
        //Loop through all table rows (except the headers).
        for (i = 1; i < (rows.length-1); i++) {
            shouldSwitch = false;
            //Comparisons.
            x = rows[i].getElementsByTagName("td")[n];
            y = rows[i+1].getElementsByTagName("td")[n];
            if (direction == "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (direction == "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            header.textContent = header.textContent.replace(/ [^˅]$/, '');
            header.textContent += ' ^';
            rows[i].parentNode.insertBefore(rows[i+1], rows[i]);
            switching = true;
            switchCount++;
        } else {
            if (switchCount == 0 && direction == "asc") {
                header.textContent = header.textContent.replace(/ [^˅]$/, '');
                header.textContent += ' ˅';
                direction = "desc";
                switching = true;
            }
        }
    }
}