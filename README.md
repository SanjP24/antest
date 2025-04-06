# Instructions
Once you have the docker container running (using docker compose up --build):
- navigate to localhost:9000/enterprises.php to load the application
    - the first form is used to upload the CSV - choose the CSV file and select "Import CSV" 
    - this will enter all CSV data into the database, which should then appear a table/list on the page along with a success message and how many records were entered.
- all entered employee data will display with input fields for their email addresses. Changing text for any of these inputs will update their email address, which will persist after page refreshes.
 - scrolling further down the page will show the average salary for each company

# Extra Notes:
To view table data, navigate localhost:8080, with the credentials DB_USER in the compose.yaml and password in the password.txt file to log in to PHPMyAdmin. The database to use is enterprisedata - if the database/corresponding employees table doesnt exist, the included enterprisedata_import SQL file can be run to create these.

DocBlocks have been added to the PHP functions with some comments for future scalability and potential performance implications of the current approach.

This Docker container was created with the templated PHP/Apache container found in their documentation - this is my first attempt at using Docker containers, so there may be some artefacts of the template seen in the directory names/structure and in the composer files.

# Future Considerations
## Frontend:
Take a bit more time to make use of the bootstrap classes - this was mostly to give some styling to the page, with more time I'd add bootstrap to the project instead of relying on a cdn link, or use/learn different CSS frameworks. The styling is fairly bare bones for this application

Add Vue and use components to render the tables, as well as handle the email update functionality. If not this, I would have added a new class/file for the JS to be added to so as to not clutter the enterprise page with too much JS.

## Backend:
There is currently only one class being used for this application. Ideally I'd have a separate class for initialising the database connection, and the CRUD functions for the database. I have listed off some considerations specific to each function in the Employee class - but general idea is to break down the functions further so that they have a single responsiblity and so they can be re-used as the application scales.

Other considerations include adding pagination (this will need Frontend updates as well) and parameters to functions that retrieve data so that we're only ever retrieving/sending data that the pages require to display. This prevents unnecessary data from being processsed and sent (and causing performance issues as the table/database becomes larger).

I would also add validation across the input fields:
 - Email - ensure that whatever text is properly formed
 - CSV upload: ensure that the there are no missing/additional columns for the rows in the CSV, either skip over or return a warning to the user.

I would have also set up the update email functionality to work as an API, using Symfony's Request/Response interfaces to approach the data processing in a more OOP way.

There are no unit tests despite the tests directory - this was planned however I had run out of time - I would have written tests for each function, once the above considerations had been implemented. 
