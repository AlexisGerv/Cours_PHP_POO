<?php
// Autoloader pour charger automatiquement les classes
spl_autoload_register(function ($class) {
    require_once $class . '.class.php';
});

class Manager
{
    private $pdo; // Instance de PDO

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Enregistrer un nouveau personnage
    public function add(Personnage $perso)
    {
        // Préparation de la requête d'insertion en respectant les colonnes du SQL
        $q = $this->pdo->prepare('INSERT INTO personnage(nom, vie, experience, degats, atout, type, timeEndormi, niveau) VALUES(:nom, :vie, :experience, :degats, :atout, :type, :timeEndormi, :niveau)');

        $q->bindValue(':nom', $perso->GetNom());
        $q->bindValue(':vie', $perso->GetVie(), PDO::PARAM_INT);
        $q->bindValue(':experience', $perso->GetExperience(), PDO::PARAM_INT);
        $q->bindValue(':degats', $perso->GetDegats(), PDO::PARAM_INT);
        $q->bindValue(':atout', $perso->GetAtout(), PDO::PARAM_INT);
        $q->bindValue(':type', $perso->GetType());
        $q->bindValue(':timeEndormi', $perso->GetTimeEndormi(), PDO::PARAM_INT);
        $q->bindValue(':niveau', $perso->GetNiveau(), PDO::PARAM_INT);
        $q->execute();

        // On hydrate le personnage avec l'ID généré par la BDD
        $perso->hydrate([
            'id' => $this->pdo->lastInsertId(),
            'degats' => $perso->GetDegats(),
            'atout' => $perso->GetAtout(),
            'type' => $perso->GetType(),
            'timeEndormi' => $perso->GetTimeEndormi(),
            'niveau' => $perso->GetNiveau(),
        ]);
    }

    // Supprimer un personnage
    public function delete(Personnage $perso)
    {
        $this->pdo->exec('DELETE FROM personnage WHERE id = ' . $perso->GetId());
    }

    // Récupérer un personnage par son ID ou son Nom
    public function get($info)
    {
        if (is_int($info)) {
            $q = $this->pdo->query('SELECT id, nom, vie, experience, degats, atout, type, timeEndormi, niveau FROM personnage WHERE id = ' . $info);
        } else {
            $q = $this->pdo->prepare('SELECT id, nom, vie, experience, degats, atout, type, timeEndormi, niveau FROM personnage WHERE nom = :nom');
            $q->execute([':nom' => $info]);
        }

        $donnees = $q->fetch(PDO::FETCH_ASSOC);

        if (!$donnees) {
            return null; // Personnage introuvable
        }

        // Ici, selon le "type" récupéré en BDD, on instancie la bonne classe (Guerrier ou Magicien)
        // C'est le principe du polymorphisme dans le stockage
        $type = $donnees['type'];
        if (class_exists($type)) {
            return new $type($donnees);
        } else {
            return null;
        }
    }


    // Récupérer la liste des personnages (sauf celui passé en paramètre, utile pour choisir un adversaire)
    public function getList($nom)
    {
        $persos = [];

        $q = $this->pdo->prepare('SELECT id, nom, vie, experience, degats, atout, type, timeEndormi, niveau FROM personnage WHERE nom <> :nom ORDER BY nom');
        $q->execute([':nom' => $nom]);

        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $type = $donnees['type'];
            if (class_exists($type)) {
                $persos[] = new $type($donnees);
            } else {
                continue;
            }
        }

        return $persos;
    }

    // Mettre à jour les infos d'un personnage
    public function update(Personnage $perso)
    {
        $q = $this->pdo->prepare('UPDATE personnage SET vie = :vie, experience = :experience, degats = :degats, atout = :atout, timeEndormi = :timeEndormi, niveau = :niveau WHERE id = :id');

        $q->bindValue(':vie', $perso->GetVie(), PDO::PARAM_INT);
        $q->bindValue(':experience', $perso->GetExperience(), PDO::PARAM_INT);
        $q->bindValue(':degats', $perso->GetDegats(), PDO::PARAM_INT);
        $q->bindValue(':atout', $perso->GetAtout(), PDO::PARAM_INT);
        $q->bindValue(':timeEndormi', $perso->GetTimeEndormi(), PDO::PARAM_INT);
        $q->bindValue(':niveau', $perso->GetNiveau(), PDO::PARAM_INT);
        $q->bindValue(':id', $perso->GetId(), PDO::PARAM_INT);

        $q->execute();
    }

    public function count()
    {
        $q = $this->pdo->query('SELECT COUNT(*) FROM personnage');
        return $q->fetchColumn();
    }
    public function AfficherStatPerso($id, $type)
    {
        $q = $this->pdo->prepare('SELECT id, nom, vie, experience, degats, atout, type, timeEndormi, niveau FROM personnage WHERE id = :id');
        $q->execute([':id' => $id]);
        $donnees = $q->fetch(PDO::FETCH_ASSOC);

        if (!$donnees) {
            echo "Personnage introuvable.";
            return;
        }

        if ($donnees['type'] == $type) {
            $perso = new $type($donnees);
            echo "Statistiques de " . $perso->GetNom() . " :";
            echo "<br> Vie : " . $perso->GetVie();
            echo "<br> Experience : " . $perso->GetExperience();
            echo "<br> Degats : " . $perso->GetDegats();
            echo "<br> Atout : " . $perso->GetAtout();
            echo "<br> Type : " . $perso->GetType();
            echo "<br> TimeEndormi : " . $perso->GetTimeEndormi();
            echo "<br> Niveau : " . $perso->GetNiveau();
        }
    }
}