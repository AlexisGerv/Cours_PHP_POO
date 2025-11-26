<?php
require_once "Personnage.class.php";
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
        $q = $this->pdo->prepare('INSERT INTO personnage(nom, vie, experience, degats, atout, type, timeEndormi) VALUES(:nom, :vie, :experience, :degats, :atout, :type, :timeEndormi)');

        $q->bindValue(':nom', $perso->GetNom());
        $q->bindValue(':vie', $perso->GetVie(), PDO::PARAM_INT);
        $q->bindValue(':experience', $perso->GetExperience(), PDO::PARAM_INT);
        $q->bindValue(':degats', $perso->GetDegats(), PDO::PARAM_INT);
        $q->bindValue(':atout', $perso->GetAtout(), PDO::PARAM_INT); // Champ spécifique au TP
        $q->bindValue(':type', $perso->GetType()); // Champ spécifique au TP (ex: 'Guerrier', 'Magicien')
        $q->bindValue(':timeEndormi', $perso->GetTimeEndormi(), PDO::PARAM_INT);
        $q->execute();

        // On hydrate le personnage avec l'ID généré par la BDD
        $perso->hydrate([
            'id' => $this->pdo->lastInsertId(),
            'degats' => $perso->GetDegats(),
            'atout' => $perso->GetAtout(),
            'type' => $perso->GetType()
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
            $q = $this->pdo->query('SELECT id, nom, vie, experience, degats, atout, type FROM personnage WHERE id = ' . $info);
        } else {
            $q = $this->pdo->prepare('SELECT id, nom, vie, experience, degats, atout, type FROM personnage WHERE nom = :nom');
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
            // Fallback si la classe spécifique n'existe pas encore
            return new Personnage($donnees);
        }
    }

    // Récupérer la liste des personnages (sauf celui passé en paramètre, utile pour choisir un adversaire)
    public function getList($nom)
    {
        $persos = [];

        $q = $this->pdo->prepare('SELECT id, nom, vie, experience, degats, atout, type FROM personnage WHERE nom <> :nom ORDER BY nom');
        $q->execute([':nom' => $nom]);

        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $type = $donnees['type'];
            if (class_exists($type)) {
                $persos[] = new $type($donnees);
            } else {
                $persos[] = new Personnage($donnees);
            }
        }

        return $persos;
    }

    // Mettre à jour les infos d'un personnage
    public function update(Personnage $perso)
    {
        $q = $this->pdo->prepare('UPDATE personnage SET vie = :vie, experience = :experience, degats = :degats, atout = :atout WHERE id = :id');

        $q->bindValue(':vie', $perso->GetVie(), PDO::PARAM_INT);
        $q->bindValue(':experience', $perso->GetExperience(), PDO::PARAM_INT);
        $q->bindValue(':degats', $perso->GetDegats(), PDO::PARAM_INT);
        $q->bindValue(':atout', $perso->GetAtout(), PDO::PARAM_INT);
        $q->bindValue(':id', $perso->GetId(), PDO::PARAM_INT);

        $q->execute();
    }

    public function count()
    {
        echo "Nombre de personnages : " . $this->pdo->query('SELECT COUNT(*) FROM personnage')->fetchColumn();
    }

    public function setDb(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}