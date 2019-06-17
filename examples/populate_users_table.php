<?php
use EC\Auth\Models\User;
use EC\Helpers\Config;
use EC\Helpers\Logger;
use EC\Model\Connection;
  
//SESSION  
session_start();  
  
//ROOT e AUTOLOAD  
define('ROOT', realpath(__DIR__ . "/../"));  
define('SITE', ROOT . '/Site');  
define('CONFIGS', SITE . '/Configs');  
define('LOGS', ROOT . '/storage/logs');  
require_once ROOT . '/vendor/autoload.php';  

//CONFIGS  
$config = new Config;  

//LOG AND DB  
Logger::init($config);  
Connection::setConn($config);

//Users
$user1 = new User (['nickname' => 'Joe Doe', 'email' => 'joedoe@email.com', 'hash' => -1, 'active' => -1]);
$user2 = new User (['nickname' => 'Marcondes', 'email' => 'mark@email.com', 'hash' => -1, 'active' => -1]);
$user3 = new User (['nickname' => 'Lucian', 'email' => 'lucian@email.com', 'hash' => -1, 'active' => -1]);
$user4 = new User (['nickname' => 'Lucius Aug.', 'email' => 'luc@email.com', 'hash' => -1, 'active' => -1]);
$user5 = new User (['nickname' => 'Marcus', 'email' => 'marcus@email.com', 'hash' => -1, 'active' => -1]);
$user6 = new User (['nickname' => 'Renato', 'email' => 'renato@email.com', 'hash' => -1, 'active' => -1]);
$user7 = new User (['nickname' => 'Mario O.', 'email' => 'ma@email.com', 'hash' => -1, 'active' => -1]);
$user8 = new User (['nickname' => 'Suzana', 'email' => 'suzana@email.com', 'hash' => -1, 'active' => -1]);
$user9 = new User (['nickname' => 'Suzie Q.', 'email' => 'suzieq@email.com', 'hash' => -1, 'active' => -1]);
$user10 = new User (['nickname' => 'Adele', 'email' => 'adele@email.com', 'hash' => -1, 'active' => -1]);

Connection::beginTransaction();

try {
    $r[] = $user1->save();
    $r[] = $user2->save();
    $r[] = $user3->save();
    $r[] = $user4->save();
    $r[] = $user5->save();
    $r[] = $user6->save();
    $r[] = $user7->save();
    $r[] = $user8->save();
    $r[] = $user9->save();
    $r[] = $user10->save();
} catch (Exception $e) {
    Connection::rollback();
    echo "There was a problem!";
    $l = Logger::getLogger();
    $l->notice('Error populating users table', ['Msg' => $e->getMessage()]);
    //Redirect somewhere
    die;
}

Connection::commit();

if (!in_array(false, $r))
    $msg = 'Yes!';
else
    $msg = 'Nope!';
?>

<p>Users table populated? <?=$msg?></p>
<p>Users inserted: <?=count($r)?></p>
