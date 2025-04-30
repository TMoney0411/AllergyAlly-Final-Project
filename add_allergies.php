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
            <div class="add-allergy-input-container">
                <div class="add-allergy-input">
                    <input type="text" id="newAllergyInput" placeholder="Please enter your new allergy here(If adding more than one allergy, separate them by a comma)" autocomplete="off">
                </div>
                <div class="suggestions-box">
                    <ul id="allergySuggestionsList">
                    </ul>
                </div>
            </div>
            <div class="added-allergies">
                <h2>Added Allergies:</h2>
                <ul id="addedAllergiesList">
                </ul>
            </div>
            <button id="saveNewAllergies">Save New Allergies</button>
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

        new AllergyInput.addEventListener('input', function()
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
            checkedBoxes.forEach(box =>
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
                    listItem.textConent = result;
                    listItem.addEventListener('click', function()
                    {
                        const selectedAllergy = this.content;
                        if (initialAllergies.map(a => a.toLowerCase()).includes(selectedAllergy.toLowerCase()) || currentAllergies.map(a => a.toLowerCase()).includes(selectedAllergy.toLowerCase()))
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
            if(!currentAddedAllergies.map(a => a.toLowerCase()).includes(allergy.toLowerCase()))
            {
                currentAddedAllergies.push(allergy);
                const listItem = document.createElement('li');
            }
        }
</body>
</html>
