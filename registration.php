<?php
// Variables
$name = $email = $password = $confirm_password = "";
$nameErr = $emailErr = $passwordErr = $confirmErr = "";
$successMsg = "";
$jsonFile = "users.json";

// When form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ====== VALIDATION ======

    // Name validation
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = htmlspecialchars($_POST["name"]);
    }

    // Email validation
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = htmlspecialchars($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    // Password validation
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = $_POST["password"];

        if (strlen($password) < 8) {
            $passwordErr = "Password must be at least 8 characters";
        } elseif (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
            $passwordErr = "Password must contain at least one special character";
        }
    }

    // Confirm password validation
    if (empty($_POST["confirm_password"])) {
        $confirmErr = "Confirm password is required";
    } else {
        $confirm_password = $_POST["confirm_password"];

        if ($password !== $confirm_password) {
            $confirmErr = "Passwords do not match";
        }
    }

    // ✅ Proceed if no validation errors
    if ($nameErr == "" && $emailErr == "" && $passwordErr == "" && $confirmErr == "") {

        // ✅ Create JSON file if not exists
        if (!file_exists($jsonFile)) {
            file_put_contents($jsonFile, json_encode([]));
        }

        $jsonData = file_get_contents($jsonFile);
        $usersArray = json_decode($jsonData, true);

        if (!is_array($usersArray)) {
            $usersArray = [];
        }

        // ✅ CHECK IF EMAIL ALREADY EXISTS
        foreach ($usersArray as $user) {
            if ($user["email"] === $email) {
                $emailErr = "This email is already registered!";
                break;
            }
        }

        // ✅ If email is unique, save user
        if ($emailErr == "") {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $newUser = [
                "name" => $name,
                "email" => $email,
                "password" => $hashedPassword
            ];

            $usersArray[] = $newUser;

            $updatedJson = json_encode($usersArray, JSON_PRETTY_PRINT);

            if (file_put_contents($jsonFile, $updatedJson) === false) {
                die("<div class='error'>Error writing to users.json file.</div>");
            }

            $successMsg = "Registration successful!";
            $name = $email = "";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP Registration System</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .form-box {
            width: 380px;
            padding: 25px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
        }

        input[type="text"],
        input[type="password"] {
            width: 95%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #0066ff;
            box-shadow: 0 0 3px rgba(0,102,255,0.5);
        }

        label {
            font-weight: bold;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            border: none;
            background-color: #0066ff;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #004bcc;
        }

        .error {
            color: red;
            font-size: 13px;
        }

        .success {
            color: green;
            font-size: 15px;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>

</head>
<body>

<div class="form-box">

    <h2>Register</h2>

    <?php if ($successMsg) { ?>
        <div class="success"><?php echo $successMsg; ?></div>
    <?php } ?>

    <form method="POST" action="">

        <!-- Name -->
        <label>Name:</label><br>
        <input type="text" name="name" value="<?php echo $name; ?>">
        <span class="error"><?php echo $nameErr; ?></span><br><br>

        <!-- Email -->
        <label>Email:</label><br>
        <input type="text" name="email" value="<?php echo $email; ?>">
        <span class="error"><?php echo $emailErr; ?></span><br><br>

        <!-- Password -->
        <label>Password:</label><br>
        <input type="password" name="password">
        <span class="error"><?php echo $passwordErr; ?></span><br><br>

        <!-- Confirm Password -->
        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password">
        <span class="error"><?php echo $confirmErr; ?></span><br><br>

        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>
