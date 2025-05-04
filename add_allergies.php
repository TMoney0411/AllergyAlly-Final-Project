<?php
session_start();

if (!isset($_SESSION['name']))
{
    if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
    {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        http_response_code(401);
    }
    else
    {
        header("Location: login.php");
    }
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
        document.addEventListener('DOMContentLoaded', function()
        {
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

            function updateCurrentAddedAllergies()
            {
                const inputText = newAllergyInput.value.trim();
                if (!inputText) return;

                const newEntries = inputText.split(',').map(item => item.trim()).filter(item => item !== '');

                currentAddedAllergies = [];

                newEntries.forEach(allergy =>
                {
                    const normalized = allergy.toLowerCase();
                    const alreadyExists = initialAllergies.map(a => a.toLowerCase()).includes(normalized) ||
                                          currentAddedAllergies.map(a => a.toLowerCase()).includes(normalized);

                    if (!alreadyExists)
                    {
                        currentAddedAllergies.push(allergy);
                    }
                });
            }

            function showConfirmationPopup()
            {
                if (currentAddedAllergies.length > 0)
                {
                    let formattedList = '';
                    const count = currentAddedAllergies.length;

                    if (count === 1)
                    {
                        formattedList = currentAddedAllergies[0];
                    }
                    else if (count === 2)
                    {
                        formattedList = `${currentAddedAllergies[0]} and ${currentAddedAllergies[1]}`;
                    }
                    else
                    {
                        formattedList = currentAddedAllergies.slice(0, -1).join(', ') + `, and ${currentAddedAllergies[count - 1]}`;
                    }

                    const message = `Would you like to add ${formattedList}?`;
                    document.getElementById('confirmationText').textContent = message;
                    document.getElementById('confirmationPopup').style.display = 'block';
                }
            }

            newAllergyInput.addEventListener('input', function()
            {
                const inputText = this.value;
                const allergyParts = inputText.split(',');
                const currentQuery = allergyParts[allergyParts.length - 1].trim().toLowerCase();
                if (currentQuery.length > 0)
                {
                    const filteredSuggestions = allKnownAllergies.filter(allergy =>
                    allergy.toLowerCase().includes(currentQuery) &&
                    !initialAllergies.map(a => a.toLowerCase()).includes(allergy) &&
                    !currentAddedAllergies.map(a => a.toLowerCase()).includes(allergy)
                );
                displayAllergySuggestions(filteredSuggestions, allergyParts);
                canAddAllergyMessage.style.display = 'none';
            }
            else
            {
                allergySuggestionsBox.style.display = 'none';
                canAddAllergyMessage.style.display = 'none';
            }
            });

            newAllergyInput.addEventListener('keypress', function(event)
            {
                if (event.key === 'Enter')
                {
                    event.preventDefault();
                    updateCurrentAddedAllergies();
                    showConfirmationPopup();
                }
            });

            saveNewAllergiesButton.addEventListener('click', function()
            {
                updateCurrentAddedAllergies();
                if (currentAddedAllergies.length === 0) return;
                showConfirmationPopup();
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

            function displayAllergySuggestions(results, allergyParts)
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
                            allergyParts[allergyParts.length - 1] = result;
                            newAllergyInput.value = allergyParts.map(a => a.trim()).join(', ');
                            allergySuggestionsBox.style.display = 'none';
                            canAddAllergyMessage.style.display = 'none';
                            newAllergyInput.focus();
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

            document.getElementById('confirmNo').addEventListener('click', function()
            {
                document.getElementById('confirmationPopup').style.display = 'none';
            });

            document.getElementById('confirmYes').addEventListener('click', function()
            {
                document.getElementById('confirmationPopup').style.display = 'none';

                fetch('handle_add_allergies.php',
                {
                    method: 'POST',
                    headers:
                    {
                        'Content-Type':'application/json',
                    },
                    body: JSON.stringify({ newAllergies: currentAddedAllergies }),
                })

                    .then(response =>
                    {
                        if (!response.ok)
                        {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
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
                            currentAddedAllergies = [];
                            addedAllergiesList.innerHTML = '';
                            newAllergyInput.value = '';
                            allergySuggestionsBox.style.display = 'none';
                            canAddAllergyMessage.style.display = 'none';

                            function fetchAndDisplayAllergies()
                            {
                                fetch('get_user_allergies.php')
                                    .then(response => response.json())
                                    .then(allergiesData =>
                                    {
                                        displayAddedAllergiesTable(allergiesData.added);
                                        displayAllKnownAllergiesTable(allergiesData.all);
                                        displaySaveSuccessMessage();
                                    })
                                    .catch(error => console.error('Error fetching allergies:', error))
                            }

                            function displayAddedAllergiesTable(allergies)
                            {
                                const container = document.getElementById('mainContainer');

                                const wrapper = document.createElement('div');

                                const heading = document.createElement('h2');
                                heading.textContent = 'Added Allergies';

                                const list = document.createElement('ul');
                                allergies.forEach(allergy =>
                                {
                                    const listItem = document.createElement('li');
                                    listItem.textContent = allergy;
                                    list.appendChild(listItem);
                                });

                                wrapper.appendChild(heading);
                                wrapper.appendChild(list);
                                container.appendChild(wrapper);
                            }

                            function displayAllKnownAllergiesTable(allAllergies)
                            {
                                const container = document.getElementById('mainContainer');

                                const wrapper = document.createElement('div');

                                const heading = document.createElement('h2');
                                heading.textContent = 'All Known Allergies';

                                const list = document.createElement('ul');
                                allAllergies.forEach(allergy =>
                                {
                                    const listItem = document.createElement('li');
                                    listItem.textContent = allergy;
                                    list.appendChild(listItem);
                                });

                                wrapper.appendChild(heading);
                                wrapper.appendChild(list);
                                container.appendChild(wrapper);
                            }

                            function displaySaveSuccessMessage()
                            {
                                const container = document.getElementById('mainContainer');
                                const successMessage = document.createElement('p');
                                successMessage.textContent = 'Allergies saved successfully!';
                                successMessage.style.color = 'green';
                                container.appendChild(successMessage);
                            }

                            fetchAndDisplayAllergies();
                        }
                        else
                        {
                            console.error('Error saving allergies:', data.error);
                            const errorMessage = document.createElement('p');
                            errorMessage.textContent = 'Error saving allergies! Check the console for errors!'
                            errorMessage.style.color = 'red';
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
                        errorMessage.style.color = 'red';
                        errorMessage.classList.add('error-status-message');
                        addAllergyContainer.appendChild(errorMessage);
                    });
            });
        });
    </script>
    <div id="confirmationPopup" class="confirmation-popup">
        <div class="confirmation-popup-content">
            <div class="confirmation-popup-icon success">
                <svg viewBox="-11 -11 55 50" fill="none" stroke="#4CAF50" stroke-linecap="round" stroke-linejoin="round" stroke-width="7" aria-hidden="true" focusable="false">
                    <path d="M5 22 L14 34 L40 6" />
                </svg>
            </div>
            <p id="confirmationText" class="confirmation-popup-message"></p>
            <div class="confirmation-popup-buttons">
                <button id="confirmNo" class="confirmation-close-button">Close</button>
                <button id="confirmYes" class="button confirmation-yes-button" style="background-color: #4CAF50; color: white;">Yes, Add</button>
            </div>
        </div>
    </div>
</body>
</html>
