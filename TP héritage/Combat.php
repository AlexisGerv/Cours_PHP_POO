<?php
require_once "connect.php"; // Création de $pdo

// Chargement automatique des classes (plus propre que les require multiples)
spl_autoload_register(function ($class) {
    require_once 'class/' . $class . '.class.php';
});

$manager = new Manager($pdo);

// --- 1. Création ou Récupération des persos ---

// On vérifie si Karthus existe, sinon on le crée
if (!$manager->get("Karthus")) {
    $karthus = new Magicien(['nom' => "Karthus", 'vie' => 100, 'type' => 'Magicien', 'atout' => 10]);
    $manager->add($karthus);
} else {
    $karthus = $manager->get("Karthus");
}

// On vérifie si Conan existe, sinon on le crée
if (!$manager->get("Conan")) {
    $conan = new Guerrier(['nom' => "Conan", 'vie' => 100, 'type' => 'Guerrier', 'atout' => 5]);
    $manager->add($conan);
} else {
    $conan = $manager->get("Conan");
}

// --- 2. Action : Le Magicien lance un sort ---

echo "<h3>Action : Sortilège</h3>";
$karthus->LancerUnSort($conan);

// IMPORTANT : On sauvegarde les modifications dans la BDD !
$manager->update($karthus); // Pour sauvegarder son gain d'atout
$manager->update($conan);   // Pour sauvegarder son état endormi

// --- 3. Action : Tentative d'attaque ---

echo "<h3>Action : Combat</h3>";
// Conan essaie d'attaquer
$conan->Attaquer($karthus); 

if ($conan->estEndormi()) {
    echo "Conan ronfle : " . $conan->reveil();
}

?>