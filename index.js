//JavaScript file for login/signup (index) page.

document.addEventListener('DOMContentLoaded', function() {
    let signupSlider = document.querySelector(".signup-slider-button");
    let loginSlider = document.querySelector(".login-slider-button");
    let slider = document.querySelector(".slider");
    let formSection = document.querySelector(".form-section");
    let loginSubmit = document.querySelector(".login-submit");
    let signupSubmit = document.querySelector(".signup-submit");

    //Slide to display sign-up options.
    signupSlider.addEventListener("click", () => {
        slider.classList.add("move-slider");
        formSection.classList.add("form-section-move");
    });

    //Slide back to original login options.
    loginSlider.addEventListener("click", () => {
        slider.classList.remove("move-slider");
        formSection.classList.remove("form-section-move");
    });

    loginSubmit.addEventListener("click", () => {
        //Check whether user clicked the "sign-up" button instead.
        const isSignup = loginSubmit.innerText === "Signup";
        if (isSignup) {
            //Display response to user.
            const notification = document.querySelector(".notification");
            notification.innerText = "Thank you for signing up! Please log in."
            notification.style.display = "block";
            setTimeout(() => {
                notification.style.display = "none";
            }, 3000);
        } else {
            //Redirect to home page.
            window.location.href = "home.html";
        }
    });

    signupSubmit.addEventListener("click", () => {
        const notification = document.querySelector(".notification");
        notification.innerText = "Thank you for signing up! Please log in.";
        notification.style.display = "block";
        setTimeout(() => {
            notification.style.display = "none";
        }, 3000);
    });

    loginSlider.addEventListener("click", function() {
        setTimeout(function() {
            loginSlider.classList.add("active");
            signupSlider.classList.remove("active");
            loginSubmit.innerText = "Login";
        }, 140);
    });

    signupSlider.addEventListener("click", function() {
        setTimeout(function() {
            signupSlider.classList.add("active");
            loginSlider.classList.remove("active");
            loginSubmit.innerText = "Signup";
        }, 140);
    });
});