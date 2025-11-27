<?php
// 1. D'abord, on charge les classes (Autoloader) pour que PHP comprenne les objets en Session
spl_autoload_register(function ($class) {
    // On vérifie que le fichier existe pour éviter les erreurs fatales
    $file = 'class/' . $class . '.class.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// 2. Ensuite, on démarre la session
session_start();

// 3. Enfin, la connexion BDD
require_once 'connect.php'; 

$manager = new Manager($pdo);
$message = "";



// --- 1. DECONNEXION ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// --- 2. CREATION DE PERSONNAGE ---
if (isset($_POST['creer']) && isset($_POST['nom']) && isset($_POST['type'])) {
    $type = $_POST['type'];
    $nom = htmlspecialchars($_POST['nom']);
    
    // Liste des classes autorisées
    $allowedTypes = ['Guerrier', 'Magicien', 'Brute', 'Assassin'];

    if (in_array($type, $allowedTypes)) {
        // On vérifie si le nom est libre
        // Note: Assurez-vous d'avoir ajouté la méthode exists($nom) dans Manager.class.php
        if (!$manager->exists($nom)) { 
             // On instancie la classe correspondante (Guerrier, Assassin, etc.)
             // Le constructeur de la classe définira les PV et Dégâts par défaut
            $perso = new $type(['nom' => $nom]);
            
            $manager->add($perso);
            $message = "Le personnage <strong>$nom</strong> ($type) a été créé avec succès !";
        } else {
            $message = "Ce nom est déjà pris. Soyez plus original !";
        }
    } else {
        $message = "Type de personnage invalide.";
    }
}

// --- 3. SELECTION DU PERSONNAGE (LOGIN) ---
if (isset($_POST['utiliser']) && isset($_POST['nom'])) {
    if ($manager->exists($_POST['nom'])) {
        $perso = $manager->get($_POST['nom']);
        $_SESSION['perso'] = $perso;
    }
}

// --- 4. ACTIONS DE JEU (Si connecté) ---
if (isset($_SESSION['perso'])) {
    // On rafraichit les données du personnage actuel depuis la BDD (pour avoir la vie à jour)
    $perso = $manager->get($_SESSION['perso']->GetId());
    
    if (!$perso) {
        // Si le perso n'existe plus en BDD (ex: supprimé manuellement), on déconnecte
        session_destroy();
        header('Location: index.php');
        exit();
    }

    // Gestion de l'attaque
    if (isset($_GET['frapper'])) {
        if (!$perso->estEndormi()) {
            $cible = $manager->get((int)$_GET['frapper']);
            
            if ($cible) {
                // On lance l'attaque
                $retour = $perso->Attaquer($cible);

                // Gestion du retour (constantes de la classe Personnage)
                switch ($retour) {
                    case Personnage::CEST_MOI:
                        $message = "Pourquoi voulez-vous vous frapper vous-même ?";
                        break;
                    case Personnage::PERSONNAGE_FRAPPE:
                        $message = $perso->GetNom() . " a frappé " . $cible->GetNom() . " !";
                        
                        // IMPORTANT : On sauvegarde les modifications
                        $manager->update($perso); // Au cas où l'attaquant change (ex: Brute, Assassin)
                        $manager->update($cible); // La cible a perdu de la vie
                        break;
                    case Personnage::PERSONNAGE_TUE:
                        $message = $perso->GetNom() . " a tué " . $cible->GetNom() . " !";
                        $manager->update($perso);
                        $manager->delete($cible); // Paix à son âme
                        break;
                }
            }
        } else {
            $message = "Zzz... Vous dormez encore (" . $perso->reveil() . ")";
        }
    }

    // Gestion du sort (Spécifique Magicien)
    if (isset($_GET['ensorceler']) && $perso->GetType() == 'magicien') { // strtolower(get_class) renvoie minuscule
        if (!$perso->estEndormi()) {
            $cible = $manager->get((int)$_GET['ensorceler']);
            if ($cible) {
                $perso->LancerUnSort($cible);
                $message = $perso->GetNom() . " a lancé un sort sur " . $cible->GetNom();
                $manager->update($perso); 
                $manager->update($cible); 
            }
        } else {
            $message = "Un magicien qui dort ne peut pas incanter !";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Combat POO - L'Arène</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #495057; }
        h1 { text-align: center; color: #343a40; margin-bottom: 30px; }
        .container { max-width: 1100px; margin: 0 auto; }
        
        /* Alert Box */
        .alert { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #ffeeba; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        /* Grid Layout */
        .arena { display: flex; gap: 30px; flex-wrap: wrap; align-items: flex-start; }
        .col { flex: 1; min-width: 320px; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

        /* Forms */
        form { margin-top: 15px; }
        input[type="text"], select { padding: 12px; width: 65%; border: 1px solid #ced4da; border-radius: 6px; margin-right: 5px; box-sizing: border-box; }
        input[type="submit"] { padding: 12px 20px; background: #0d6efd; color: white; border: none; border-radius: 6px; cursor: pointer; transition: background 0.2s; font-weight: bold; }
        input[type="submit"]:hover { background: #0b5ed7; }
        .full-width { width: 100% !important; margin-top: 10px; }

        /* Card Design */
        .perso-card { border: 1px solid #e9ecef; padding: 15px; margin-bottom: 15px; border-radius: 8px; position: relative; background: #fff; transition: transform 0.2s; }
        .perso-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        .my-perso { border: 2px solid #0d6efd; background-color: #f8f9fa; }
        
        /* Stats & Bars */
        .stats-row { display: flex; justify-content: space-between; margin-top: 5px; font-size: 0.9em; }
        .hp-bar-bg { background: #e9ecef; height: 12px; border-radius: 6px; overflow: hidden; margin: 8px 0; }
        .hp-bar-fill { height: 100%; background: #198754; transition: width 0.5s ease-out; }
        
        .stat-badge { padding: 3px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold; color: white; }
        .bg-degats { background-color: #dc3545; }
        .bg-atout { background-color: #0dcaf0; color: #000; }
        .bg-type { background-color: #6c757d; }

        /* Buttons & Actions */
        .actions { margin-top: 15px; display: flex; gap: 5px; }
        .btn { text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.9em; display: inline-block; text-align: center; }
        .btn-attack { background: #dc3545; color: white; }
        .btn-attack:hover { background: #bb2d3b; }
        .btn-spell { background: #6610f2; color: white; }
        .btn-spell:hover { background: #520dc2; }
        
        /* States */
        .sleeping { opacity: 0.7; background-color: #e2e6ea; border-style: dashed; }
        .zzz { position: absolute; top: 10px; right: 10px; font-weight: bold; color: #6610f2; font-size: 1.2em; animation: pulse 2s infinite; }
        
        @keyframes pulse { 0% { opacity: 0.5; } 50% { opacity: 1; } 100% { opacity: 0.5; } }
    </style>
</head>
<body>

<div class="container">
    <h1>⚔️ L'Arène des Héros ⚔️</h1>

    <?php if ($message): ?>
        <div class="alert"><?= $message ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['perso'])): ?>
        <div class="arena">
            <div class="col">
                <h2>🐣 Créer un nouveau Héros</h2>
                <form action="" method="post">
                    <div style="margin-bottom: 10px;">
                        <input type="text" name="nom" placeholder="Nom du héros" required style="width: 100%;">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <select name="type" style="flex: 1;">
                            <option value="Guerrier">🛡️ Guerrier (Tank)</option>
                            <option value="Magicien">🔮 Magicien (Sorts)</option>
                            <option value="Brute">🪓 Brute (Dégâts)</option>
                            <option value="Assassin">🗡️ Assassin (Critique)</option>
                        </select>
                        <input type="submit" name="creer" value="Créer">
                    </div>
                </form>
            </div>
            <div class="col">
                <h2>🗝️ Rejoindre l'arène</h2>
                <form action="" method="post">
                    <select name="nom" class="full-width" style="margin-bottom: 10px;">
                        <?php
                        // On récupère tous les persos (paramètre "" pour ne rien exclure)
                        $persos = $manager->getList(""); 
                        if (empty($persos)) echo "<option disabled>Aucun personnage disponible</option>";
                        foreach ($persos as $unPerso) {
                            echo '<option value="' . $unPerso->GetNom() . '">' . $unPerso->GetNom() . ' (' . ucfirst($unPerso->GetType()) . ')</option>';
                        }
                        ?>
                    </select>
                    <input type="submit" name="utiliser" value="Combattre !" class="full-width">
                </form>
            </div>
        </div>

    <?php else: ?>
        <div style="text-align: right; margin-bottom: 15px;">
            <a href="?logout=1" style="color: #6c757d; text-decoration: none;">🚪 Se déconnecter</a>
        </div>

        <div class="arena">
            <div class="col" style="flex: 0 0 350px;">
                <h2 style="border-bottom: 2px solid #0d6efd; padding-bottom: 10px;">Mon Héros</h2>
                
                <div class="perso-card my-perso">
                    <h3 style="margin: 0 0 10px 0;"><?= $perso->GetNom() ?> <small style="color:gray; font-weight:normal;">(Niv. <?= $perso->GetNiveau() ?>)</small></h3>
                    
                    <span class="stat-badge bg-type"><?= ucfirst($perso->GetType()) ?></span>
                    
                    <?php 
                        // Calcul du pourcentage de vie restant par rapport au MAX de la classe
                        // Utilise la méthode getMaxVie() ajoutée précédemment
                        $maxVie = $perso->getMaxVie();
                        $percentVie = ($perso->GetVie() / $maxVie) * 100;
                    ?>
                    
                    <div style="margin-top: 15px; font-weight: bold; color: #198754;">
                        Santé : <?= $perso->GetVie() ?> / <?= $maxVie ?>
                    </div>
                    <div class="hp-bar-bg">
                        <div class="hp-bar-fill" style="width: <?= $percentVie ?>%;"></div>
                    </div>
                    
                    <div class="stats-row">
                        <span class="stat-badge bg-degats">Dégâts : <?= $perso->GetDegats() ?></span>
                        <span class="stat-badge bg-atout">Atout : <?= $perso->GetAtout() ?></span>
                    </div>

                    <?php if($perso->estEndormi()): ?>
                        <div class="alert" style="margin-top: 15px; font-size: 0.9em;">
                            💤 <strong>Chut !</strong> Vous dormez encore...<br>
                            Réveil dans : <?= $perso->reveil() ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="background: #e9ecef; padding: 15px; border-radius: 8px; font-size: 0.85em; color: #495057;">
                    <strong>ℹ️ Légende des Atouts :</strong>
                    <ul style="padding-left: 20px; margin: 5px 0;">
                        <li>🛡️ <strong>Guerrier</strong> : Réduit les dégâts reçus.</li>
                        <li>🔮 <strong>Magicien</strong> : Durée du sommeil (x6h).</li>
                        <li>🪓 <strong>Brute</strong> : Bonus de force temporaire.</li>
                        <li>🗡️ <strong>Assassin</strong> : Précision (Coup critique).</li>
                    </ul>
                    <em>Note : L'atout change selon vos blessures ! Moins vous avez de vie, plus l'atout baisse.</em>
                </div>
            </div>

            <div class="col">
                <h2 style="border-bottom: 2px solid #dc3545; padding-bottom: 10px;">Adversaires</h2>
                
                <?php
                $adversaires = $manager->getList($perso->GetNom());
                
                if (empty($adversaires)) {
                    echo "<p style='text-align:center; padding: 20px; color: gray;'>Il n'y a personne d'autre dans l'arène... Revenez plus tard !</p>";
                }

                foreach ($adversaires as $adversaire): 
                    $isSleeping = $adversaire->estEndormi();
                    $advMaxVie = $adversaire->getMaxVie();
                    $advPercent = ($adversaire->GetVie() / $advMaxVie) * 100;
                ?>
                    <div class="perso-card <?= $isSleeping ? 'sleeping' : '' ?>">
                        <?php if($isSleeping) echo '<span class="zzz">Zzz</span>'; ?>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong><?= $adversaire->GetNom() ?></strong>
                            <span style="font-size: 0.8em; color: #6c757d;"><?= ucfirst($adversaire->GetType()) ?></span>
                        </div>

                        <div class="hp-bar-bg" style="height: 8px; margin-top: 5px;">
                            <div class="hp-bar-fill" style="width: <?= $advPercent ?>%;"></div>
                        </div>
                        <div style="font-size: 0.8em; text-align: right; color: gray;">
                            PV : <?= $adversaire->GetVie() ?> / <?= $advMaxVie ?> | Atout : <?= $adversaire->GetAtout() ?>
                        </div>

                        <div class="actions">
                            <?php if (!$perso->estEndormi()): ?>
                                <a href="?frapper=<?= $adversaire->GetId() ?>" class="btn btn-attack">⚔️ Attaquer</a>
                                
                                <?php if ($perso->GetType() == 'magicien'): ?>
                                    <a href="?ensorceler=<?= $adversaire->GetId() ?>" class="btn btn-spell">🔮 Sortilège</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="font-size:0.8em; color:gray; font-style:italic; padding: 5px;">(Impossible d'agir en dormant)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>