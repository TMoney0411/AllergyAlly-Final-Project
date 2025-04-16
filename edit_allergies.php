<?php
session_start();

if (!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit;
}

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

    $all_allergies = [];
    if ($result->num_rows > 0)
    {
        $row = $result->fetch_assoc();
        $user_allergies = array_map('trim', explode(',', $row['allergies']));
        $user_other_allergies = array_map('trim', explode(',', $row['other']));
        $all_allergies = array_merge($user_allergies, $user_other_allergies);
        $all_allergies = array_filter($all_allergies);
    }

    $stmt->close();
    $conn->close();
}
catch (mysqli_sql_exception $exception)
{
    echo "<p class='error-message'>Database error: " . $exception->getMessage() . "</p>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_allergy']))
{
    $allergy_to_delete = trim($_POST['delete_allergy']);

    try
    {
        $conn = new mysqli($servername, $db_username, $db_password, $database);
        if ($conn->connect_error)
        {
            die("Connect failed: " . $conn->connect_error);
        }

        if (!empty($remove_allergy))
        {
            $other_allergies_array = array_map('trim', explode(',' , $user_other_allergies));
            $index = array_search(strtolower($remove_allergy), array_map('strtolower', $other_allergies_array));

            if ($index !== false)
            {
                unset($other_allergies_array[$index]);
                $user_other_allergies = implode(',' , $other_allergies_array);
            }
        }

        $sql = "UPDATE $table SET allergies = ?, other = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $allergies_string, $user_other_allergies, $user_id);

        if ($stmt->execute())
        {
            echo "<p class='success-message'>Allergies updated successfully!</p>";
            echo "<script>
                const deletedButton = document.querySelector('button[value=\"" . htmlspecialchars($allergy_to_delete) . "\"]');
                if (deletedButton)
                {
                    const allergyRow = deletedButton.closet('.allergy-row');
                    if (allergyRow)
                    {
                        allergyRow.remove();
                    }
                }
            </script>";
        } 
        else
        {
            echo "<p class='error-message'>Error updating allergies: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
        $conn->close();
    }
    catch (mysqli_sql_exception $exception)
    {
        echo "<p class='error-message'>Database error: " . $exception->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang = "en">
<head>
    <meta charset="UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Allergies</h1>
        <div class="allergies-container">
            <?php if (!empty($all_allergies)): ?>
                <?php foreach ($all_allergies as $allergy): ?>
                    <div class='allergy-row'>
                        <div class='allergy-item'><?php echo ucfirst(htmlspecialchars($allergy)); ?></div> 
                            <form method='post' action=edit_allergies.php>
                                <input type='hidden' name='delete_allergy' value='" . htmlspecialchars($allergy) . "'>
                                <button type='submit'>Delete</button>
                            </form>
                        </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="allergy-row empty-row">
                    <div class="allergy-item"></div>
                </div>
        <?php endif; ?>
    </div>

    <form id="allergiesForm" method="post" action="edit_allergies.php">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_allergy']))
        {
            $allergy_to_delete = trim($_POST['delete_allergy']);

            echo "<p class='success-message'>Allergies updated successfully!</p>";
        }
        ?>
    </form>
    <script>
        const checkboxes = document.querySelectorAll('input[name="allergies[]"]');
        const removeInput = document.getElementById('remove_allergy');
        const saveButton = document.getElementById('saveButton');

        function showSaveButton()
        {
            saveButton.style.display = 'block';
        }

        checkboxes.forEach(checkbox =>
        {
            checkbox.addEventListener('change', showSaveButton);
        });

        removeInput.addEventListener('input', showSaveButton);
    </script>
</body>
</html>
