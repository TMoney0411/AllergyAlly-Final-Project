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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
  <div class="container"> 
    <div class="settings-icon">
      <i class="fas fa-cog" id="settingsButton"></i>
    </div>
    <h1 id="allergy-ally-header">AllergyAlly</h1>
    <div id="greeting-container">
      <h2 id="greeting-text">Hello <?php echo htmlspecialchars($name) . "!"; ?></h2>
    </div>
   <h3 id="instructions-text">Please select the button below: </h3>
   <div class="button-container">
      <input type="button" value="Search" onclick="window.location.href='food_finder.php'"><br><br>
    </div>
    <?php include('disclaimer.php'); ?>
  </div> 
  <div id="location"></div>
  <div class="dropdown-menu" id="settingsDropdown">
    <a href="edit_allergies.php">Edit Allergies</a>
    <a href="add_allergies.php">Add Allergies</a>
  </div>
  <script src="settingsScript.js"></script>
</body>
</html>
