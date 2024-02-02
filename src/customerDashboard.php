<!-- customerDashboard.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles2.css">
    <script type="text/javascript" src="script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
    $(document).ready(function(){
        $('.td-truncated').click(function(){
            $(this).toggleClass('td-truncated-expanded');
        });

         // Add click event for the notification button
         $('.notification-btn').click(function(){
            // Example notification message
            var notificationMessage = "This is a notification message.";

            // Inject notification into the designated div
            $(".notifications").html("<div class='notification'>" + notificationMessage + "</div>");

            // Show the modal below the button
            var btnOffset = $(this).offset();
            var btnHeight = $(this).outerHeight();
            var modalHeight = $('.modal').outerHeight();

            var top = btnOffset.top + btnHeight;
            $('.modal').css('top', top + 'px').show();
        });

        // Close modal when close button is clicked
        $('.close-btn').click(function(){
            $('.modal').hide();
        });
    });
    </script>
</head>
<body>
    <div class="dashboard-container">
       
        <?php
        
        // Start the session
        session_start();
        
        require 'C:\xampp\htdocs\barcode\vendor\autoload.php'; // Adjust the path based on your actual file structure
        // Assuming you have a class named BarcodeGenerator with a static method generateBarcode
        //use Picqer\Barcode\BarcodeGenerator;

        // Check if the customer ID is set in the session
        if (isset($_SESSION['customer_id'])) {
            // Establish a database connection
            $servername = 'NEIL\SQLEXPRESS'; // Replace with the actual server address
            $db_id = ''; // Replace with a dedicated database user
            $db_password = ''; // Replace with the user's password
            $db_name = 'CanteenDB';

            $connectionOptions = [
                "Database" => $db_name,
                "Uid" => $db_id,
                "PWD" => $db_password
            ];

            $con = sqlsrv_connect($servername, $connectionOptions);

            // Check if the connection is successful
            if ($con === false) {
                die('Connection failed: ' . print_r(sqlsrv_errors(), true));
            }

            // Get the customer ID from the session
            $id = $_SESSION['customer_id'];

            // Include the logic for handling the dashboard
            include('dashboardFunctions.php');

            // Fetch customer details
            $customerDetails = getCustomerDetails($con, $id);

            // Display customer details
            if ($customerDetails !== false) {
                //echo '<h2>Welcome to Your Dashboard</h2><br>';
                // Generate barcode for the customer ID
                $barcodeGenerator = new Picqer\Barcode\BarcodeGeneratorHTML();
                $barcode = $barcodeGenerator->getBarcode($id, $barcodeGenerator::TYPE_CODE_128); // Adjust the barcode type as needed

                // Display the barcode
                echo '<h2>Your Barcode</h2>';
                echo '<div class="customer-barcode">';
                echo $barcode;
                echo '</div>';
                echo '<a href="logout.php" class="logout-btn" img src="logout.png" alt="Logout Icon">Logout</a>';
                echo '<div class="customer-details">';
                echo '<div class="customer-image">';
                echo '<img src="data:image/jpeg;base64,' . base64_encode($customerDetails['image']) . '" alt="Customer Image">';
                echo '</div>';
                echo '<div class="customer-info">';
                echo '<p>Hello, ' . $customerDetails['fname'] . ' ' . $customerDetails['lname'] . '!</p>';
                echo '<p>Department: ' . $customerDetails['department'] . '</p>';
                echo '<p>Status: ' . $customerDetails['status'] . '</p>';
                echo '<p><strong>Voucher Balance:</strong><span style="color: green;"><br>₱' . number_format($customerDetails['voucher'], 2) . '</span></p>';
                echo '<p><strong>Credits Amount:</strong><span style="color: red;"><br>₱' . number_format($customerDetails['credit'], 2) . '</span></p>';             
                echo '</div>';
                echo '</div>';
                echo '<div class="notification-btn-container">';
                echo '<button class="notification-btn">Notification</button>';
                echo '</div>';
            } else {
                echo '<p>Error retrieving customer details.</p>';
            }
            
            // Fetch customer product purchase orders
            $customerOrders = getCustomerProductOrders($con, $id);

            // Display product purchase orders
            if ($customerOrders !== false) {
                echo '<h3>View Purchases</h3>';
                echo '<div class="orders-table fixed-header-container">';
                echo '<table class="fixed-header-table" border="1">';
                echo '<thead>';
                echo '<tr>';
                echo '<th class="th-truncated fixed-header-th">Date Purchased</th>';
                echo '<th class="th-truncated fixed-header-th">Product Purchased</th>';
                echo '<th class="th-truncated fixed-header-th">Total Cost</th>';
                echo '<th class="th-truncated fixed-header-th">Voucher Used</th>';
                echo '<th class="th-truncated fixed-header-th">Credits</th>';
                echo '<th class="th-truncated fixed-header-th">Cash</th>';
                echo '<th class="th-truncated fixed-header-th">Cashier Name</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                foreach ($customerOrders as $order) {
                    echo '<tr>';
                    echo '<td class="td-truncated">' . $order['datetimepurchased']->format('Y-m-d g:i A') . '</td>';
                    echo '<td class="td-truncated">' . $order['productpurchased'] . '</td>';
                    echo '<td class="td-truncated">' . $order['amount'] . '</td>';
                    echo '<td class="td-truncated"><span style="color: green;">' . $order['customervoucherused'] . '</span></td>';
                    echo '<td class="td-truncated"><span style="color: red;">' . $order['credit'] . '</span></td>';
                    echo '<td class="td-truncated"><span style="color: red;">' . $order['cash'] . '</span></td>';
                    echo '<td class="td-truncated">' . $order['canteenstaffname'] . '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '<p>No product purchase orders found for this customer.</p>';
            }
        } else {
            echo '<p>Error: Customer ID not provided.</p>';
            echo '<a href="logout.php" class="logout-btn" img src="logout.png" alt="Logout Icon">Back</a>';

        }
        ?>
         
            <!-- Modal -->
            <div class="modal">
                <div class="modal-content">
                    <span class="close-btn">&times;</span>
                    <h3>Notification</h3>
                    <div class="all-notifications">
                        <?php
                        // Fetch and merge both voucher and credit reports
                        $voucherReports = getEmployeeAddedVoucherReports($con, $id);
                        $paycreditReports = getEmployeePaycreditReports($con, $id);

                        $allReports = array_merge($voucherReports, $paycreditReports);

                        // Sort all reports by date in descending order
            usort($allReports, function($a, $b) {
                $dateA = isset($a['datetimevoucheradded']) ? $a['datetimevoucheradded'] : $a['daterecieve'];
                $dateB = isset($b['datetimevoucheradded']) ? $b['datetimevoucheradded'] : $b['daterecieve'];
                
                return $dateB <=> $dateA;
            });

            if (!empty($allReports)) {
                echo '<ul>';
                foreach ($allReports as $report) {
                    echo '<li>';
                    if (isset($report['amountvoucheradded'])) {
                        // Voucher notification
                        $date = isset($report['datetimevoucheradded']) ? $report['datetimevoucheradded']->format('Y-m-d g:i A') : '';
                        echo 'Ang inyong balanseng voucher ay nadagdagan ni ' . $report['accountingname'] . ' noong ' . $date . '.';
                        echo ' Dagdag na Halaga: <span style="color: green;">₱' . number_format($report['amountvoucheradded'], 2);

                    } elseif (isset($report['amountpaid'])) {
                        // Credit notification
                        $date = isset($report['daterecieve']) ? $report['daterecieve']->format('Y-m-d g:i A') : '';
                        echo 'Binayaran ng <span style="color: green;">₱' . number_format($report['amountpaid'], 2) . '</span> mula sa inyong balanseng Credits.';
                        echo ' Petsa ng Bayad: ' . $date . '.';
                    }
                    echo '</span></li>';
                }
                echo '</ul>';
            } else {
                echo '<p>No notifications found.</p>';
            }
            ?>
        </div>
    </div>
</div>
</div>
</body>
</html>