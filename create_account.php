<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  $name = trim($_POST['name']);
  $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_EMAIL);
  $createPassword = $_POST['createPassword'];
  $retypePassword = $_POST['retypePassword'];

  $peanuts_severity = trim($_POST['peanuts_severity'] ?? '');
  $treenuts_severity = trim($_POST['treenuts_severity'] ?? '');
  $seeds_severity = trim($_POST['seeds_severity'] ?? '');
  $shellfish_severity = trim($_POST['shellfish_severity'] ?? '');
  $wheat_severity = trim($_POST['wheat_severity'] ?? '');
  $milk_severity = trim($_POST['milk_severity'] ?? '');
  $eggs_severity = trim($_POST['eggs_severity'] ?? '');
  $soybeans_severity = trim($_POST['soybeans_severity'] ?? '');
  $sesame_severity = trim($_POST['sesame_severity'] ?? '');
  $mangos_severity = trim($_POST['mangos_severity'] ?? '');
  

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
    echo json_encode([
      "status" => "error",
      "message" => "Database connection error. Please try again later."
    ]);
  }
  else
  {
    if (!filter_var($username, FILTER_VALIDATE_EMAIL))
    {
      echo json_encode([
        "status" => "error",
        "message" => "Invalid email format! Please enter a valid email address!"
      ]);
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
          echo json_encode([
            "status" => "error",
            "code" => "USERNAME_TAKEN",
            "message" => "Your username is the same as another user! Please change it!"
          ]);
          exit;
        }
        else
        {
          if (empty($name) || empty($createPassword) || empty($retypePassword))
          {
            echo json_encode([
              "status" => "error",
              "message" => "Please fill out all required fields (Name, Create Password, Retype Password)!"
            ]);
            exit;
          }
          else if ($createPassword != $retypePassword)
          {
            echo json_encode([
              "status" => "error",
              "message" => "Passwords do not match! Please retype the password!"
            ]);
            exit;
          }
          else if (strlen($createPassword) < 8)
          {
            echo json_encode([
              "status" => "error",
              "message" => "Password must be at least 8 characters long!"
            ]);
            exit;
          }
          else if (strlen($name) > 320)
          {
            echo json_encode([
              "status" => "error",
              "message" => "Name must be at most 320 characters long!"
            ]);
            exit;
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
                $severity_val = trim($_POST[$severity_key]);
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
              echo json_encode([
                "status" => "error",
                "message" => "Please provide a valid severity (1-10) for all checked allergies!"
              ]);
              exit;
            }
            else
            {
              if (!empty($selected_allergies))
              {
                $allergies_str = implode(", ", $selected_allergies);
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
                  echo json_encode([
                    "status" => "success",
                    "message" => "Congratulations! You have created an account! Please click the Login button at the top of the screen to login with your account!"
                  ]);
                  exit;
                }
                else
                {
                  error_log("Database query failed: " . $stmt->error);
                  echo json_encode([
                    "status" => "error",
                    "message" => "Error creating account! Please try again! Check the console for specific error!"
                  ]);
                  exit;
                }
              }
              else
              {
                error_log("Database prepare failed: " . $conn->error);
                echo json_encode([
                  "status" => "error",
                  "message" => "Internal server error! Please try again later! Check the console for specific error!"
                ]);
                exit;
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
        echo json_encode([
          "status" => "error",
          "message" => "Internal server error! Please try again later! Check the console for specific error!"
        ]);
        exit;
      }
    }
  }

  if ($conn)
  {
    $conn->close();
  }
}
?>
#hi
