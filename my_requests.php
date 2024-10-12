<?php
    require_once "model/common.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.0/main.min.css" />
    <title>My Requests</title>
    <style>
        .dropdown {
            float: left;
            overflow: visible;
        }

        .dropdown .dropbtn {
            font-size: 16px;
            border: none;
            outline: none;
            color: white;
            padding: 14px 20px;
            background-color: inherit;
            font-family: inherit;
            margin: 0;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 100px;
            max-width: 150px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .dropdown-content a {
            float: none;
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
            color: black;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #000;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            height: 80px;
            border-bottom: 1px solid #444;
        }

        .navbar a img {
            height: 60px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: left;
            font-size: 16px;
        }

        th {
            background-color: #f1f1f1;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }
    </style>

    <script>
      
        function confirmDelete(requestId, staffId, arrangementDate) {
            var message = "Are you sure you want to delete the request with:\n" +
                        "Request ID: " + requestId + "\n" +
                        "Staff ID: " + staffId + "\n" +
                        "Arrangement Date: " + arrangementDate + "?";
            
            if (confirm(message)) {
                // If confirmed, redirect to delete script with all parameters
                window.location.href = "delete_request.php?request_id=" + requestId +
                                    "&staff_id=" + staffId +
                                    "&arrangement_date=" + arrangementDate;
            }
        }
    
    </script>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <a href="home.php"><img src="images/logo.jpg" alt="Company Logo"></a> <!-- Link to homepage -->
    <div class="dropdown">
        <button class="dropbtn">New Requests</button>
        <div class="dropdown-content">
            <a href="apply_wfh.php">Apply for Work-From-Home</a>
            <a href="apply_leave.php">Apply for Leave</a>
            <a href="delete_wfh.php">Delete Request</a>
            <a href="update_wfh.php">Update Request</a>
        </div>
    </div>
</div>


<?php
    # Display User Details
    $userID = $_SESSION['userID'];
    $userRole = $_SESSION['userRole'];

    $dao = new RequestDAO;
    $requests = $dao->retrieveRequestInfo($userID);

    echo "<br><br>";

    if (count($requests) > 0) {
        echo "<table border=1>";
        echo "<tr><th>ID</th><th>Request ID</th><th>Date</th><th>Arrangement</th><th>Reason</th><th>Status</th><th>Delete</th></tr>";    
        foreach ($requests as $request) {
            echo "<tr><td>{$request['Staff_ID']}</td><td>{$request['Request_ID']}</td><td>{$request['Arrangement_Date']}</td><td>{$request['Working_Arrangement']}</td><td>{$request['Reason']}</td><td>{$request['Request_Status']}</td>";
            echo "<td>
                    <button onclick='confirmDelete({$request['Request_ID']}, {$request['Staff_ID']}, \"{$request['Arrangement_Date']}\")'>Delete</button>
                </td>
                </tr>";
            
        }
        echo "</table>";
    } else {
        echo '<p style="color: red;">No Requests Found</p>';
    }
?>

</body>
</html>
