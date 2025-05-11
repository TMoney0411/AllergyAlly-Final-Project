<!DOCTYPE html>
<html lang = "en">
<head>
    <meta charset="UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
  <div id="backButton" class="back-button">Back</button></div>

  <div class="container">
    <div class="settings-icon">
      <i class="fas fa-cog" id="settingsButton"></i>
    </div>
    <h1>AllergyAlly</h1>
    <h2 style = "color:black;">Please type in your barcode number below: </h2>
    <form id="form" method="post" action="food_finder.php">
      <input type="text" id="searchHere" name="searchHere">
      <button type="submit" id="search">Search</button><br><br>
    </form>
    <div id="search-results">
    </div>
    <?php include('disclaimer.php'); ?>

    <div id="allergy-warning-overlay"></div>
    <div id="allergy-warning">
      <div id="warning-header">
        <i class="fas fa-exclamation-triangle"></i> Warning!
      <div id="warning-body">
        <h3>The product you entered contains the following allergies:</h3>
        <ul id="found-allergy-list"></ul>
      </div>
      <div id="warning-footer">
        <button id="close-warning">OK</button>
      </div>
    </div>

    <?php
    session_start();

    $found_allergens = [];
    $search_results = null;
    $search_query = '';

    function search_openfoodfacts($barcode)
    {
      $api_url = "https://world.openfoodfacts.net/api/v2/product/" . urlencode($barcode) . "?fields=product_name,ingredients_text";
      $response = @file_get_contents($api_url);

      if ($response === false)
      {
        error_log("Error fetching data from Open Food Facts for barcode:" . $barcode);
        return null;
      }

      $data = json_decode($response, true);

      if ($data && isset($data['product']))
      {
        return
        [
          'product_name' => $data['product']['product_name'] ?? "Product name not found",
          'ingredients_text' => $data['product']['ingredients_text'] ?? "No allergies found"
        ];
      }
      else
      {
        error_log("Product not found for barcode: " . $barcode);
        return null;
      }
    }

    function check_for_allergens($ingredients, $allergies_string)
    {
      $allergens_array = array_map('trim', explode(',', strtolower($allergies_string)));
      $ingredients_lower = strtolower($ingredients);
      $found_allergens = [];

      foreach ($allergens_array as $allergen)
      {
        if (!empty($allergen) && strpos($ingredients_lower, $allergen) !== false)
        {
          $found_allergens[] = trim($allergen);
        }
      }

      return $found_allergens;
    }

    if (!empty($_POST['searchHere']) && isset($_SESSION['user_id']))
    {
      $search_query = trim($_POST['searchHere']);
      $user_id = $_SESSION['user_id'];

      $servername = 'localhost';
      $db_username = 'root';
      $db_password = '';
      $database = 'allergyally_final-project';
      $table = 'account_information';

      try
      {
        $conn = new mysqli($servername, $db_username, $db_password, $database);
        if ($conn->connect_error)
        {
          die("Connect failed: " . $conn->connect_error);
        }

        $sql = "SELECT allergies, other FROM $table WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0)
        {
          $row = $result->fetch_assoc();
          $user_allergies = $row['allergies'];
          $user_other_allergies = $row['other'];

          $all_user_allergies = trim(strtolower($user_allergies . ',' . $user_other_allergies));

          if (!empty($search_query))
          {
            $search_results_data = search_openfoodfacts($search_query);
            $search_results = $search_results_data ? $search_results_data : null;

            if ($search_results !== null)
            {
              $found_allergens = check_for_allergens($search_results['ingredients_text'], $all_user_allergies);
            }
          }
        }
        $stmt->close();
        $conn->close();
      }
      catch(mysqli_sql_exception $exception)
      {
        echo "<div id=\"search-results\"><p class='error-message'>Database error: " . $exception->getMessage() . "</p></div>";
      }
    }
    ?>

    <div id="allergy-data" style="display: none;"
      data-allergens-found="<?php echo count($found_allergens) > 0 ? 'true' : 'false'; ?>"
      data-allergen-array="<?php echo htmlspecialchars(json_encode($found_allergens)); ?>"
      data-product-name="<?php echo htmlspecialchars($search_results['product_name'] ?? ''); ?>"
      data-ingredients-text="<?php echo htmlspecialchars($search_results['ingredients_text'] ?? ''); ?>">
    </div>
  </div>
  <div class="dropdown-menu" id="settingsDropdown">
    <a href="edit_allergies.php">Edit Allergies</a>
    <a href="add_allergies.php">Add Allergies</a>
  </div>
  <script src="settingsScript.js"></script>
  <script>
    document.getElementById('backButton').addEventListener('click', function()
    {
      window.location.href = 'product_options.php';
    });
    
    document.addEventListener('DOMContentLoaded', function()
    {
      const form = document.getElementById('form');

      form.addEventListener('submit', function(event)
      {
        event.preventDefault();

        const formData = new FormData(form);

        fetch('food_finder.php',
        {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then (html =>
        {
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = html;
          const allergyDataDiv = tempDiv.querySelector('#allergy-data');

          const allergensFound = allergyDataDiv.dataset.allergensFound === 'true';
          let allergenArray = JSON.parse(allergyDataDiv.dataset.allergenArray || '[]');
          
          const productName = allergyDataDiv.dataset.productName;
          const ingredientsText = allergyDataDiv.dataset.ingredientsText;

          const allergyWarningOverlay = document.getElementById('allergy-warning-overlay');
          const allergyWarning = document.getElementById('allergy-warning');
          const foundAllergyList = document.getElementById('found-allergy-list');
          const searchResultsDiv = document.getElementById('search-results');

          if (allergensFound)
          {
            foundAllergyList.innerHTML = '';
            allergenArray.forEach(allergy => 
            {
              const listItem = document.createElement('li');
              listItem.textContent = allergy;
              foundAllergyList.appendChild(listItem);
            });
            allergyWarningOverlay.style.display = 'block';
            allergyWarning.style.display = 'block';
            searchResultsDiv.style.display = 'none';
          }
          else if (productName && ingredientsText)
          {
            const displayIngredients = allergenArray.length === 0
              ? `<span class="allergy-safe">No Allergies Found!</span>`
              : ingredientsText;
            
            searchResultsDiv.innerHTML = `<table>
              <tr><th>Product Name</th><th>Ingredients</th></tr>
              <tr><td>${productName}</td><td>${displayIngredients}</td></tr>
            </table>`;
      
            searchResultsDiv.style.display = 'block';
            allergyWarningOverlay.style.display = 'none';
            allergyWarning.style.display = 'none';
          }
          else if ("<?php echo !empty($_POST['searchHere']); ?>")
          {
            searchResultsDiv.innerHTML = "<p>Product with barcode '<?php echo htmlspecialchars($search_query); ?>' not found or an error occurred.</p>";
            searchResultsDiv.style.display = 'block';
            allergyWarningOverlay.style.display = 'block';
            allergyWarning.style.display = 'none';
          }  
          else
          {
            searchResultsDiv.innerHTML = "";
            searchResultsDiv.style.display = 'none';
            allergyWarningOverlay.style.display = 'none';
            allergyWarning.style.display = 'none';
          } 

          const closeWarningButton = document.getElementById('close-warning');

          closeWarningButton.addEventListener('click', function()
          {
            allergyWarningOverlay.style.display = 'none';
            allergyWarning.style.display = 'none';

            if (productName && ingredientsText)
            {
              let highlightedIngredients = '';
              let parts = ingredientsText.split(new RegExp(`(${allergenArray.join('|')})`, 'gi'));
              parts.forEach(part =>
              {
                if (allergenArray.some(allergen => part.toLowerCase().includes(allergen)))
                {
                  highlightedIngredients += `<span style="color: red;">${part}</span>`;
                }
                else
                {
                  highlightedIngredients += `<span>${part}</span>`;
                }
              });
              searchResultsDiv.innerHTML = `<table><tr><th>Product Name</th><th>Ingredients</th></tr><tr><td>${productName}</td><td class='${(allergensFound ? 'allergy-warning-triggered' : 'allergy-safe')}'>${highlightedIngredients}</td></tr></table>`;
              searchResultsDiv.style.display = 'block';
            }
          });
          allergyWarningOverlay.addEventListener('click', function()
          {
            allergyWarningOverlay.style.display = 'none';
            allergyWarning.style.display = 'none';

            if (productName && ingredientsText)
            {
              let highlightedIngredients = '';
              let parts = ingredientsText.split(new RegExp(`(${allergenArray.join('|')})`, 'gi'));
              parts.forEach(part =>
              {
                if (allergenArray.some(allergen => part.toLowerCase().includes(allergen)))
                {
                  highlightedIngredients += `<span style="color: red;">${part}</span>`;
                }
                else
                {
                  highlightedIngredients += `<span>${part}</span>`;
                }
              });
              searchResultsDiv.innerHTML = `<table><tr><th>Product Name</th><th>Ingredients</th></tr><tr><td>${productName}</td><td class='${(allergensFound ? 'allergy-warning-triggered' : 'allergy-safe')}'>${ingredientsText}</td></tr></table>`;
              searchResultsDiv.style.display = 'block';
            }
          });
        })
        .catch(error=>
        {
          console.error('Error:', error);
        });
      });
    });
  </script>
</body>
</html>
