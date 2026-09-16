async function showData() {
   
    var response = await fetch('data.json');
    var rawText = await response.text();

    var students = JSON.parse(rawText);

    var output = document.getElementById("result");
    output.innerHTML = "";

    for (var i = 0; i < students.length; i++) {
        output.innerHTML += "<p><b>Student name: </b>" + students[i].name + "<br><b>ID </b>: " + students[i].rollno + "<br><b>Department: </b>" + students[i].branch + "</p>";
    }
}


