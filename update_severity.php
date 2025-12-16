<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_POST['allergy_severity'])) {
  echo json_encode(['success' => false]);
  exit;
}

$servername = 'localhost';
$db_username = 'root';
$db_password = '';
$database = 'allergyally_final-project';
$table = 'account_information';

$user_id = $_SESSION['user_id'];
$changes = [];

$conn = new mysqli($servername, $db_username, $db_password, $database);
if ($conn->connect_error) {
  echo json_encode(['success' => false]);
  exit;
}

/* Get existing values */
$stmt = $conn->prepare("SELECT severity_symptoms FROM $table WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$existing = [];

if (!empty($row['severity_symptoms'])) {
  $normalized = str_replace(["\r\n", "\r", "\n"], ',', $row['severity_symptoms']);
  foreach (explode(',', $normalized) as $pair) {
    if (strpos($pair, ':') !== false) {
      [$a, $s] = array_map('trim', explode(':', $pair, 2));
      $existing[strtolower($a)] = (int)$s;
    }
  }
}


/* Build updated string + detect changes */
$newPairs = [];
foreach ($_POST['allergy_severity'] as $allergy => $severity) {
  $severity = (int)$severity;
  $key = strtolower($allergy);

  $newPairs[] = "$allergy:$severity";

  if (!isset($existing[$key]) || $existing[$key] !== $severity) {
    $changes[] = [
      'allergy' => ucfirst($allergy),
      'severity' => $severity
    ];
  }
}

/* Update DB */
$newString = implode("\n", $newPairs);
$update = $conn->prepare(
  "UPDATE $table SET severity_symptoms = ? WHERE id = ?"
);
$update->bind_param("si", $newString, $user_id);
$update->execute();

$stmt->close();
$update->close();
$conn->close();

echo json_encode([
  'success' => true,
  'changes' => $changes
]);
