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

$success_message = "";
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
    

        $sql_select = "SELECT allergies, other FROM $table WHERE id = ?";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("i", $user_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        $row_select = $result_select->fetch_assoc();
        $current_allergies_str = $row_select['allergies'];
        $current_other_str = $row_select['other'];
        $stmt_select->close();

        $allergies_array = array_map('trim', explode(',', $current_allergies_str));
        $other_array = array_map('trim', explode(',', $current_other_str));

        $updated_allergies_str = $current_allergies_str;
        $updated_other_str = $current_other_str;

        $index_allergies = array_search($allergy_to_delete, $allergies_array);
        if ($index_allergies !== false)
        {
            unset($allergies_array[$index_allergies]);
            $allergies_array = array_filter($allergies_array);
            $updated_allergies_str = implode(',', $allergies_array);
        }
        else
        {
            $index_other = array_search($allergy_to_delete, $other_array);
            if ($index_other !== false)
            {
                unset($other_array[$index_other]);
                $other_array = array_filter($other_array);
                $updated_other_str = implode(',', $other_array);
            }
        }

        $sql_update = "UPDATE $table SET allergies = ?, other = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssi", $updated_allergies_str, $updated_other_str, $user_id);

        if ($stmt_update->execute())
        {
            http_response_code(200);
        }
        else
        {   http_response_code(500);
            echo "<p class='error-message'>Error deleting allergy: " . $stmt_update->error . "</p>";
        }

        $stmt_update->close();
        $conn->close();
    }
    catch (mysqli_sql_exception $exception)
    {
        http_response_code(500);
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
    <div class="container" id="mainContainer">
        <h1>Edit Allergies</h1>
        <div class="allergies-container">
            <?php foreach ($all_allergies as $allergy_item): ?>
                <div class="allergy-row">
                    <div class="allergy-item"><?php echo ucfirst(htmlspecialchars($allergy_item)); ?></div>
                    <form method="post" action="edit_allergies.php">
                        <input type="hidden" name="delete_allergy" value="<?php echo htmlspecialchars($allergy_item); ?>">
                        <button type="submit" class="delete-button">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
                
            <?php if (!empty($success_message)): ?>
                <p class="success-message"><?php echo $success_message; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div id="confirmationPopup" class="confirmation-popup">
        <div class="confirmation-popup-content">
            <div class="confirmation-popup-icon success">
                <svg viewBox="-11 -11 55 50" fill="none" stroke="#4CAF50" stroke-linecap="round" stroke-linejoin="round" stroke-width="7" aria-hidden="true" focusable="false">
                    <path d="M5 22 L14 34 L40 6" />
                </svg>
            </div>
            <p id="confirmationText" class="confirmation-popup-message">Are you sure you want to delete this allergy?</p>
            <div class="confirmation-popup-buttons">
                <button id="confirmNo" class="confirmation-close-button">Close</button>
                <button id="confirmYes" class="button confirmation-yes-button" style="background-color: #e53935; color: white;">Yes, delete</button>
            </div>
        </div>
    </div>

    <form id="allergiesForm" method="post" action="edit_allergies.php"></form>

    <script>
        
        const confirmationPopup = document.getElementById('confirmationPopup');
        const confirmationText = document.getElementById('confirmationText');
        const confirmationYesButton = document.getElementById('confirmYes');
        const confirmationNoButton = document.getElementById('confirmNo');
        let allergyToDelete = null;
        let rowToDelete = null;

        function showConfirmation(button)
        {
            rowToDelete = button.closest('.allergy-row');
            const form = button.closest('form');
            const allergyName = form.querySelector('input[name="delete_allergy"]').value;
            allergyToDelete = allergyName;
            confirmationText.textContent = `Are you sure you want to delete ${capitalizeFirstLetter(allergyName)}?`;
            confirmationPopup.style.display = "block";
        }

        function hideConfirmation()
        {
            confirmationPopup.style.display = "none";
            allergyToDelete = null;
            rowToDelete = null;
        }

        confirmationYesButton.addEventListener('click', () =>
        {
            if (allergyToDelete && rowToDelete)
            {
                rowToDelete.remove();
                fetch('edit_allergies.php', 
                {
                    method: 'POST',
                    headers: 
                    {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'delete_allergy=' + encodeURIComponent(allergyToDelete)
                })
                .then(response => response.text())
                .then (data =>
                {
                    const successMessageContainer = document.querySelector('.allergies-container');
                    if (successMessageContainer)
                    {
                        successMessageContainer.insertAdjacentHTML('beforeend', `<p class="success-message">Allergy: ${capitalizeFirstLetter(allergyToDelete)} deleted successfully!</p>`);
                    }

                    hideConfirmation();
                })
                .catch((error) =>
                {
                    console.error('Error deleting allergy:', error);
                    const allergiesContainer = document.querySelector('.allergies-container');
                    hideConfirmation();
                });
            }
        });

        confirmationNoButton.addEventListener('click', hideConfirmation);
                
        function capitalizeFirstLetter(string)
        {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        const deleteButtons = document.querySelectorAll('.allergy-row button[type="submit"]');
        deleteButtons.forEach(button =>
        {
            button.onclick = function(event)
            {
                event.preventDefault();
                showConfirmation(this);
            };
            button.type = 'button';
        });
    </script>
</body>
</html>
