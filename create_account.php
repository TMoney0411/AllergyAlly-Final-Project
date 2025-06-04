<?php
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  $name = htmlspecialchars(trim($_POST['name']));
  $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_EMAIL);
  $createPassword = $_POST['createPassword'];
  $retypePassword = $_POST['retypePassword'];

  $peanuts_severity = htmlspecialchars(trim($_POST['peanuts_severity'] ?? ''));
  $treenuts_severity = htmlspecialchars(trim($_POST['treenuts_severity'] ?? ''));
  $seeds_severity = htmlspecialchars(trim($_POST['seeds_severity'] ?? ''));
  $shellfish_severity = htmlspecialchars(trim($_POST['shellfish_severity'] ?? ''));
  $wheat_severity = htmlspecialchars(trim($_POST['wheat_severity'] ?? ''));
  $milk_severity = htmlspecialchars(trim($_POST['milk_severity'] ?? ''));
  $eggs_severity = htmlspecialchars(trim($_POST['eggs_severity'] ?? ''));
  $soybeans_severity = htmlspecialchars(trim($_POST['soybeans_severity'] ?? ''));
  $sesame_severity = htmlspecialchars(trim($_POST['sesame_severity'] ?? ''));
  $mangos_severity = htmlspecialchars(trim($_POST['mangos_severity'] ?? ''));
  

  $full_allergy_description = "";

  if (!empty($peanuts_severity) && is_numeric($peanuts_severity)) $full_allergy_description .= "Peanuts: " . (int)$peanuts_severity . "\n";
  if (!empty($treenuts_severity) && is_numeric($treenuts_severity)) $full_allergy_description .= "Treenuts: " . (int)$treenuts_severity . "\n";
  if (!empty($seeds_severity) && is_numeric($seeds_severity)) $full_allergy_description .= "Seeds: " . (int)$seeds_severity . "\n";
  if (!empty($shellfish_severity) && is_numeric($shellfish_severity)) $full_allergy_description .= "Shellfish: " . (int)$shellfish_severity . "\n";
  if (!empty($wheat_severity) && is_numeric($wheat_severity)) $full_allergy_description .= "Wheat: " . (int)$wheat_severity . "\n";
  if (!empty($milk_severity) && is_numeric($milk_severity)) $full_allergy_description .= "Milk: " . (int)$milk_severity . "\n";
  if (!empty($eggs_severity) && is_numeric($eggs_severity)) $full_allergy_description .= "Eggs: " . (int)$eggs_severity . "\n";
  if (!empty($soybeans_severity) && is_numeric($soybeans_severity)) $full_allergy_description .= "Soybeans: " . (int)$soybeans_severity . "\n";
  if (!empty($sesame_severity) && is_numeric($sesame_severity)) $full_allergy_description .= "Sesame: " . (int)$sesame_severity . "\n";
  if (!empty($mangos_severity) && is_numeric($mangos_severity)) $full_allergy_description .= "Mangos: " . (int)$mangos_severity . "\n";

  $full_allergy_description = rtrim($full_allergy_description, "\n");

  $servername = 'localhost';
  $db_username = 'root';
  $db_password = '';
  $database = 'allergyally_final-project';
  $table = 'account_information';

  
  $conn = new mysqli($servername, $db_username, $db_password, $database);

  if($conn->connect_error)
  {
    error_log("Connection failed: " . $conn->connect_error);
    $message = "<p class='error-message'>Database connection error. Please try again later.</p>";
  }
  else
  {
    if (!filter_var($username, FILTER_VALIDATE_EMAIL))
    {
      $message = "<p class='error-message'>Invalid email format! Please enter a valid email address!</p>";
    }
    else
    {
      $check_sql = "SELECT username FROM $table WHERE username = ?";
      $checkStmt = $conn->prepare($check_sql);
      if ($checkStmt)
      {
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0)
        {
          $message = "<div id='usernameTaken'>USERNAME_TAKEN</div>";
        }
        else
        {
          if (empty($name) || empty($createPassword) || empty($retypePassword))
          {
            $message = "<p class='error-message'>Please fill out all required fields (Name, Create Password, Retype Password)!</p>";
          }
          else if ($createPassword != $retypePassword)
          {
            $message = "<p class='error-message'>Passwords do not match! Please retype the password!</p>";
          }
          else if (strlen($createPassword) < 8)
          {
            $message = "<p class='error-message'>Password must be at least 8 characters long!</p>";
          }
          else if (strlen($name) > 320)
          {
            $message = "<p class='error-message'>Name must be at most 320 characters long!</p>";
          }
          else
          {
            $selected_allergies = isset($_POST['allergies']) ? (array)$_POST['allergies'] : [];
            $has_severity_issue = false;

            foreach($selected_allergies as $allergy_name)
            {
              $severity_key = strtolower($allergy_name) . '_severity';
              if (isset($_POST[$severity_key]) && !empty($_POST[$severity_key]))
              {
                $severity_val = htmlspecialchars(trim($_POST[$severity_key]));
                if (!is_numeric($severity_val) || $severity_val < 1 || $severity_val > 10)
                {
                  $has_severity_issue = true;
                  break;
                }
              }
              else
              {
                $has_severity_issue = true;
                break;
              }
            }

            if ($has_severity_issue)
            {
              $message = "<p class='error-message'>Please provide a valid severity (1-10) for all checked allergies!</p>";
            }
            else
            {
              if (!empty($selected_allergies))
              {
                $allergies_str = implode(", ", array_map('htmlspecialchars', $selected_allergies));
              }
              else
              {
                $allergies_str = "";
              }

              $sql = "INSERT INTO account_information (name, username, password, allergies, severity_symptoms)
                        VALUES (?, ?, ?, ?, ?)";
              $stmt = $conn->prepare($sql);
              if ($stmt)
              {
                $stmt->bind_param("sssss", $name, $username, $createPassword, $allergies_str, $full_allergy_description);
                if ($stmt->execute())
                {
                  $message = "<p class='success-message'>Congratulations! You have created an account! Please click the Login button at the top of the screen!";
                }
                else
                {
                  error_log("Database query failed: " . $stmt->error);
                  $message = "<p class='error-message'>Error creating account! Please try again! Check the console for specific error!</p>";
                }
              }
              else
              {
                error_log("Database prepare failed: " . $conn->error);
                $message = "<p class='error-message'>Internal server error! Please try again later! Check the console for specific error!</p>";
              }
            }
            if ($stmt) $stmt->close();
          }
        }
        $checkStmt->close();
      }
      else
      {
        error_log("Database prepare failed for check: " . $conn->error);
        $message = "<p class='error-message'>Internal server error! Please try again later! Check the console for specific error!</p>";
      }
    }
  }

  if ($conn)
  {
    $conn->close();
  }
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
      <div class="create-account-container">
      <img src="allergyally_logo.png" alt="AllergyAlly Logo" class="logo">
      <h2>Create Account</h2>
      <input type="button" value="Login" class="login-button" onclick="window.location.href='login.php'"><br><br>
    <form id="form" method="post">
        <label for="name">Name: </label> <input type="text" id="name" name="name" autocomplete="name"><br><br>
        <label for="username">Username (Must be an email address): </label> <input type="text" id="username" name="username" autocomplete="email"><br><br>
        <div id="usernameError" class="error-message"></div><br>

        <label for="createPassword">Create Password: </label> <input type="password" id="createPassword" name="createPassword" autocomplete="new-password">
        <button type="button" id="togglePasswordBtn1">Show Password</button><br><br>

        <label for="retypePassword">Retype Password: </label> <input type="password" id="retypePassword" name="retypePassword" autocomplete="new-password">
        <button type="button" id="togglePasswordBtn2">Show Password</button><br><br>

        <fieldset>
          <legend>Please check the box and/or boxes below that describes your allergy: </legend>

          <div style="display: flex; align-items: flex-start;">
            <div style="flex: 1;">
              <input type="checkbox" id="nuts1" name="allergies[]" value="peanuts"> <label for="nuts1">Peanuts</label><br>
              <input type="checkbox" id="nuts2" name="allergies[]" value="treenuts"> <label for="nuts2">Treenuts</label>
              <input type="checkbox" id="nuts3" name="allergies[]" value="seeds"> <label for="nuts3">Seeds</label><br>
              <input type="checkbox" id="shellfish" name="allergies[]" value="shellfish"> <label for="shellfish">Shellfish</label>
              <input type="checkbox" id="wheat" name="allergies[]" value="wheat"> <label for="wheat">Wheat</label>
              <input type="checkbox" id="milk" name="allergies[]" value="milk"> <label for="milk">Milk</label>
              <input type="checkbox" id="eggs" name="allergies[]" value="eggs"> <label for="eggs">Eggs</label><br>
              <input type="checkbox" id="soybeans" name="allergies[]" value="soybeans"> <label for="soybeans">Soybeans</label><br>
              <input type="checkbox" id="sesame" name="allergies[]" value="sesame"> <label for="sesame">Sesame</label>
              <input type="checkbox" id="mangos" name="allergies[]" value="mangos"> <label for="mangos">Mangos</label><br><br>
              <label for="other">Other (If stating more than one allergy, use a comma to separate them): </label> <input type="text" id="other" name="other">
            </div>

            <div style="margin-left: 20px; display: flex; flex-direction: column;">
              <button type="button" id="selectAllAllergies">Select All Allergies</button><br>
              <button type="button" id="deselectAllAllergies">Deselect All Allergies</button>
            </div>
          </div>
        </fieldset><br><br>
        <br>
        <fieldset>
          <legend>Please specify the severity of your relevent allergy or allergies (1 - Not severe; 5 - Kind of severe; 10 - Very severe):</legend>
          <div>
            <label for="peanuts_severity">Peanuts:</label>
            <input type="number" id="peanuts_severity" name="peanuts_severity" min="1" max="10"><br><br>
          </div>
          <div>
            <label for="treenuts_severity">Treenuts:</label>
            <input type="number" id="treenuts_severity" name="treenuts_severity" min="1" max="10"><br><br>
          </div>
          <div>
            <label for="seeds_severity">Seeds:</label>
            <input type="number" id="seeds_severity" name="seeds_severity" min="1" max="10"><br><br>
          </div>
          <div>
            <label for="shellfish_severity">Shellfish:</label>
            <input type="number" id="shellfish_severity" name="shellfish_severity" min="1" max="10"><br><br>
          </div>
          <div>
            <label for="wheat_severity">Wheat:</label>
            <input type="number" id="wheat_severity" name="wheat_severity" min="1" max="10"><br><br>
          </div>
          <div>
            <label for="milk_severity">Milk:</label>
            <input type="number" id="milk_severity" name="milk_severity" min="1" max="10"><br><br>
          </div>
          <div>
            <label for="eggs_severity">Eggs:</label>
            <input type="number" id="eggs_severity" name="eggs_severity" min="1" max="10"><br><br>
          </div>
          <div>
            <label for="soybeans_severity">Soybeans:</label>
            <input type="number" id="soybeans_severity" name="soybeans_severity" min="1" max="10"><br><br>
          </div>
          <div>
            <label for="sesame_severity">Sesame:</label>
            <input type="number" id="sesame_severity" name="sesame_severity" min="1" max="10"><br><br>
          </div>
          <div>
            <label for="mangos_severity">Mangos:</label>
            <input type="number" id="mangos_severity" name="mangos_severity" min="1" max="10"><br>
          </div>
        </fieldset><br>

        <p id="formError" class="error-message"></p> 
        <button type="submit">Submit</button><br><br>

        <div id="allergySeverityError" class="error-message"></div><br> 

        <?php include('disclaimer.php'); ?>
    </form>
    <div id="messageContainer"><?php echo isset($message) ? $message : ''; ?></div>
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
      document.getElementById('selectAllAllergies').addEventListener('click', function()
      {
        const checkboxes = document.querySelectorAll('input[name="allergies[]"]');

        checkboxes.forEach(checkbox =>
        {
          checkbox.checked = true;
        });
      });

      document.getElementById('deselectAllAllergies').addEventListener('click', function()
      {
        const checkboxes = document.querySelectorAll('input[name="allergies[]"]');

        checkboxes.forEach(checkbox =>
        {
          checkbox.checked = false;
        });
      });
      const form = document.getElementById('form');
      const submitBtn = form.querySelector('button[type="submit"]');

      document.getElementById('togglePasswordBtn1').addEventListener('click', function()
      {
        togglePassword('createPassword', 'togglePasswordBtn1');
      });

      document.getElementById('togglePasswordBtn2').addEventListener('click', function()
      {
        togglePassword('retypePassword', 'togglePasswordBtn2');
      });

      form.addEventListener("submit", function(event)
      {
        event.preventDefault();

        const name = document.getElementById("name").value;
        const username = document.getElementById("username").value;
        const createPassword = form.createPassword.value;
        const retypePassword = form.retypePassword.value;
        const formErrorDiv = document.getElementById("formError");
        const usernameErrorDiv = document.getElementById('usernameError');
        const allergySeverityErrorDiv = document.getElementById('allergySeverityError');
        const messageContainer = document.getElementById("messageContainer");

        let firstErrorElement = null;
        
        formErrorDiv.textContent = '';
        formErrorDiv.style.display = 'none';
        usernameErrorDiv.style.display = 'none';
        usernameErrorDiv.textContent = '';
        allergySeverityErrorDiv.textContent = '';
        allergySeverityErrorDiv.style.display = 'none';
        messageContainer.innerHTML = '';

        if (!name)
        {
          formErrorDiv.textContent = 'Please enter your Name!';
          formErrorDiv.style.display = 'block';
          firstErrorElement = formErrorDiv;
        }
        else if (name.length > 50)
        {
          event.preventDefault();
          formErrorDiv.textContent = "Name must be at most 50 characters long!"
          formErrorDiv.style.display = 'block';
          firstErrorElement = formErrorDiv;
        }

        if (!username)
        {
          usernameErrorDiv.style.display = 'block';
          usernameErrorDiv.textContent = 'Please enter your username/email!';
          firstErrorElement = usernameErrorDiv;
        }
        else if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(username))
        {
          usernameErrorDiv.style.display = 'block';
          usernameErrorDiv.textContent = "Invalid email format! Please enter a valid email address!";
          firstErrorElement = usernameErrorDiv;
        } 
        else if (username.length > 320) 
        {
          usernameErrorDiv.style.display = 'block';
          usernameErrorDiv.textContent = "Username (email) must be at most 320 characters long!";
          firstErrorElement = usernameErrorDiv;
        }
        else
        {
          const parts = username.split('@');
          if (parts.length !== 2)
          {
            usernameErrorDiv.style.display = 'block';
            usernameErrorDiv.textContent = 'Invalid email format!';
            firstErrorElement = usernameErrorDiv;
          }
          else
          {
            const [usernamePart, domainPart] = parts;
            if (usernamePart.length > 64)
            {
              usernameErrorDiv.style.display = 'block';
              usernameErrorDiv.textContent = 'The part before the @ symbol must be at most 64 characters!';
              firstErrorElement = usernameErrorDiv;
            }
            if (domainPart.length > 255)
            {
              usernameErrorDiv.style.display = 'block';
              usernameErrorDiv.textContent = 'The part after the @ symbol must be at most 255 characters!';
              firstErrorElement = usernameErrorDiv;
            }
          }
        }

        if (!firstErrorElement)
        {
          if (!createPassword)
          {
            formErrorDiv.textContent = 'Please create a password!';
            formErrorDiv.style.display = 'block';
            if (!firstErrorElement) firstErrorElement = usernameErrorDiv;
          }
          else if (createPassword.length < 8)
          {
            event.preventDefault();
            formErrorDiv.textContent = "Password much be at least 8 characters long!"
            formErrorDiv.style.display = 'block';
            if (!firstErrorElement) firstErrorElement = usernameErrorDiv;
          }
        }

        if (!firstErrorElement)
        {
          if (!retypePassword)
          {
            formErrorDiv.textContent = 'Please retype your password!';
            formErrorDiv.style.display = 'block';
            if (!firstErrorElement) firstErrorElement = usernameErrorDiv;
          }
          else if (createPassword !== retypePassword)
          {
            event.preventDefault();
            formErrorDiv.textContent = "Passwords do not match! Please retype the password!"
            formErrorDiv.style.display = 'block';
            if (!firstErrorElement) firstErrorElement = usernameErrorDiv;
          }
        }

        if (!firstErrorElement)
        {
          const allergyCheckboxes = form.querySelectorAll('input[name="allergies[]"]:checked');
          let hasUnspecifiedSeverity = false;

          allergyCheckboxes.forEach(checkbox =>
          {
            const allergyValue = checkbox.value;
            const severityInputId = allergyValue + '_severity';
            const severityInput = document.getElementById(severityInputId);

            if (severityInput)
            {
              const severityValue = severityInput.value.trim();
              if (severityValue === '' || isNaN(severityValue) || parseInt(severityValue) < 1 || parseInt(severityValue) > 10)
              {
                hasUnspecifiedSeverity = true;
              }
            }
            else
            {
              hasUnspecifiedSeverity = true;
            }  
          });

          if (hasUnspecifiedSeverity)
          {
            allergySeverityErrorDiv.textContent = 'Please provide a valid severity (1-10) for all checked allergies!';
            allergySeverityErrorDiv.style.display = 'block';
            return;
          }
        }
    
        if (firstErrorElement)
        {
          firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
          return;
        }


        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);

        fetch('create_account.php',
        {
          method: 'POST',
          body: formData,
        })
        .then(response => response.text())
        .then(data =>
        {
          if (submitBtn) submitBtn.disabled = false;

          if (data.includes("USERNAME_TAKEN"))
          {
            usernameErrorDiv.style.display = 'block';
            usernameErrorDiv.textContent = "Your username is the same as another user! Please change it!";
            usernameErrorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() =>
            {
              usernameErrorDiv.textContent = '';
              usernameErrorDiv.style.display = 'none';
            }, 5000);
            return;
          }

          const tempElement = document.createElement('div');
          tempElement.innerHTML = data;

          const successMessageElement = tempElement.querySelector('.success-message');
          const errorMessageElement = tempElement.querySelector('.error-message');

          if (successMessageElement)
          {
            messageContainer.innerHTML = `<p class="success-message">${successMessageElement.textContent}</p>`;
            form.reset();
            messageContainer.scrollIntoView
            ({ 
              behavior: 'smooth', 
              block: 'center' 
            });
            setTimeout(() =>
            {
              messageContainer.innerHTML = '';
            }, 5000);
          }
          else if (errorMessageElement)
          {
            messageContainer.innerHTML = errorMessageElement.outerHTML;
            messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() =>
            {
              messageContainer.innerHTML = '';
            }, 5000);
            if (firstErrorElement)
            {
              firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          }
          else
          {
            messageContainer.innerHTML = `<p class="error-message">An unexpected error occured! Please check the console for error details!</p>`;
            messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() =>
            {
              messageContainer.innerHTML = '';
            }, 5000);
            if (firstErrorElement)
            {
              firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          }
        })
        .catch(error =>
        {
          if (submitBtn) submitBtn.disabled = false;
          console.error('Error:', error);
          formErrorDiv.textContent = "An error occured during submission! Check console for errors!";
          formErrorDiv.style.display = 'block';
          
          firstErrorElement.scrollIntoView
          ({ 
            behavior: 'smooth', 
            block: 'center' 
          });
        });
      });
    });
  </script>
</body>
</html>
