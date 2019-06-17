# EC
Some PHP classes that can be used to have ORM (Object-relational mapping), follow the ActiveRecord pattern.  
There's other classes for pagination, User ACL and much more.

## Usage
First, clone this repository on your web folder.

#### Create a *composer.json* file for PSR-4 autoloading
**Path:** Project Root (~)  
**File contents:**
```json
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
```

After that, type *composer install* on your terminal, at the Project's root (~) folder.  

**KLogger** is a great Logger utility, there's more about it in https://github.com/katzgrau/KLogger  

### Create a database.ini file for database data  
**Path:** ~/Site/Configs/database.ini  
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
logLevel = NOTICE;  

### Create a MySQL DB  
Fill the Database required info in database.ini file  

### Create an index.php file  
**Path:** ~  
**Contents:**
```PHP
<?php
use EC\Auth\Models\User;  
use EC\Helpers\Config;  
use EC\Helpers\Logger;  
use EC\Model\Connection;  
use EC\Exceptions\ModelNotFoundException;  
  
//SESSION  
session_start();  
  
//ROOT e AUTOLOAD  
define('ROOT', realpath(__DIR__));  
define('SITE', ROOT . '/Site');  
define('CONFIGS', SITE . '/Configs');  
define('LOGS', ROOT . '/storage/logs');  
require_once ROOT . '/vendor/autoload.php';  

//CONFIGS  
$config = new Config;  

//LOG AND DB  
Logger::init($config);  
Connection::setConn($config);
```

## Model Class  

###Conventions
1. Records are identified by an **id** field
2. You can add **created_at** and **updated** fields as *DATETIME* and it will automatically populate Record creation and update times.

### The User class extends Model class    
Let's start with the **User** class, which inherits functionality from the **Model** class.  

### Create the *users* table  
**Table name:** users  
**Fields:**
```SQL
id  INT, UN, PK, AI  
nickname  VARCHAR(20)  
email  VARCHAR(100)  
hash  VARCHAR(255)  
remember_token  VARCHAR(255)  
active  TINYINT(4)  
created_at  DATETIME  
updated_at  DATETIME  
```


### Create  
Add the following code to your index.php file to create new users  
```PHP
$user = new User;  
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
```
If everything went well, it should echo *1* (true) to the screen, as the recording operation was completed.  
If there's an Exception, it will be echoed to the screen and $r will be set to 0 (false).

### Find By id  
Add the following code to *index.php* file:
```PHP
$id = 1;  
$user = User::find($id);  
echo $user->nickname;  
```
Obs: As this finder *throws an exception* if the record was not found, we can do this:  
```PHP
$id = 1;  
try {  
    $user = User::find(1);  
} catch (Exception $e) {  
    die ($e->getMessage());  
}  

echo $user->nickname;  
```
### Update  
```PHP
$id = 1;  
$user = User::find($id);  
$user->nickname = 'Joe';  

try {  
    $r = $user->save();  
} catch (Exception $e) {  
    die($e->getMessage());  
}  

echo $r;  
```
If there's change in the info supplied, record will be updated and *1* will be echoed to the screen.  
If we run this code again, *0* (false) will be echoed.  
As there's no change, the *update* operation won't be ran in the Database.  

OBS: You can verify the last time a record was updated in the *updated_at* field. This field is updated automatically if registry had changes and was updated.  

### Delete  
```PHP
$id = 1;  
$user = User::find($id);  

try {  
    $r = $user->delete();  
} catch (Exception $e) {  
    die($e->getMessage());  
}  

echo $r;  
```
Again, $r is set to *1* for deletion operation completed or *0*, for a failed operation.  
The exceptions in the delete operations almost always occur due to foreign keys that reference the actual user in the users table; in this case MySQL won't allow deletion of the record.  