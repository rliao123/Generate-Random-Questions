# Generate-Random-Questions
## Project Description
This project allows authorized users to upload a file containing a list of questions through a PHP program. The questions will be added into the database, ensuring that there will be no duplicate questions. Users can click the "Generate Random Question" button for a question to appear. This can be helpful for users who are preparing for interviews and need an application to randomly give them questions. The project uses JavaScript client-side validation to ensure data integrity and validate input fields. To improve the user experience, sessions are used so the user to stay signed in when they navigate through th eapplication. Password management is integrated so users are only signed in when they enter the correct password. Username uniqueness is enforced so the program checks that the username is unique in the database before allowing the user to successfully create an account.
## Prerequisites
- [XAMPP] (https://www.apachefriends.org/): This project is developed using PHP and uses XAMPP for local development.
### Database Setup
1. Navigate to ../XAMPP/xamppfiles/bin
2. Start MySQL: `./mysql -u root -p`
3. Create user: `CREATE USER 'your_username'@localhost' IDENTIFIED BY 'your_password';`
4. Replace _database_username_ and _database_password_ in login.php with username and password used above
5. Create _question_list_ database: `CREATE DATABASE question_list;`
6. Grant access to the database for user: `GRANT ALL ON question_list.* TO 'your_username'@'localhost';`
7. `quit`




