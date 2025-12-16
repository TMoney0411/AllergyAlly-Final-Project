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
    <form id="form" method="post" action="create_account.php">
        <label for="name">Name: </label> 
        <input type="text" id="name" name="name" autocomplete="name"><br><br>
        <p id="nameError" class="error-message" style="display:none;"></p>
        <br>
        <label for="username">Username (Must be an email address): </label> <input type="text" id="username" name="username" autocomplete="email"><br><br>
        <div id="usernameError" class="error-message" style="display:none;"></div><br>

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

      function fadeOutMessage(element, delay = 3000)
      {
        if (!element) return;

        setTimeout(() =>
        {
          element.style.transition = 'opacity 0.5s ease';
          element.style.opacity = '0';

          setTimeout(() =>
          {
            element.textContent = '';
            element.style.display = 'none';
            element.style.opacity = '1'; // reset for next time
            element.style.transition = '';
          }, 500);
        }, delay);
      }
   
      form.addEventListener("submit", function(event)
      {
        event.preventDefault();

        const name = document.getElementById("name").value;
        const nameErrorDiv = document.getElementById('nameError');
        const username = document.getElementById("username").value;
        const createPassword = form.createPassword.value;
        const retypePassword = form.retypePassword.value;
        const formErrorDiv = document.getElementById("formError");
        const usernameErrorDiv = document.getElementById('usernameError');
        const allergySeverityErrorDiv = document.getElementById('allergySeverityError');
        const messageContainer = document.getElementById("messageContainer");

        let firstErrorElement = null;
        
        nameErrorDiv.textContent = '';
        nameErrorDiv.style.display = 'none';
        formErrorDiv.textContent = '';
        formErrorDiv.style.display = 'none';
        usernameErrorDiv.style.display = 'none';
        usernameErrorDiv.textContent = '';
        allergySeverityErrorDiv.textContent = '';
        allergySeverityErrorDiv.style.display = 'none';
        messageContainer.innerHTML = '';

        if (!name)
        {
          nameErrorDiv.textContent = 'Please enter your Name!';
          nameErrorDiv.style.display = 'block';
          fadeOutMessage(nameErrorDiv);
          firstErrorElement = nameErrorDiv;
        }
        else if (name.length > 50)
        {
          nameErrorDiv.textContent = "Name must be at most 50 characters long!"
          nameErrorDiv.style.display = 'block';
          fadeOutMessage(nameErrorDiv);
          firstErrorElement = nameErrorDiv;
        }

        if (!username)
        {
          usernameErrorDiv.style.display = 'block';
          usernameErrorDiv.textContent = 'Please enter your username/email!';
          fadeOutMessage(usernameErrorDiv);
          firstErrorElement = formErrorDiv;
        }
        else if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(username))
        {
          usernameErrorDiv.style.display = 'block';
          usernameErrorDiv.textContent = "Invalid email format! Please enter a valid email address!";
          fadeOutMessage(usernameErrorDiv);
          firstErrorElement = formErrorDiv;
        } 
        else if (username.length > 320) 
        {
          usernameErrorDiv.style.display = 'block';
          usernameErrorDiv.textContent = "Username (email) must be at most 320 characters long!";
          fadeOutMessage(usernameErrorDiv);
          firstErrorElement = formErrorDiv;
        }
        else
        {
          const parts = username.split('@');
          if (parts.length !== 2)
          {
            usernameErrorDiv.style.display = 'block';
            usernameErrorDiv.textContent = 'Invalid email format!';
            fadeOutMessage(usernameErrorDiv);
            firstErrorElement = formErrorDiv;
          }
          else
          {
            const [usernamePart, domainPart] = parts;
            if (usernamePart.length > 64)
            {
              usernameErrorDiv.style.display = 'block';
              usernameErrorDiv.textContent = 'The part before the @ symbol must be at most 64 characters!';
              fadeOutMessage(usernameErrorDiv);
              firstErrorElement = usernameErrorDiv;
              
            }
            if (domainPart.length > 255)
            {
              usernameErrorDiv.style.display = 'block';
              usernameErrorDiv.textContent = 'The part after the @ symbol must be at most 255 characters!';
              fadeOutMessage(usernameErrorDiv);
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
            fadeOutMessage(formErrorDiv);
          }
          else if (createPassword.length < 8)
          {
            formErrorDiv.textContent = "Password much be at least 8 characters long!"
            formErrorDiv.style.display = 'block';
            if (!firstErrorElement) firstErrorElement = usernameErrorDiv;
            fadeOutMessage(formErrorDiv);
          }
        }

        if (!firstErrorElement)
        {
          if (!retypePassword)
          {
            formErrorDiv.textContent = 'Please retype your password!';
            formErrorDiv.style.display = 'block';
            if (!firstErrorElement) firstErrorElement = usernameErrorDiv;
            fadeOutMessage(formErrorDiv);
          }
          else if (createPassword !== retypePassword)
          {
            formErrorDiv.textContent = "Passwords do not match! Please retype the password!"
            formErrorDiv.style.display = 'block';
            if (!firstErrorElement) firstErrorElement = usernameErrorDiv;
            fadeOutMessage(formErrorDiv);
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
            fadeOutMessage(allergySeverityErrorDiv);
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
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;

            if (data.status === 'error' && data.code === 'USERNAME_TAKEN') 
            {
                usernameErrorDiv.style.display = 'block';
                usernameErrorDiv.textContent = data.message;
                usernameErrorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                fadeOutMessage(usernameErrorDiv);
                return;
            }

            if (data.status === 'error') 
            {
                messageContainer.innerHTML = `<p class="error-message">${data.message}</p>`;
                messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                fadeOutMessage(messageContainer);
                return;
            }

            if (data.status === 'success') 
            {
                messageContainer.innerHTML = `<p class="success-message">${data.message}</p>`;
                form.reset();
                messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                fadeOutMessage(messageContainer, 5000);
            }
        })
        .catch(error => {
            if (submitBtn) submitBtn.disabled = false;
            console.error(error);
            formErrorDiv.textContent = 'An error occurred during submission.';
            formErrorDiv.style.display = 'block';
        });
      });
    });
</script>
</body>
</html>
<!-- hi -->
