document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.querySelector('.logout');
    const courseCreateButton = document.querySelector('.create');
    const homeButton = document.querySelector('.home');
    const accountButton = document.querySelector('.account');
    const pointsGradingSection = document.getElementById('score_type');
    const plusMinusRadios = document.querySelectorAll('input[type="radio"]');

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
        console.error('Could not redirect to homepage.');
    }

    pointsGradingSection.addEventListener('change', function() {
        'use strict';
        let visiblePercentageClass = document.querySelector('.visible');
        let selectedOption = document.getElementById(this.value);
        if (visiblePercentageClass !== null) {
            visiblePercentageClass.classList.remove('visible');
            visiblePercentageClass.classList.add('invisible');

            let allElements = visiblePercentageClass.querySelectorAll('input');
            allElements.forEach(function(input) {
                input.required = false;
            });
        }
        if (selectedOption !== null) {
            selectedOption.classList.add('visible');
            selectedOption.required = true;
        }
    });

    plusMinusRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            'use strict';

            // Make all inputs not required to clear out previous selection.
            let pmTrue = document.querySelector('.pm-true');
            let allTrue = pmTrue.querySelectorAll('input');
            allTrue.forEach(function(input) {
                input.required = false;
            });
            let pmFalse = document.querySelector('.pm-false');
            let allFalse = pmFalse.querySelectorAll('input');
            allFalse.forEach(function(input) {
                input.required = false;
            });

            // Make only selected radio's class inputs required.
            let selectedRadio = document.getElementById(this.value);
            let allSelected = selectedRadio.querySelectorAll('input');
            allSelected.forEach(function(input) {
                input.required = true;
            });
        })
    });
});