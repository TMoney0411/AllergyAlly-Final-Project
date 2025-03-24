<?php
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $name=$_POST['name'];
    $username=$_POST['username'];
    $createPassword=$_POST['createPassword'];
    $retypePassword=$_POST['retypePassword'];
    $allergies=isset($_POST['allergies']) ? $_POST ['allergies'] : array();
    $other=$_POST['other'];
    $severity_symptoms = $_POST['severity_symptoms'];
    
    if (!empty($allergies))
    {
      $allergies_str = implode(" ", $allergies);
    }
    else
    {
      $allergies_str = "";
    }



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

    $sql = "INSERT INTO account_information (name, username, password, allergies, other, severity_symptoms)
    VALUES ('$name', '$username', '$createPassword', '$allergies_str', '$other', '$severity_symptoms')";

    if ($conn->query($sql) === TRUE) 
    {
        $message = "<p class='success-message'>Congratulations! You have created an account! Please click the Login button at the top of the screen!";
    } 
    else 
    {
        $message = "<p>Error: " . $sql . "<br>" . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html lang = "en">
  <head>
    <meta charset="UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
  
    <center>
    <body>
      <div class="create-account-container">
      <h1>AllergyAlly</h1>
      <h2>Create Account</h2>
      <input type="button" value="Login" class="login-button" onclick="window.location.href='login.php'"><br><br>
      <form id="form" method="post" action="create_account.php">
      <label>Name: </label> <input type="text" id="name" name="name"><br><br>
      <label>Username(Must be an email address): </label> <input type="text" id="username" name="username"><br><br>

      <label>Create Password: </label> <input type="password" id="createPassword" name="createPassword">
      <button type="button" id="togglePasswordBtn1">Show Password</button><br><br>

      <label>Retype Password: </label> <input type="password" id="retypePassword" name="retypePassword">
      <button type="button" id="togglePasswordBtn2">Show Password</button><br><br>

      <fieldset>
        <legend>Please check the box and/or boxes below that describes your allergy: </legend>
        <input type="checkbox" id="nuts1" name="allergies[]" value="peanuts"> <label for="peanuts">Peanuts</label>
        <input type="checkbox" id="nuts2" name="allergies[]" value="treenuts"> <label for="treenuts">Treenuts</label>
        <input type="checkbox" id="nuts3" name="allergies[]" value="seeds"> <label for="seeds">Seeds</label>
        <input type="checkbox" id="shellfish" name="allergies[]" value="shellfish"> <label for="shellfish">Shellfish</label>
        <input type="checkbox" id="wheat" name="allergies[]" value="wheat"> <label for="wheat">Wheat</label>
        <input type="checkbox" id="milk" name="allergies[]" value="milk"> <label for="milk">Milk</label>
        <input type="checkbox" id="eggs" name="allergies[]" value="eggs"> <label for="eggs">Eggs</label><br>
        <input type="checkbox" id="soybeans" name="allergies[]" value="soybeans"> <label for="soybeans">Soybeans</label>
        <input type="checkbox" id="sesame" name="allergies[]" value="sesame"> <label for="sesame">Sesame</label>
        <input type="checkbox" id="mangos" name="allergies[]" value="mangos"> <label for="mangos">Mangos</label><br>
        <label> Other: </label> <input type="text" id="other" name="other">
      </fieldset><br><br>

      <fieldset>
        <legend>Please specify about your allergy, specifically cross contamination, severity, and symptoms. Feel free to explain further beyond the numbers (1 - Not severe; 5 - Kind of severe; 10 - Very severe)</legend>
        <label>Allergy Description: </label> <input type="text" id="severity_symptoms" name="severity_symptoms"><br>
      </fieldset><br>
      <p id="passwordError"></p>
      <button type="submit">Submit</button><br><br>
      </form>
      <div id="messageContainer"><?php echo isset($message) ? $message : ''; ?></div>

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
          document.getElementById('togglePasswordBtn1').addEventListener('click', function()
          {
            togglePassword('createPassword', 'togglePasswordBtn1');
          });

          document.getElementById('togglePasswordBtn2').addEventListener('click', function()
          {
            togglePassword('retypePassword', 'togglePasswordBtn2');
          });

          document.getElementById('form').addEventListener("submit", function(event)
          {
            event.preventDefault();

            var name = document.getElementById("name").value;
            var username = document.getElementById("username").value;
            var createPassword = document.getElementById("createPassword").value;
            var retypePassword = document.getElementById("retypePassword").value;
            var severitySymptoms = document.getElementById("severity_symptoms").value;
            var errorMessage = document.getElementById("passwordError");
            var messageContainer = document.getElementById("messageContainer");

            if (name == "" || username == "" || createPassword == "" || retypePassword == "" || severitySymptoms == "")
            {
              event.preventDefault();
              errorMessage.textContent = "Please fill out all required fields!"
              return false;
            }

            if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(username))
            {
              event.preventDefault();
              errorMessage.textContent = "Invalid email format! Please enter a valid email address!";
              return false;
            } 
            else
            {
              var parts = username.split('@');
              if (parts.length === 2)
              {
                var usernamePart = parts[0];
                var domainPart = parts[1];

                if (usernamePart.length > 64)
                {
                  event.preventDefault();
                  errorMessage.textContent = "The part before the @ symbol must be at most 64 characters!";
                  return false;
                }

                else if (domainPart.length > 255)
                {
                  event.preventDefault();
                  errorMessage.textContent = "The part after the @ symbol must be at most 255 characters!";
                  return false;
                }
              }
            
              else
              {
                event.preventDefault();
                errorMessage.textContent = "Invalid email format!";
                return false;
              }
            }

            if (name.length > 320)
            {
              event.preventDefault();
              errorMessage.textContent = "Username must be at most 320 characters long!"
              return false;
            } 

            if (createPassword.length < 8)
            {
              event.preventDefault();
              errorMessage.textContent = "Password much be at least 8 characters long!"
              return false;
            }

            if (createPassword !== retypePassword)
            {
              event.preventDefault();
              errorMessage.textContent = "Passwords do not match! Please retype the password!"
              return false;
            }

            if (errorMessage.textContent === "")
            {
              const formData = new FormData(document.getElementById('form'));
              fetch('create_account.php',
              {
                method: 'POST',
                body: formData,
              })
              .then(response => response.text())
              .then(data =>
              {
                const tempElement = document.createElement('div');
                tempElement.innerHTML = data;

                const successMessageElement = tempElement.querySelector('.success-message');

                if (successMessageElement)
                {
                  messageContainer.innerHTML = `<p class="success-message">${successMessageElement.textContent}</p>`;
                }
                else
                {
                  const errorMessageElement = tempElement.querySelector('.error-message');

                  if (errorMessageElement)
                  {
                    messageContainer.innerHTML = `<p class="error-message">${errorMessageElement.textContent}</p>`;
                  }

                  else
                  {
                    messageContainer.innerHTML = `<p class="error-message">An unexpected error occured! Please check the console for error details!</p>`;
                  }
                }
              })
              .catch(error =>
              {
                console.error('Error:', error);
                errorMessage.textContent = "An error occured during submission! Check console for errors!";
              })
            }
          });
        });
      </script>
    </body>
  </head>
</center>
</html>
