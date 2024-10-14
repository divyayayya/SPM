<?php
    Class RequestDAO{
        public function retrieveRequestInfo($userID){
            $conn = new ConnectionManager();
            $pdo = $conn->getConnection();

            $sql = 'SELECT * FROM employee_arrangement WHERE Staff_ID = :userID';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = null;
            $pdo = null;

            return $result;
        }

        public function generateReqID() {
            try {
                $conn = new ConnectionManager();
                $pdo = $conn->getConnection();
        
                // Query to get the max Request_ID
                $sql = "SELECT MAX(Request_ID) AS maxID FROM employee_arrangement";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
                // Increment the max Request_ID by 1 to get the new ID
                $newRequestID = $row['maxID'] + 1;
        
                return $newRequestID;
        
            } catch (PDOException $e) {
                echo "SQL error: " . $e->getMessage();
                return false;
            }
        }
        

        public function submitWFHRequest($userID, $wfh_date, $wfh_time, $reason) {
            try {
                $conn = new ConnectionManager();
                $pdo = $conn->getConnection();
        
                $time_slot = $this->getTimeSlot($wfh_time); // Get the time range (AM, PM, or full day)
        
                $sql = "INSERT INTO employee_arrangement (Staff_ID, Arrangement_Date, Working_Arrangement, Request_Status, Reason, Working_Location)
                        VALUES (:userID, :wfh_date, :time_slot, 'Pending', :reason, 'Home')";
        
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
                $stmt->bindParam(':wfh_date', $wfh_date);
                $stmt->bindParam(':time_slot', $time_slot);
                $stmt->bindParam(':reason', $reason);
        
                $result = $stmt->execute();
        
                $stmt = null;
                $pdo = null;
        
                return $result;
        
            } catch (PDOException $e) {
                echo "SQL error: " . $e->getMessage();
                return false;
            }
        }
        
        public function submitRecurringWFHRequest($userID, $start_date, $end_date, $recurring_days, $wfh_time, $reason) {
            try {
                $conn = new ConnectionManager();
                $pdo = $conn->getConnection();
        
                $time_slot = $this->getTimeSlot($wfh_time); // Get the time range (AM, PM, or full day)
        
                // Convert start and end dates to timestamps
                $current_date = strtotime($start_date);
                $end_date = strtotime($end_date);
        
                // Loop through each day in the range
                while ($current_date <= $end_date) {
                    $day_of_week = date('l', $current_date); // Get day of the week (e.g., Monday)
        
                    // If this day is in the selected recurring days, insert the request
                    if (in_array($day_of_week, $recurring_days)) {
                        $sql = "INSERT INTO employee_arrangement (Staff_ID, Arrangement_Date, Working_Arrangement, Request_Status, Reason, Working_Location)
                                VALUES (:userID, :arrangement_date, :time_slot, 'Pending', :reason, 'Home')";
        
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
                        $arrangement_date = date('Y-m-d', $current_date);
                        $stmt->bindParam(':arrangement_date', $arrangement_date);
                        $stmt->bindParam(':time_slot', $time_slot);
                        $stmt->bindParam(':reason', $reason);
        
                        $stmt->execute();
                    }
        
                    // Move to the next day
                    $current_date = strtotime('+1 day', $current_date);
                }
        
                $stmt = null;
                $pdo = null;
        
                return true;
        
            } catch (PDOException $e) {
                echo "SQL error: " . $e->getMessage();
                return false;
            }
        }
                 
        
        // Submit a new leave request
    
        public function submitLeaveRequest($userID, $leave_date, $leave_time, $reason) {
            try {
                $conn = new ConnectionManager();
                $pdo = $conn->getConnection();
                
                // Get the time slot (AM, PM, or Full Day)
                $time_slot = $this->getTimeSlot($leave_time);
        
                // Reuse generateReqID function to get a new Request ID
                $newRequestID = $this->generateReqID();
                if ($newRequestID === false) {
                    throw new Exception("Failed to generate new Request ID.");
                }
        
                // Prepare the SQL statement to insert the new leave request
                $sql = "INSERT INTO employee_arrangement (Request_ID, Staff_ID, Arrangement_Date, Working_Arrangement, Request_Status, Reason, Working_Location)
                        VALUES (:newRequestID, :userID, :leave_date, :time_slot, 'Pending', :reason, 'Office')";
        
                $stmt = $pdo->prepare($sql);
                
                // Bind parameters
                $stmt->bindParam(':newRequestID', $newRequestID, PDO::PARAM_INT);
                $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
                $stmt->bindParam(':leave_date', $leave_date);
                $stmt->bindParam(':time_slot', $time_slot);
                $stmt->bindParam(':reason', $reason);
        
                // Execute the statement
                $result = $stmt->execute();
        
                // Close the statement and connection
                $stmt = null;
                $pdo = null;
        
                // Return the result of the execution
                return $result;
        
            } catch (PDOException $e) {
                // Handle any SQL errors
                echo "SQL error: " . $e->getMessage();
                return false;
            } catch (Exception $e) {
                // Handle general exceptions
                echo "Error: " . $e->getMessage();
                return false;
            }
        }
        
            // You may also have other functions to fetch available leave days, etc.
        

        private function getTimeSlot($time_selection) {
            switch($time_selection) {
                case 'AM':
                    return 'AM (9:00 AM - 1:00 PM)';
                case 'PM':
                    return 'PM (1:00 PM - 6:00 PM)';
                case 'full_day':
                default:
                    return 'Full Day (9:00 AM - 6:00 PM)';
            }
        }

        public function deleteRequest($requestId, $staffId, $arrangementDate) {
            $conn = new ConnectionManager();
            $pdo = $conn->getConnection();
        
            // Prepare the SQL statement
            $sql = "DELETE FROM employee_arrangement 
                    WHERE Request_ID = :requestId 
                    AND Staff_ID = :staffId 
                    AND Arrangement_Date = :arrangementDate";
        
            $stmt = $pdo->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':requestId', $requestId, PDO::PARAM_INT);
            $stmt->bindParam(':staffId', $staffId, PDO::PARAM_INT);
            $stmt->bindParam(':arrangementDate', $arrangementDate);
        
            // Execute the statement and return the result
            return $stmt->execute();
        }

        public function retrievePendingArrangements($staffID){
            $conn = new ConnectionManager;
            $pdo = $conn->getConnection();
            
            $sql = "SELECT * FROM employee_arrangement WHERE Staff_ID = :staffID AND Request_Status = 'Pending' AND Arrangement_Date > CURRENT_DATE ORDER BY Arrangement_Date";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':staffID', $staffID, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $results = $stmt->fetchAll(); // Fetch all employees in the department
            
            $stmt = null;
            $pdo = null;

            return $results;
        }
        
        public function approveRequest($requestID){
            $conn = new ConnectionManager;
            $pdo = $conn->getConnection();

            $sql = "UPDATE employee_arrangement SET Request_Status = 'Approved' WHERE Request_ID = :requestID AND Request_Status = 'Pending'";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
            $stmt->execute();
            $affectedRows = $stmt->rowCount();

            $stmt = null;
            $pdo = null;

            if ($affectedRows == 1){
                return true;
            }else{
                return false;
            }
        }

        public function rejectRequest($requestID, $reason){
            $conn = new ConnectionManager;
            $pdo = $conn->getConnection();

            $sql = "UPDATE employee_arrangement SET Request_Status = 'Rejected', Rejection_Reason = :reason WHERE Request_ID = :requestID AND Request_Status = 'Pending'";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
            $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
            $stmt->execute();
            $affectedRows = $stmt->rowCount();

            $stmt = null;
            $pdo = null;

            if ($affectedRows == 1){
                return true;
            }else{
                return false;
            }
        }

        public function retrieveByReqID($requestID){
            $conn = new ConnectionManager();
            $pdo = $conn->getConnection();

            $sql = 'SELECT * FROM employee_arrangement WHERE Request_ID = :requestID';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = null;
            $pdo = null;

            return $result;
        }
        
        public function rejectExpiredRequests(){
            $conn = new ConnectionManager();
            $pdo = $conn->getConnection();

            $sql = "SELECT Request_ID FROM employee_arrangement WHERE Request_Status = 'Pending' AND Arrangement_Date <= CURRENT_DATE";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $expired = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = null;

            $sql = "UPDATE employee_arrangement SET Request_Status = 'Rejected', Rejection_Reason = 'Not Approved past deadline' WHERE Request_ID = :reqID";
            $stmt = $pdo->prepare($sql);
            foreach ($expired as $req){
                $reqID = $req['Request_ID'];
                
                $stmt->bindValue(':reqID', $reqID, PDO::PARAM_INT);
                $stmt->execute();
            }

            $stmt = null;
            $pdo = null;
        }

    }
?>