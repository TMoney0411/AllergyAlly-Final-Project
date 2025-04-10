<?php
session_start();
$password_error = '';
$username_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $username= htmlspecialchars(trim($_POST['username']));
    $createPassword=$_POST['createPassword'];

    $servername = 'localhost';
    $db_username = 'root';
    $db_password = '';
    $database = 'allergyally_final-project';
    $table = 'account_information';

    $conn = new mysqli($servername, $db_username, $db_password, $database);

    if($conn->connect_error)
    {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM account_information WHERE username = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt)
    {
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0)
      {
        $row = $result->fetch_assoc();
        if ($row !== null && $createPassword === $row['password'])
        {
          $_SESSION['user_id'] = $row['id'];
          $_SESSION['username'] = $row['username'];
          $_SESSION['name'] = $row['name'];
          $_SESSION['allergies'] = $row['allergies'];
          header("Location: product_options.php");
          exit;
        }

        else
        {
          $password_error = "<p class='error-message'>Password doesn't match! Please retype the password!</p>";
        }
      }
      else
      {
        $username_error = "<p class='error-message'>User not found! Please retype the username!</p>";
      }

      $stmt->close();
    }
    else
    {
      $username_error = "<p class='error-message'>Database error occurred!</p>";
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang = "en">
  <head>
    <meta charset="UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
  </head>
<body>
  <div class="login-container">
    <h1>AllergyAlly</h1>
    <h2>Login</h2>
    <label>If you don't have an account, click here: </label> 
    <input type="button" value="Create Account" onclick="window.location.href='create_account.php'"><br><br>
    <form id="form" method="post" action="login.php">
      <label for="username">Username (Must be an email address): </label> 
      <input type="text" id="username" name="username"><br><br>
      <label for="createPassword">Password: </label> 
      <input type="password" id="createPassword" name="createPassword">
      <button type="button" id="togglePasswordBtn">Show Password</button><br><br>
      <input type="submit" value="Submit">
    
      <?php
      if (isset($password_error))
      {
        echo $password_error;
      }
      if (isset($username_error))
      {
        echo $username_error;
      }
      ?>

      <?php include('disclaimer.php'); ?>
    </form>
  </div>

  <script>
    function togglePassword(passwordId, buttonId)
    {
      var passwordField = document.getElementById(passwordId);
      var button = document.getElementById(buttonId);

      if (passwordField.type === "password")
      {
        passwordField.type = "text";
        button.innerText = "Hide Password";
      }
      else
      {
        passwordField.type = "password";
        button.innerText = "Show Password";
      }
    }

    document.addEventListener('DOMContentLoaded', function()
    {
      document.getElementById('togglePasswordBtn').addEventListener('click', function()
      {
        togglePassword('createPassword', 'togglePasswordBtn');
      });
    });
  </script>
</body>
</html>
