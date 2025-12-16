<?php
session_start();

if (!isset($_SESSION['name'])) {
  header("Location: login.php");
  exit;
}

$name = $_SESSION['name'];
$severity_symptoms = [];

/* ---------- DATABASE SETUP ---------- */
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
.centered-content { max-width: 250px; margin: 0 auto; }
.severity-input-group {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}
.severity-input-group label {
  font-weight: bold;
  margin-right: 15px;
}
.severity-input-group input {
  width: 50px;
  text-align: center;
}
.fade-out {
  opacity: 0;
  transition: opacity 1s ease;
}
</style>
</head>

<body>

<button id="backButton" class="back-button">Back</button>

<div class="container">
  <div class="settings-icon">
    <i id="settingsButton" class="fas fa-cog"></i>
  </div>

  <img src="allergyally_logo.png" class="logo" alt="Logo">

  <h3>Please change your severity below</h3>

  <form id="severityEditForm" action="edit_severity.php" method="POST">
    <div class="centered-content">

      <?php foreach ($severity_symptoms as $allergy => $severity): ?>
        <div class="severity-input-group">
          <label for="<?= htmlspecialchars($allergy) ?>">
            <?= htmlspecialchars(ucfirst($allergy)) ?>:
          </label>
          <input type="number"
                 id="<?= htmlspecialchars($allergy) ?>"
                 name="allergy_severity[<?= htmlspecialchars($allergy) ?>]"
                 value="<?= htmlspecialchars($severity) ?>"
                 min="1" max="10" required>
        </div>
      <?php endforeach; ?>

      <div class="button-container"
           id="saveButtonContainer"
           style="display:none; margin-top:20px;">
        <input type="submit" value="Save Severity Changes">
      </div>

    </div>
  </form>

  <?php include('disclaimer.php'); ?>

  <!-- ✅ SUCCESS MESSAGE -->
  <?php if (!empty($_SESSION['severity_success'])): ?>
    <div id="severitySuccessMessage" style="margin-top:15px;">
      <?php foreach ($_SESSION['severity_success'] as $change): ?>
        <p style="color:green; font-weight:bold;">
          <?= htmlspecialchars($change['allergy']) ?>
          severity successfully changed to
          <?= htmlspecialchars($change['severity']) ?>!
        </p>
      <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['severity_success']); ?>
  <?php endif; ?>

</div>

<div id="confirmationPopup" class="confirmation-popup" style="display:none;">
  <div class="confirmation-popup-content">
    <div class="confirmation-popup-icon success">
      <svg viewBox="-11 -11 55 50" fill="none"
           stroke="#4CAF50"
           stroke-linecap="round"
           stroke-linejoin="round"
           stroke-width="7"
           aria-hidden="true"
           focusable="false">
        <path d="M5 22 L14 34 L40 6" />
      </svg>
    </div>

    <p id="confirmationText" class="confirmation-popup-message"></p>

    <div class="confirmation-popup-buttons">
      <button id="confirmNo" class="confirmation-close-button">
        Close
      </button>
      <button id="confirmYes"
              class="button confirmation-yes-button"
              style="background-color:#4CAF50;color:white;">
        Yes, Save
      </button>
    </div>
  </div>
</div>

<div class="dropdown-menu" id="settingsDropdown">
  <a href="add_allergies.php">Add Allergies</a>
  <a href="delete_allergies.php">Delete Allergies</a>
<script src="settingsScript.js"></script>

<script>
document.getElementById('backButton').addEventListener('click', function () {
  window.location.href = 'product_options.php';
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('severityEditForm');
  const inputs = document.querySelectorAll('input[type="number"]');
  const saveBtn = document.getElementById('saveButtonContainer');
  const popup = document.getElementById('confirmationPopup');
  const popupText = document.getElementById('confirmationText');
  let allowSubmit = false;

  inputs.forEach(i => i.dataset.original = i.value);

  function checkChanges() {
    saveBtn.style.display =
      [...inputs].some(i => i.value !== i.dataset.original)
        ? 'block' : 'none';
  }

  inputs.forEach(i => i.addEventListener('input', checkChanges));

  function buildMessage() {
    const changes = [];
    inputs.forEach(i => {
      if (i.value !== i.dataset.original) {
        changes.push(`${i.id.charAt(0).toUpperCase() + i.id.slice(1)} to ${i.value}`);
      }
    });

    if (changes.length === 1)
      return `Are you sure you want to change ${changes[0]}?`;

    if (changes.length === 2)
      return `Are you sure you want to change ${changes[0]} and ${changes[1]}?`;

    return `Are you sure you want to change ${changes.slice(0,-1).join(', ')}, and ${changes.at(-1)}?`;
  }

  form.addEventListener('submit', e => {
    if (!allowSubmit) {
      e.preventDefault();
      popupText.textContent = buildMessage();
      popup.style.display = 'block';
    }
  });

  document.getElementById('confirmYes').onclick = () => 
  {
    popup.style.display = 'none';

    const formData = new FormData(form);

    fetch('update_severity.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        alert('Failed to save changes.');
        return;
      }

      // Remove old messages
      document.getElementById('severitySuccessMessage')?.remove();

      const msgDiv = document.createElement('div');
      msgDiv.id = 'severitySuccessMessage';
      msgDiv.style.marginTop = '15px';

      data.changes.forEach(change => {
        const p = document.createElement('p');
        p.style.color = 'green';
        p.style.fontWeight = 'bold';
        p.textContent = `${change.allergy} severity successfully changed to ${change.severity}!`;
        msgDiv.appendChild(p);
      });

      // Insert BELOW disclaimer
      document.querySelector('.container')
        .appendChild(msgDiv);

      // Update original values so Save button hides again
      inputs.forEach(i => i.dataset.original = i.value);
      saveBtn.style.display = 'none';

      // Fade out
      setTimeout(() => {
        msgDiv.classList.add('fade-out');

        setTimeout(() => {
          msgDiv.remove();
        }, 2000);

      }, 2000);
    });
  };
  document.getElementById('confirmNo').onclick = () => {
    popup.style.display = 'none';
  };

  /* Fade success message */
  setTimeout(() => {
    const msg = document.getElementById('severitySuccessMessage');
    if (msg) {
      msg.classList.add('fade-out');
      setTimeout(() => msg.remove(), 1000);
    }
  }, 7000);
});
</script>
</body>
</html>
