<?php

require_once "class/Personnage.class.php";
require_once "class/Manager.class.php";
require_once "class/Magicien.class.php";
require_once "class/Brute.class.php";
require_once "class/Guerrier.class.php";
require_once "class/Assassin.class.php";
require_once "connect.php";

$manager = new Manager($pdo);

$Karthus = new Magicien([
    'nom' => "Karthus",
    'vie' => 100,
    'experience' => 100,
    'degats' => 10,
    'atout' => 10,
    'type' => "Magicien",
    'timeEndormi' => 0,
    'niveau' => 1,
]);

$Gandalf = new Magicien([
    'nom' => "Gandalf",
    'vie' => 100,
    'experience' => 100,
    'degats' => 10,
    'atout' => 10,
    'type' => "Magicien",
    'timeEndormi' => 0,
    'niveau' => 1,  
]);

$Karthus->LancerUnSort($Gandalf);





