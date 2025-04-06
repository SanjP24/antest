<?php

class Employee {

    private $dbHost;
    private $dbName;
    private $dbUser;
    private $passwordFilePath;

    public $dbConnection;

    public function __construct() {
        $this->dbConnection = $this->getDbConnection();
    }

    /**
     * Initialises Database connection and PDO object used to execute queries
     *
     * @return object
     */

    private function getDbConnection(): object {
        // Retrieve credentials and password
        $this->dbHost = getenv('DB_HOST');
        $this->dbName = getenv('DB_NAME');
        $this->dbUser = getenv('DB_USER');
        $this->passwordFilePath = getenv('PASSWORD_FILE_PATH');

        $db_pass = trim(file_get_contents($this->passwordFilePath));

        // Create a new PDO instance
        $this->dbConnection = new PDO("mysql:host=" . $this->dbHost . ";dbname=" . $this->dbName, $this->dbUser, $db_pass);

        // check if table exists, if not, create
        $this->dbConnection->exec("
            CREATE TABLE IF NOT EXISTS `employees` (
                `employeeid` int(11) NOT NULL AUTO_INCREMENT,
                `companyname` varchar(255) DEFAULT NULL,
                `employeename` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `salary` int(11) DEFAULT NULL,
                `created` timestamp NULL DEFAULT current_timestamp(),
                `lastupdated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`employeeid`),
                KEY `companyname` (`companyname`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
            ");

        return $this->dbConnection;

    }

    
    /**
     * Retrieve all employee data currently in the database
     * 
     * if this were to scale, we would need to define limits and offsets (which would allow for pagination) 
     * to prevent sending large amounts of data (potentially causing browser crashes and performance issues)
     *
     * @return array
     */

    public function getEmployeeData(): array {

        $stmt = $this->dbConnection->query("
            SELECT `employeeid`, `employeename` AS 'Employee Name', `companyname` AS `Company Name`, `email` AS 'Email Address', `salary` AS 'Salary'
            FROM employees
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $employeeData = [];

        // modify data to render on the FE
        foreach ($rows as $row) {
            $employeeData[$row['employeeid']] = $row;

            unset($employeeData[$row['employeeid']]['employeeid']);
        }
        return $employeeData;
    }


    
    /**
     * Insert rows into the employees table, provided by a CSV.
     * 
     * To scale this, we would break this function down by specifying which fields we want to insert.
     * Additionally, we would also check the contents of the CSV itself to ensure that it conforms with the table structure 
     * i.e. rows do no have missing columns (if they do, skip over or return a notice/warning)
     * and potentially allow multiple records to be inserted at once, by using a query builder
     * @param $csvHandle - the processed CSV handle returned by the fopen() call 
     * @return int how many rows were added to the table
     */
    public function insertEmployeeDataByCsv($csvHandle): int {
        $csvData = fgetcsv($csvHandle, 0, ',');

        $headingRow = true;
    
        $insertStmt = $this->dbConnection->prepare("
            INSERT INTO `employees`
            (`companyname`, `employeename`, `email`, `salary`, `created`)
            VALUES
            (:companyname, :employeename, :email, :salary, NOW())
        ");

        $insertCount = 0;

        $this->dbConnection->beginTransaction();
    
        while ($csvData = fgetcsv($csvHandle, 0, ',')) {
        
            if ($headingRow) {
                // do not insert
            }
            else {
                $companyName = $csvData[0];
                $employeeName = $csvData[1];
                $emailAddress = $csvData[2];
                $salary = $csvData[3];
    
                $success = $insertStmt->execute([
                    ':companyname' => $companyName,
                    ':employeename' => $employeeName,
                    ':email' => $emailAddress,
                    ':salary' => $salary
                ]);

                if ($success) {
                    $insertCount++;
                }
            }
    
            $headingRow = false;
        }

        $this->dbConnection->commit();

        return $insertCount;
    }

    /**
     * Update email for a single employee, by id
     * 
     * To scale this, we would break this function down by allowing all fields to be updated (by specifying which field we want to update) 
     * and potentially allow multiple records to be updated at once
     * @param int $employeeid - the id of the employee we want to update
     * @param string $newEmail - the new email address to update to
     * @return bool if the update was successful
     */
    public function updateEmployeeEmail(int $employeeId, string $newEmail): bool {

        $success = $this->dbConnection->prepare("
            UPDATE `employees`
            SET `email` = :email
            WHERE
            `employeeid` = :employeeid
        ")->execute([
            ':email' => $newEmail,
            ':employeeid' => $employeeId
        ]);

        return $success;
    }

    /**
     * Retrieve average salaries grouped by company
     * 
     * if this were to scale, we could add a parameter to specifically retrieve which companies we wanted to get the average salaries for.
     * to once again prevent processing large amounts of data (potentially causing browser crashes and performance issues)
     *
     * @return array
     */
    public function getSalaryAverageByCompany(): array {

        $stmt = $this->dbConnection->query("
            SELECT `companyname` as 'Company Name', AVG(salary) as 'Average Salary' 
            FROM `employees` 
            GROUP BY `companyname`
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $companyData = [];

        foreach ($rows as $row) {
            $companyData[$row['Company Name']] = $row;

            $row['Averge Salary'] = number_format($row['Average Salary'], 2);

            unset($companyData[$row['Company Name']]['Company Name']);
        }

        return $companyData;

    }
}