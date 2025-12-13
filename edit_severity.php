<?php
session_start();

if (!isset($_SESSION['name'])) {
  header("Location: login.php");
  exit;
}

$name = $_SESSION['name'];
$severity_symptoms = [];

// --- Database Setup ---
$servername = 'localhost';
$db_username = 'root';
$db_password = '';
$database = 'allergyally_final-project';
$table = 'account_information';

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $conn = new mysqli($servername, $db_username, $db_password, $database);

  if (!$conn->connect_error) {
    $sql = "SELECT severity_symptoms FROM $table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      $symptoms_string = $row['severity_symptoms'] ?? '';

      $normalized_string = str_replace(["\r\n", "\r", "\n"], ', ', $symptoms_string);
      $pairs = array_filter(array_map('trim', explode(',', $normalized_string)));

      foreach ($pairs as $pair) {
        if (strpos($pair, ':') !== false) {
          list($allergy, $severity) = array_map('trim', explode(':', $pair, 2));
          $severity_symptoms[$allergy] = (int)$severity;
        }
      }
    }

    $stmt->close();
    $conn->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Severity</title>

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <style>
    #instructions-text {
      margin-top: 0;
      margin-bottom: 15px;
    }

    .logo {
      display: block;
      margin-bottom: 0;
    }

    .severity-input-group {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .severity-input-group label {
      font-weight: bold;
      flex-grow: 1;
    }

    .severity-input-group input[type="number"] {
      width: 50px;
      padding: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
      text-align: center;
    }
  </style>
</head>

<body>

<button id="backButton" class="back-button">Back</button>

<div class="container">

  <div class="settings-icon">
    <i id="settingsButton" class="fas fa-cog"></i>
  </div>

  <img src="allergyally_logo.png" alt="AllergyAlly Logo" class="logo">

  <h3 id="instructions-text">Please change your severity below</h3>

  <form id="severityEditForm" action="edit_severity.php" method="POST">

    <?php if (!empty($severity_symptoms)): ?>
      <?php foreach ($severity_symptoms as $allergy => $severity): ?>

        <div class="severity-input-group">
          <label for="<?= htmlspecialchars($allergy) ?>">
            <?= htmlspecialchars(ucfirst($allergy)) ?>:
          </label>

          <input
            type="number"
            id="<?= htmlspecialchars($allergy) ?>"
            name="allergy_severity[<?= htmlspecialchars($allergy) ?>]"
            value="<?= htmlspecialchars($severity) ?>"
            min="1"
            max="10"
            required
          >
        </div>

      <?php endforeach; ?>

      <!-- 🔹 Hidden until change -->
      <div class="button-container"
           id="saveButtonContainer"
           style="display: none; margin-top: 20px;">
        <input type="submit" value="Save Severity Changes">
      </div>

    <?php else: ?>
      <p>No current allergies found.</p>
    <?php endif; ?>

  </form>

  <?php include('disclaimer.php'); ?>
</div>

<div class="dropdown-menu" id="settingsDropdown">
  <a href="delete_allergies.php">Delete Allergies</a>
  <a href="add_allergies.php">Add Allergies</a>
</div>

<script src="settingsScript.js"></script>

<script>
  // Back button
  document.getElementById('backButton').addEventListener('click', () => {
    window.location.href = 'product_options.php';
  });

  // Settings dropdown
  document.addEventListener('DOMContentLoaded', () => {
    const settingsButton = document.getElementById('settingsButton');
    const settingsDropdown = document.getElementById('settingsDropdown');

    settingsButton.addEventListener('click', () => {
      settingsDropdown.classList.toggle('show');
    });

    window.addEventListener('click', e => {
      if (!e.target.closest('.settings-icon') &&
          !e.target.closest('.dropdown-menu')) {
        settingsDropdown.classList.remove('show');
      }
    });
  });

  // ✅ SHOW SAVE BUTTON WHEN ANY VALUE CHANGES
  document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('#severityEditForm input[type="number"]');
    const saveButtonContainer = document.getElementById('saveButtonContainer');

    inputs.forEach(input => {
      input.dataset.originalValue = input.value;

      input.addEventListener('input', function () {
        if (input.value !== input.dataset.originalValue) {
          saveButtonContainer.style.display = 'block';
        }
      });
    });
  });
</script>

</body>
</html>
