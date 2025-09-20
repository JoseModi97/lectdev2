# Graduate System

The Graduate System is a Yii2-based application designed to manage and track postgraduate student progress efficiently.

## Installation Guide for Development

### Step 1: Clone the Repository

Clone the repository to your local machine:

```bash
$ git clone https://github.com/jarrdim/graduate-system.git
$ cd graduate-system
$ php composer install
```
- ```yml
  YII_DEBUG: true
  YII_ENV: dev 
  ```
Database Configuration

Create or update the /config/db_constants.php file with database credentials. Replace placeholders with actual values:
- ```yml
  defined('ORA_DB_SERVER') or define('ORA_DB_SERVER', '<your_db_server>');
  // defined('ORA_DB_PORT') or define('ORA_DB_PORT', '<your_db_port>');
  defined('ORA_DB_DATABASE') or define('ORA_DB_DATABASE', '<your_db_name>');
  // defined('ORA_DB_SCHEMA') or define('ORA_DB_SCHEMA', '<your_db_schema>');
  defined('ORA_DB_USER') or define('ORA_DB_USER', '<your_db_user>');
  defined('ORA_DB_PASS') or define('ORA_DB_PASS', '<your_db_password>');
  ```

ORA_DB_SERVER: Hostname or IP address of the Oracle database server.
ORA_DB_DATABASE: The name of the Oracle database.
ORA_DB_USER: Database username.
ORA_DB_PASS: Password for the database user.


Step 4: Run the Application

After configuration, you can now run the application locally for development and testing.
