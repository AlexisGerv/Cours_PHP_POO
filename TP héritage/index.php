<?php
// 1. D'abord, on charge les classes (Autoloader)
spl_autoload_register(function ($class) {
    require_once 'class/' . $class . '.class.php';
});

// 2. Ensuite, on peut démarrer la session
// PHP connaît maintenant "Guerrier" et peut reconstruire l'objet correctement
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
    
    // Vérification de sécurité sur le type
    if (in_array($type, ['Guerrier', 'Magicien', 'Brute'])) {
        if (!$manager->exists($nom)) { // Note: Assurez-vous d'avoir ajouté la méthode exists() ou utilisez get() qui renvoie null
             // On laisse l'hydratation mettre les valeurs par défaut (vie 100, etc.)
            $perso = new $type(['nom' => $nom]);
            $manager->add($perso);
            $message = "Le personnage $nom a été créé !";
        } else {
            $message = "Ce nom est déjà pris.";
        }
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
    // On rafraichit les données du personnage actuel depuis la BDD
    $perso = $manager->get($_SESSION['perso']->GetId());
    
    if (!$perso) {
        // Si le perso est mort (supprimé de la BDD), on déconnecte
        session_destroy();
        header('Location: index.php');
        exit();
    }

    // Gestion de l'attaque ou du sort
    if (isset($_GET['frapper'])) {
        if (!$perso->estEndormi()) {
            $cible = $manager->get((int)$_GET['frapper']);
            if ($cible) {
                // On lance l'attaque
                $retour = $perso->Attaquer($cible);

                // Gestion du retour (constantes de la classe Personnage)
                switch ($retour) {
                    case Personnage::CEST_MOI:
                        $message = "Mais... pourquoi voulez-vous vous frapper ?";
                        break;
                    case Personnage::PERSONNAGE_FRAPPE:
                        $message = $perso->GetNom() . " a frappé " . $cible->GetNom() . " !";
                        $manager->update($perso); // Mise à jour de l'attaquant (ex: Brute atout)
                        $manager->update($cible); // Mise à jour de la cible (Vie - Atout)
                        break;
                    case Personnage::PERSONNAGE_TUE:
                        $message = $perso->GetNom() . " a tué " . $cible->GetNom() . " !";
                        $manager->update($perso);
                        $manager->delete($cible);
                        break;
                }
            }
        } else {
            $message = "Vous dormez... zzz... (" . $perso->reveil() . ")";
        }
    }

    // Gestion du sort (Spécifique Magicien)
    if (isset($_GET['ensorceler']) && $perso->GetType() == 'Magicien') {
        if (!$perso->estEndormi()) {
            $cible = $manager->get((int)$_GET['ensorceler']);
            if ($cible) {
                $perso->LancerUnSort($cible);
                $message = $perso->GetNom() . " a lancé un sort sur " . $cible->GetNom();
                $manager->update($perso); // MAJ si l'atout change (logique custom)
                $manager->update($cible); // MAJ du timeEndormi
            }
        } else {
            $message = "Un magicien qui dort ne lance pas de sorts !";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Combat POO</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; color: #333; }
        h1 { text-align: center; color: #444; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        /* Message Log */
        .alert { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffeeba; text-align: center;}

        /* Grid System */
        .arena { display: flex; gap: 20px; flex-wrap: wrap; }
        .col { flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        /* Forms */
        input[type="text"], select { padding: 10px; width: 60%; border: 1px solid #ddd; border-radius: 5px; }
        input[type="submit"] { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background: #0056b3; }

        /* Personnage Card */
        .perso-card { border: 1px solid #eee; padding: 10px; margin-bottom: 10px; border-radius: 8px; position: relative; }
        .perso-card.my-perso { border-color: #007bff; background-color: #f8f9fa; }
        
        /* Stats */
        .hp-bar-bg { background: #ddd; height: 10px; border-radius: 5px; overflow: hidden; margin-top: 5px; }
        .hp-bar-fill { height: 100%; background: #28a745; transition: width 0.3s; }
        .stat-badge { font-size: 0.8em; padding: 2px 6px; background: #6c757d; color: white; border-radius: 4px; }
        .stat-atout { background: #17a2b8; }
        
        /* Actions */
        .actions { margin-top: 10px; }
        .btn-attack { text-decoration: none; background: #dc3545; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.9em; }
        .btn-spell { text-decoration: none; background: #6f42c1; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.9em; }
        
        /* Sleep State */
        .sleeping { opacity: 0.6; background-color: #e2e6ea; }
        .zzz { position: absolute; top: 10px; right: 10px; font-weight: bold; color: #6f42c1; }
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
                <h2>Créer un personnage</h2>
                <form action="" method="post">
                    <input type="text" name="nom" placeholder="Nom du héros" required>
                    <select name="type">
                        <option value="Guerrier">Guerrier 🛡️</option>
                        <option value="Magicien">Magicien 🔮</option>
                        <option value="Brute">Brute 🪓</option>
                    </select>
                    <input type="submit" name="creer" value="Créer">
                </form>
            </div>
            <div class="col">
                <h2>Choisir un combattant</h2>
                <form action="" method="post">
                    <select name="nom" style="width: 100%; margin-bottom: 10px;">
                        <?php
                        // Lister tous les persos pour le menu déroulant
                        // Note: Manager::getList a besoin d'un nom à exclure, on met un vide pour tout avoir
                        $persos = $manager->getList(""); 
                        if (empty($persos)) echo "<option disabled>Aucun personnage</option>";
                        foreach ($persos as $unPerso) {
                            echo '<option value="' . $unPerso->GetNom() . '">' . $unPerso->GetNom() . ' (' . $unPerso->GetType() . ')</option>';
                        }
                        ?>
                    </select>
                    <input type="submit" name="utiliser" value="Entrer dans l'arène" style="width: 100%;">
                </form>
            </div>
        </div>

    <?php else: ?>
        <div style="text-align: right; margin-bottom: 10px;">
            <a href="?logout=1" style="color: #666;">Se déconnecter</a>
        </div>

        <div class="arena">
            <div class="col" style="flex: 0 0 300px;">
                <h2>Mon Personnage</h2>
                <div class="perso-card my-perso">
                    <h3><?= $perso->GetNom() ?> <small>(Niv. <?= $perso->GetNiveau() ?>)</small></h3>
                    <p>Type: <strong><?= $perso->GetType() ?></strong></p>
                    
                    <div>Vie: <?= $perso->GetVie() ?>/100</div>
                    <div class="hp-bar-bg">
                        <div class="hp-bar-fill" style="width: <?= $perso->GetVie() ?>%;"></div>
                    </div>
                    
                    <p>
                        <span class="stat-badge">Dégâts: <?= $perso->GetDegats() ?></span>
                        <span class="stat-badge stat-atout">Atout: <?= $perso->GetAtout() ?></span>
                    </p>

                    <?php if($perso->estEndormi()): ?>
                        <div class="alert" style="margin-top: 10px;">
                            💤 Vous dormez encore ! <br>
                            <?= $perso->reveil() ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 20px; font-size: 0.9em; color: #666;">
                    <strong>Légende Atout :</strong><br>
                    🛡️ Guerrier : Réduction dégâts<br>
                    🔮 Magicien : Durée sommeil (x6h)<br>
                    🪓 Brute : Bonus force
                </div>
            </div>

            <div class="col">
                <h2>Adversaires</h2>
                <?php
                $adversaires = $manager->getList($perso->GetNom());
                
                if (empty($adversaires)) {
                    echo "<p>Il n'y a personne d'autre dans l'arène...</p>";
                }

                foreach ($adversaires as $adversaire): 
                    $isSleeping = $adversaire->estEndormi();
                ?>
                    <div class="perso-card <?= $isSleeping ? 'sleeping' : '' ?>">
                        <?php if($isSleeping) echo '<span class="zzz">ZZZ</span>'; ?>
                        
                        <strong><?= $adversaire->GetNom() ?></strong> (<?= $adversaire->GetType() ?>)
                        <div class="hp-bar-bg" style="width: 100px; display: inline-block; vertical-align: middle; margin-left: 10px;">
                            <div class="hp-bar-fill" style="width: <?= $adversaire->GetVie() ?>%;"></div>
                        </div>
                        <span style="font-size: 0.8em; color: #666;"> Atout: <?= $adversaire->GetAtout() ?></span>

                        <div class="actions">
                            <?php if (!$perso->estEndormi()): ?>
                                <a href="?frapper=<?= $adversaire->GetId() ?>" class="btn-attack">⚔️ Attaquer</a>
                                
                                <?php if ($perso->GetType() == 'Magicien'): ?>
                                    <a href="?ensorceler=<?= $adversaire->GetId() ?>" class="btn-spell">🔮 Sort</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="font-size:0.8em; color:gray;">(Vous dormez)</span>
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