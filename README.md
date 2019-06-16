# EC
A PHP framework with classes to abstract ActiveRecord atomic persistences, pagination, user ACL and much more.

## Usage
First, clone this repository on your web folder.

#### Create a composer.json for PSR-4 autoloading
**Path:** Project Root
**File contents:** 

{
    "require": {
        "katzgrau/klogger": ">=1.2.1"
    },
    
    "autoload": {
	
        "psr-4": {
            "EC\\": "/src",
        }
    }
}

After that, type *composer install* on your terminal, at the Project's root folder.

### Create a database.ini file for database data
**Path:** Project Root/Site/Configs/database.ini
**File contents:**
[development]
host = 127.0.0.1
charset = utf8mb4
dbname = 
user = 
password = 

[production]
host = 127.0.0.1
charset = utf8mb4
dbname = 
user =
password =

[app]
mode = development
logLevel = INFO ; NOTICE, EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG

### Create a MySQL DB
Fill the Database required info in database.ini file

### Create a index.php
**Path:** Project's root folder
**Contents:**

<?php
use EC\Auth\Models\Role;
use EC\Auth\Models\User;
use EC\Helpers\Config;
use EC\Helpers\Logger;
use EC\Model\Connection;
use EC\Exceptions\ModelNotFoundException;

//SESSION
session_start();

//Locale Português
setlocale(LC_ALL, 'pt_BR.utf8');

//ROOT e AUTOLOAD
define('ROOT', realpath(__DIR__));
define('SITE', ROOT . '/Site');
define('CONFIGS', SITE . '/Configs');
define('LOGS', ROOT . '/storage/logs');
require_once ROOT . '/vendor/autoload.php';

//CONFIGS
$config = new Config;
$config->users_redirect_on_login = "/my-profile";

//LOG E DB
Logger::init($config);
Connection::setConn($config);

## Model Class

### The User class extends the Model class
Let's start with the **User** class, which inherits functionality from the **Model** class.

### Create the users table
**Table name:** users

**Fields:**

id => INT, UN, PK, AI
nickname => VARCHAR(20)
email => VARCHAR(100)
hash => VARCHAR(255)
remember_token => VARCHAR(255)
active => TINYINT(4)
created_at => DATETIME
updated_at => DATETIME

### Create - Add the following code to your index.php file to create new users

$user = new User();
$user->nickname = 'John';
$user->email = 'john@emai.com';
$user->hash = -1;
$user->active = -1;

try {
    $r = $user->save();
} catch (Exception $e) {
    die($e->getMessage());
}

echo $r;

If everything went well, it should echo *1* (true) to the screen, as of *true*, the recording was OK.

### Find By Id
Every class that extends Model class has the ID finder by default.

$id = 1;
$user = User::find($id);
echo $user->nickname;

Obs: As this finder *throws an exception* if the record was not found, we can do this:

$id = 1;
try {
    $user = User::find(1);
} catch (Exception $e) {
    die ($e->getMessage());
}

echo $user->nickname;

### Update

$id = 1;
$user = User::find($id);
$user->nickname = 'Joe';

try {
    $r = $user->save();
} catch (Exception $e) {
    die($e->getMessage());
}

echo $r;

If there's change in the info supplied, record will be updated and *1* will be echoed to the screen.
If we run this code again, *0* (false) will be echoed.
As there's no change, there will be no **update* operation ran in the Database.

OBS: You can verify the last time a record was updated in the updated_at field. This field is updated automatically if registry had changes and was updated.

### Delete

$id = 1;
$user = User::find($id);

try {
    $r = $user->delete();
} catch (Exception $e) {
    die($e->getMessage());
}

echo $r;

Again, 1 for deletion operation completed. 0, for a failed operation. The exceptions in the delete operations almost always occur due to foreign keys that reference the users table; in this case MySQL won't allow deletion of the record.

More to come! Hope you enjoyed so far.