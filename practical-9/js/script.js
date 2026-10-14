function validateLogin() {
    var studentID = document.getElementById("studentID").value;
    var password = document.getElementById("password").value;
    if (studentID === "" || password === "") {
        alert("Please enter both Student ID and Password!");
    } else {
        alert("Login Successful!");
        window.location.href = "profile.html";
    }
}

function validateRegistration(event) {
    var name = document.getElementById("fullName").value.trim();
    var email = document.getElementById("email").value.trim();
    var mobile = document.getElementById("mobile").value.trim();
    var password = document.getElementById("password").value.trim();
    var confirmPassword = document.getElementById("confirmPassword").value.trim();
    var year = document.getElementById("year").value;
    var terms = document.getElementById("terms").checked;

    var courseSelect = document.getElementById("course");
    var selectedCourses = [];
    for (var i = 0; i < courseSelect.options.length; i++) {
        if (courseSelect.options[i].selected) {
            selectedCourses.push(courseSelect.options[i].value);
        }
    }

    var genderRadios = document.getElementsByName("gender");
    var genderSelected = false;
    for (var j = 0; j < genderRadios.length; j++) {
        if (genderRadios[j].checked) {
            genderSelected = true;
            break;
        }
    }

    var nameRegex = /^[A-Za-z\s]{3,30}$/;
    var passRegex = /^[a-zA-Z0-9]+$/;
    var mobileRegex = /^[6-9]\d{9}$/;
    var emailRegex = /^2\d[a-z]{2,4}\d{3}@charusat\.edu\.in$/i;

    if (!nameRegex.test(name)) {
        alert("Invalid Name: Please enter letters only (minimum 3 characters).");
        event.preventDefault();
        return false;
    }

    if (!emailRegex.test(email)) {
        alert("Invalid Email: Please enter a valid email address.");
        event.preventDefault();
        return false;
    }

    if (!mobileRegex.test(mobile)) {
        alert("Invalid Mobile: Please enter a valid 10-digit mobile number.");
        event.preventDefault();
        return false;
    }

    if (!passRegex.test(password)) {
        alert("Invalid Password format!");
        event.preventDefault();
        return false;
    }

    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        event.preventDefault();
        return false;
    }

    if (selectedCourses.length === 0) {
        alert("Please select at least one course.");
        event.preventDefault();
        return false;
    }

    if (year === "") {
        alert("Please select your Year of study.");
        event.preventDefault();
        return false;
    }

    if (!genderSelected) {
        alert("Please select your Gender.");
        event.preventDefault();
        return false;
    }

    if (!terms) {
        alert("You must accept the terms and conditions.");
        event.preventDefault();
        return false;
    }

    return true;
}

async function loadCoursePageData() {
    var response = await fetch('../data/student.json');
    var data = await response.json();

    var studentBox = document.getElementById("studentDetails");
    studentBox.innerHTML = "<h3>Student Name: " + data.studentName + "</h3>" +
                           "<h3>Student ID: " + data.studentID + "</h3>";

    var courseBox = document.getElementById("courseList");
    courseBox.innerHTML = ""; 

    for (var i = 0; i < data.courses.length; i++) {
        courseBox.innerHTML += data.courses[i] + "<br>";
    }
}

