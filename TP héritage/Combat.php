<?php

require_once "class/Personnage.class.php";
require_once "class/Manager.class.php";
require_once "class/Magicien.class.php";
require_once "class/Brute.class.php";
require_once "class/Guerrier.class.php";
require_once "class/Assassin.class.php";
require_once "connect.php";

$manager = new Manager($pdo);

$Gandalf = new Magicien([
    "nom" => "Gandalf",
    "vie" => 100,
    "experience" => 50,
    "degats" => 20,
    "atout" => 10,
    "type" => "Magicien",
    "timeEndormi" => 0,
]);

$manager->add($Gandalf);
$manager->count();

