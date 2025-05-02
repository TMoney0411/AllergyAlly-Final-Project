<?php
session_start();

if (!isset($_SESSION['name']))
{
  header("Location: login.php");
  exit;
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang = "en">
<head>
    <meta charset="UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" id="mainContainer">
        <div class="add-allergy-container">
            <h1>Add New Allergies</h1>
            <p class="input-description">Please enter your new allergy or allergies in the box below(If adding more than one allergy, separate them by a comma)</p>
            <div class="add-allergy-input-container">
                <div class="add-allergy-input">
                    <input type="text" id="newAllergyInput" autocomplete="off">
                </div>
                <div class="suggestions-box">
                    <ul id="allergySuggestionsList">
                    </ul>
                </div>
            </div>
            <div class="added-allergies">
                <ul id="addedAllergiesList">
                </ul>
            </div>
            <button id="saveNewAllergies">Save New Allergies</button>
            <?php include('disclaimer.php'); ?>
        </div>
    </div>

    <script>
        const newAllergyInput = document.getElementById('newAllergyInput');
        const allergySuggestionsList = document.getElementById('allergySuggestionsList');
        const allergySuggestionsBox = document.querySelector('.suggestions-box');
        const addedAllergiesList = document.getElementById('addedAllergiesList');
        const saveNewAllergiesButton = document.getElementById('saveNewAllergies');
        const canAddAllergyMessage = document.createElement('p');
        canAddAllergyMessage.style.color = 'red';
        canAddAllergyMessage.textContent = "Can't add allergy. Already added.";
        canAddAllergyMessage.style.display = 'none';

        const allKnownAllergies = ["Dairy", "Fish", "Mustard", "Celery", "Avocado", "Kiwi", "Latex", "Corn", "Coconut", "Rice", "Oats", "Yeast"];
        const initialAllergies = getInitialAllergies();
        let currentAddedAllergies = [];
        
        newAllergyInput.parentNode.insertBefore(canAddAllergyMessage, newAllergyInput.nextSibling);

        newAllergyInput.addEventListener('input', function()
        {
            const query = this.value.toLowerCase();
            const filteredSuggestions = allKnownAllergies.filter(allergy =>
                allergy.toLowerCase().startsWith(query) && query.length > 0 &&
                !initialAllergies.map(a => a.toLowerCase()).includes(allergy) &&
                !currentAddedAllergies.map(a => a.toLowerCase()).includes(allergy)
            );
            displayAllergySuggestions(filteredSuggestions);
            canAddAllergyMessage.style.display = 'none';
        });

        function getInitialAllergies()
        {
            const checkboxes = document.querySelectorAll('input[name="allergies[]"]:checked');
            const allergies = [];
            checkboxes.forEach(box =>
            {
                allergies.push(box.value);
            });
            return allergies;
        }

        function displayAllergySuggestions(results)
        {
            allergySuggestionsList.innerHTML = '';
            if (results.length > 0)
            {
                allergySuggestionsBox.style.display = 'block';
                results.forEach(result =>
                {
                    const listItem = document.createElement('li');
                    listItem.textContent = result;
                    listItem.addEventListener('click', function()
                    {
                        const selectedAllergy = this.textContent;
                        if (initialAllergies.map(a => a.toLowerCase()).includes(selectedAllergy.toLowerCase()) || currentAddedAllergies.map(a => a.toLowerCase()).includes(selectedAllergy.toLowerCase()))
                        {
                            canAddAllergyMessage.style.display = 'block';
                        }
                        else
                        {
                            addAllergyToAddedList(selectedAllergy);
                            newAllergyInput.value = '';
                            allergySuggestionsBox.style.display = 'none';
                            canAddAllergyMessage.style.display = 'none';
                        }
                    });
                    allergySuggestionsList.appendChild(listItem);
                });
            }
            else
            {
                allergySuggestionsBox.style.display = 'none';
            }
        }

        function addAllergyToAddedList(allergy)
        {
            if(!currentAddedAllergies.includes(allergy))
            {
                currentAddedAllergies.push(allergy);
                const listItem = document.createElement('li');
                listItem.textContent = allergy;
                addedAllergiesList.appendChild(listItem);
                canAddAllergyMessage.style.display = 'none';
            }
            else
            {
                canAddAllergyMessage.style.display = 'block';
            }
        }

        document.addEventListener('click', function(event)
        {
            if (!event.target.closest('.add-allergy-input-container'))
            {
                allergySuggestionsBox.style.display = 'none';
                canAddAllergyMessage.style.display = 'none';
            }
        });

        saveNewAllergiesButton.addEventListener('click', function()
        {
            fetch('add_allergies.php',
            {
                method: 'POST',
                headers: 
                {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ newAllergies: currentAddedAllergies }),
            })
            .then(response => response.json())
            .then(data =>
            {
                const addAllergyContainer = document.querySelector('.add-allergy-container');
                const statusMessage = addAllergyContainer.querySelector('.save-status-message');
                if (statusMessage)
                {
                    statusMessage.remove();
                }

                if (data.success)
                {
                    const successMessage = document.createElement('p');
                    successMessage.textContent = 'Saved successfully!'
                    successMessage.style.color = 'green';
                    successMessage.classList.add('success-status-message');
                    addAllergyContainer.appendChild(successMessage);

                    currentAddedAllergies = [];
                    addedAllergiesList.innerHTML = '';
                    newAllergyInput.value = '';
                    allergySuggestionsBox.style.display = 'none';
                    canAddAllergyMessage.style.display = 'none';
                }
                else
                {
                    console.error('Error saving allergies:', data.error);
                    const errorMessage = document.createElement('p');
                    errorMessage.textContent = 'Error saving allergies! Check the console for errors!';
                    errorMessgae.style.color = 'red';
                    errorMessage.classList.add('error-status-message');
                    addAllergyContainer.appendChild(errorMessage);
                }
            })
            .catch(error =>
            {
                console.error('Network error', error);
                const addAllergyContainer = document.querySelector('.add-allergy-container');
                const errorMessage = document.createElement('p');
                errorMessage.textContent = 'Network error! Check the console for details!';
                errorMessgae.style.color = 'red';
                errorMessage.classList.add('error-status-message');
                addAllergyContainer.appendChild(errorMessage);

            });
        });
    </script>
</body>
</html>
