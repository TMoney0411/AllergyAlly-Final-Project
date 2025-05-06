<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['name']))
{
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    http_response_code(401);
    exit;
}

header('Content-Type: application/json');

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!isset($data['newAllergies']) || !is_array($data['newAllergies']))
{
    echo json_encode(['success' => false, 'error' => 'Invalid data format']);
    exit;
}


$cleanAllergies = array_map(function($item)
{
    return htmlspecialchars(trim($item), ENT_QUOTES, 'UTF-8');
}, $data['newAllergies']);

$servername = 'localhost';
$db_username = 'root';
$db_password = '';
$database = 'allergyally_final-project';
$table = 'account_information';

$conn = new mysqli($servername, $db_username, $db_password, $database);

if ($conn->connect_error)
{
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$name = $_SESSION['name'];

$stmt_select = $conn->prepare("SELECT allergies FROM account_information WHERE username = ?");
$stmt_select->bind_param("s", $name);
$stmt_select->execute();
$result = $stmt_select->get_result();
$existingAllergiesString = '';
if ($result->num_rows > 0)
{
    $row = $result->fetch_assoc();
    $existingAllergiesString = $row['allergies'];
}
$stmt_select->close();

$existingAllergiesArray = array_map('trim', explode(',', $existingAllergiesString));
$existingAllergiesArray = array_filter($existingAllergiesArray);

$combinedAllergiesArray = array_unique(array_merge($existingAllergiesArray, $cleanAllergies));
$combinedAllergiesString = implode(',', $combinedAllergiesArray);

$stmt_update = $conn->prepare("UPDATE account_information SET allergies = ? WHERE username = ?");
$stmt_update->bind_param("ss", $combinedAllergiesString, $name);
$stmt_update->execute();

if ($stmt_update->affected_rows > 0)
{
    echo json_encode(['success' => true]);
}
else
{
    echo json_encode(['success' => false, 'error' => 'Error updating allergies. Affected rows: ' . $stmt_update->affected_rows . '. MySQL error: ' . $stmt_update->error]);
}

$stmt_update->close();
$conn->close();

?>
