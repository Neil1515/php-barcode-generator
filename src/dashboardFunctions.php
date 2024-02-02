<!-- dashboardFunctions.php -->
<?php

function getCustomerDetails($con, $customerId) {
    $query = "SELECT * FROM tblcustomer WHERE Id = ?";
    $stmt = sqlsrv_prepare($con, $query, array(&$customerId));

    if ($stmt) {
        sqlsrv_execute($stmt);
        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($result) {
            return $result;
        }
    }

    return false;
}
function getCustomerProductOrders($con, $customerId) {
    $query = "SELECT * FROM productpurchaseorder WHERE customerId = ? ORDER BY datetimepurchased DESC";
    $stmt = sqlsrv_prepare($con, $query, array(&$customerId));

    if ($stmt) {
        sqlsrv_execute($stmt);

        $customerOrders = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $customerOrders[] = $row;
        }

        return $customerOrders;
    }

    return false;
}

function getEmployeeAddedVoucherReports($con, $customerId) {
    $query = "SELECT * FROM tblcustomervouchereports WHERE customerId = ? ORDER BY datetimevoucheradded DESC";
    $stmt = sqlsrv_prepare($con, $query, array(&$customerId));

    if ($stmt) {
        sqlsrv_execute($stmt);

        $voucherReports = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $voucherReports[] = $row;
        }

        return $voucherReports;
    }

    return false;
}

function getEmployeePaycreditReports($con, $customerId) {
    $query = "SELECT * FROM paycreditreportstbl WHERE customernameid = ? ORDER BY daterecieve DESC";
    $stmt = sqlsrv_prepare($con, $query, array(&$customerId));

    if ($stmt) {
        sqlsrv_execute($stmt);

        $paycreditReports = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $paycreditReports[] = $row;
        }

        return $paycreditReports;
    }

    return false;
}

?>