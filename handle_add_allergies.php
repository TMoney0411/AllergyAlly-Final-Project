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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && stripos($_SERVER['CONTENT_TYPE'], 'application/json') === 0)
{
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (isset($data['newAllergies']) && is_array($data['newAllergies']))
    {
        $newAllergies = array_map('trim', array_map('strtolower', $data['newAllergies']));
        $user_id = $_SESSION['user_id'];

        $servername = 'localhost';
        $db_username = 'root';
        $db_password = '';
        $database = 'allergyally_final-project';
        $table = 'account_information';

        $conn = new mysqli($servername, $db_username, $db_password, $database);

        if ($conn->connect_error)
        {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database connection failed']);
            exit;
        }

        $sql_select = "SELECT allergies, other FROM $table WHERE id = ?";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("i", $user_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        $existing_allergies_string = '';
        $existing_other_string = '';

        if ($row = $result_select->fetch_assoc())
        {
            $existing_allergies_string = strtolower($row['allergies'] ?? '');
            $existing_other_string = strtolower($row['other'] ?? '');
        }
        $stmt_select->close();

        $existing_allergies = array_filter(explode(',', $existing_allergies_string));
        $existing_other = array_filter(explode(',', $existing_other_string));

        $base_known_allergies = ["dairy", "fish", "mustard", "celery", "avocado", "kiwi", "latex", "corn", "coconut", "rice", "oats", "yeast"];

        $allergies_to_save = $existing_allergies;
        $other_to_save = $existing_other;

        foreach ($newAllergies as $allergy)
        {
            
            if (!in_array($allergy, $allergies_to_save))
            {
                $allergies_to_save[] = $allergy;
            }
            
            if (($key = array_search($allergy, $other_to_save)) !== false)
            {
                unset($other_to_save[$key]);
            }
        }

        $allergies_string_save = implode(',', $allergies_to_save);
        $other_string_save = implode(',', $other_to_save);

        $sql_update = "UPDATE $table SET allergies = ?, other = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssi", $allergies_string_save, $other_string_save, $user_id);

        if ($stmt_update->execute())
        {
            echo json_encode(['success' => true]);
        }
        else
        {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error updating allergies: ' . $stmt_update->error]);
        }

        $stmt_update->close();
        $conn->close();

    }
    else
    {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid data received']);
    }
}
else
{
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request method or content type']);
}
?>
