<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
<?php

include_once('classes/employee.php');

$employeeObj = new Employee;
$action = $_GET['action'] ?? '';

// if the user has submitted a CSV via the form
if (!empty($action) && is_uploaded_file($_FILES['csv']['tmp_name'] ?? '')) {
    $csvHandle = fopen($_FILES['csv']['tmp_name'], 'r');
    $rowsAdded = $employeeObj->insertEmployeeDataByCsv($csvHandle);
}

// Display data
$employeeData = $employeeObj->getEmployeeData();

$headings = (!empty($employeeData)) ? array_keys(current($employeeData)) : [] ;


// get salary averages for each company:
$companyData = $employeeObj->getSalaryAverageByCompany();
$companyNames = array_keys($companyData);
?>
<!-- CSV Upload Form -->
<h1>
    Upload Your CSV Below:
</h1>
<div class="form-group">
    <form method="POST" action="?action=uploadcsv" enctype="multipart/form-data">
        <div>
            <input type="file" name="csv" id="csv" required/>
        </div>

        <button type="submit" class="btn btn-primary">Import CSV</button>
    </form>
</div>
<?php
if (isset($rowsAdded)) {
    ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?=$rowsAdded?> Employees saved from CSV
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </div>
    <?php
}
?>
<!-- Display List of Employees -->
<h1>
    Employees
</h1>

<?php
if (empty($employeeData)) {
    ?>
    <h3>
        No Employee Data Found - Please upload a csv
    </h3>
    <?php
}
?>

<table class="table table-striped">
    <?php
    foreach ($headings as $heading) {
        ?>
        <th>
            <?=$heading?>
        </th>
        <?php
    }
    foreach ($employeeData as $employeeId => $rowDetail) {
        ?>
        <tr id="employee_<?=$employeeId?>">
        <?php
        foreach ($rowDetail as $rowKey => $rowValue) {
            if ($rowKey == 'Email Address') {
                ?>
                <td>
                    <input type="email" class="email-update" id="email-<?=$employeeId?>" data-employeeid="<?=htmlentities($employeeId)?>"value="<?=htmlentities($rowValue)?>">
                </td>
                <?php
            }
            else {
                ?>
                <td class="<?=$rowKey?>"><?=htmlentities($rowValue)?></td>
                <?php
            }
            ?>
            
            <?php

        }
        ?>

        </tr>
        <?php
    }
    ?>
</table>

<!-- Display Salary Averages across each company -->
<h1>
    Average Salary By Company
</h1>

<?php
if (empty($companyData)) {
    ?>
    <h3>
        No Company Data Found - Please upload a csv
    </h3>
    <?php
}
?>

<table class='table table-striped'>
    <?php
    foreach ($companyNames as $companyName) {
        ?>
        <th><?=$companyName?></th>
        <?php
    }
    ?>
    <tr>
    <?php
    foreach ($companyData as $companyName => $companyData) {
        ?>
        
            <?php
            foreach ($companyData as $companyKey => $companyValue) {
                ?>
                <td class="<?=$companyKey?>"><?=$companyValue?></td>
                <?php
            }
            ?>
        <?php
    }
    ?>
    </tr>

</table>

</body>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<script>
    var emailInputs = document.getElementsByClassName("email-update");

    var updateEmail = function() {
        var employeeid = this.getAttribute("data-employeeid");
        var email = this.value;

        var updateData = {
            employeeid: employeeid,
            email: email
        }

        fetch("dataupdate.php?action=updateemail", {
            method: "POST",
            headers: {'Content-Type': 'application/json'}, 
            body: JSON.stringify(updateData)
        }).then(res => {
            if (res.ok) {
                alert("Employee Email Updated!");
                
            } else {
                alert("Employee Update Failed, please try again");
            }
        });
    };

    for (var iterator = 0; iterator < emailInputs.length; iterator++) {
        emailInputs[iterator].addEventListener('change', updateEmail, false);
    }

</script>