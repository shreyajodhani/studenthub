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