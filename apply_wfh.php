<?php
    require_once "model/common.php";
    // Check if the user is logged in
    if (!isset($_SESSION['userID'])) {
        header("Location: login.php");
        exit();
    }

    // Fetch user details
    $userID = $_SESSION['userID'];
    $userRole = $_SESSION['userRole'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Work-From-Home</title>
</head>
<body>

    <h1 style='display: inline-block; margin-right: 20px;'>Apply for Work-from-Home Days</h1><a href='my_requests.php'>Back</a>

    <form action="process_wfh_request.php" method="POST">
        <label for="date">Select Date(s):</label><br>
        <input type="date" name="wfh_date" required><br><br>
        
        <label for="reason">Reason for WFH:</label><br>
        <textarea name="reason" required></textarea><br><br>
        
        <button type="submit">Submit Request</button>
    </form>

<?php
    $msg = '';
    if (isset($_POST['submit'])){
        $wfhDate = $_POST['wfh_date'];
        $reason = $_POST['reason'];
        $status = "Pending";

        $dao = new RequestDAO;


    }

?>

</body>
</html>
