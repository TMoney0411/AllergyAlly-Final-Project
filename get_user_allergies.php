<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['name']))
{
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$servername = 'localhost';
$db_username = 'root';
$db_password = '';
$database = 'allergyally_final-project';

$conn = new mysqli($servername, $db_username, $db_password, $database);

if ($conn->connect_error)
{
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$username = $_SESSION['name'];

$stmt = $conn->prepare("SELECT allergies FROM account_information WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$added_allergies = [];
if ($result->num_rows > 0)
{
    $row = $result->fetch_assoc();
    $addedAllergies = [];
    if (!empty($row['allergies']))
    {
        $addedAllergies = explode(',', $row['allergies']);
        $addedAllergies = array_map('trim', $addedAllergies);
        $addedAllergies = array_filter($addedAllergies);
    }
}

$stmt->close();
$conn->close();

$allKnownAllergies = ["Dairy", "Fish", "Mustard", "Celery", "Avocado", "Kiwi", "Latex", "Corn", "Coconut", "Rice", "Oats", "Yeast"];

$response = 
[
    'added' => $addedAllergies,
    'all' => $allKnownAllergies,
];

header('Content-Type: application/json');
echo json_encode($response);
exit;

?>
