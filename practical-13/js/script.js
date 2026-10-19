document.addEventListener("DOMContentLoaded", function () {
    var roleSelect = document.getElementById("role");
    var userLabel = document.getElementById("userLabel");

    function updateLabel() {
        userLabel.textContent = roleSelect.value === "admin" ? "Admin Name" : "Student ID";
    }

    function setupRoleLabel() {
        if (!roleSelect || !userLabel) {
            return;
        }
        roleSelect.addEventListener("change", updateLabel);
        updateLabel();
    }

    function setupRegisterForm() {
        var form = document.getElementById("registerForm");
        var errorBox = document.getElementById("clientErrors");
        if (!form || !errorBox) {
            return;
        }

        function showErrors(list) {
            errorBox.innerHTML = "";

            var title = document.createElement("strong");
            title.textContent = "Please fix the following:";
            errorBox.appendChild(title);

            var ul = document.createElement("ul");
            for (var i = 0; i < list.length; i++) {
                var li = document.createElement("li");
                li.textContent = list[i];
                ul.appendChild(li);
            }
            errorBox.appendChild(ul);

            errorBox.hidden = false;
        }

        form.addEventListener("submit", function (event) {
            var errors = [];

            var user = document.getElementById("user").value.trim();
            var password = document.getElementById("password").value;
            var role = roleSelect.value;

            if (role === "student" && !/^2\d[A-Za-z]{2,4}\d{3}$/.test(user)) {
                errors.push("Invalid Student ID: it should look like 25DCE033.");
            }

            if (role === "admin" && !/^[A-Za-z0-9]{4,20}$/.test(user)) {
                errors.push("Invalid Admin Name: use 4 to 20 letters or numbers.");
            }

            if (password.length < 8 || password.length > 64 || !/[A-Za-z]/.test(password) || !/\d/.test(password)) {
                errors.push("Invalid Password: use 8 to 64 characters with at least one letter and one number.");
            }

            if (errors.length > 0) {
                event.preventDefault();
                showErrors(errors);
            }
        });
    }

    function setupLoginRedirect() {
        var message = document.body.getAttribute("data-login-message");
        var target = document.body.getAttribute("data-login-redirect");
        if (!message || !target) {
            return;
        }

        setTimeout(function () {
            alert(message);
            window.location.href = target;
        }, 100);
    }

    setupRoleLabel();
    setupRegisterForm();
    setupLoginRedirect();
});