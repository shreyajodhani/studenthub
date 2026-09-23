<!DOCTYPE html>
<html>
    <head>
        <title>
            PHP practice
        </title> 
    </head>

    <body>
        <form method="POST">
            NAME:<br>
            <input type="text" name="name"><br><br>
            EMAIL:<br>
            <input type="email" name="email"><br><br>
            MOBILE NO:<br>
            <input type="tel" name="mobileno"><br><br>
            PASSWORD:<br>
            <input type="password" name="password"><br><br>
            CONFIRM PASSWORD:<br>
            <input type="password" name="confirmpassword"><br><br>
            <button type="submit">SUBMIT</button>

            <?php

            if (isset($_POST['name'])) {
            $name= $_POST['name'];
            $email= $_POST['email'];
            $mobileno= $_POST['mobileno'];
            $Password= $_POST['password'];

            $file = fopen("data.csv", "a");

            fwrite($file, $name . "," . $email . "\n");
            fclose($file);
            }
            ?>
    </body>    
</html>    