<?php

$action = $_GET['action'] ?? '';
header('Content-type: application/json');

if ($action == 'updateemail') {
    // save it to database

    $data = json_decode(file_get_contents('php://input'), true);

    // var_dump($data);

    include_once('classes/employee.php');

    $employeeObj = new Employee;

    $result = $employeeObj->updateEmployeeEmail($data['employeeid'], $data['email']);

    echo json_encode($result);
}
