<?php
abstract class Personnage
{
  protected $id;
  protected $nom;
  protected $vie;
  protected $experience;
  protected $degats;
  protected $atout;
  protected $type;
  protected $timeEndormi;
  protected $niveau;

  const CEST_MOI = 1;
  const PERSONNAGE_TUE = 2;
  const PERSONNAGE_FRAPPE = 3;

  public function __construct(array $donnees)
  {
    $this->hydrate($donnees);
    $this->setType(static::class);
  }

  public function hydrate(array $donnees)
  {
    foreach ($donnees as $key => $value) {
      $method = 'Set' . ucfirst($key);
      if (method_exists($this, $method)) {
        $this->$method($value);
      }
    }
  }

  // --- Nouvelle méthode demandée ---
  public function estEndormi()
  {
    // Si le timestamp de réveil est supérieur au temps actuel, il dort encore
    return $this->timeEndormi > time();
  }

  public function reveil()
  {
      // Retourne le temps restant ou une chaine vide
      if ($this->estEndormi()) {
          $secondes = $this->timeEndormi - time();
          $heures = floor($secondes / 3600);
          $secondes -= $heures * 3600;
          $minutes = floor($secondes / 60);
          $secondes -= $minutes * 60;
          
          return $heures . ' heures, ' . $minutes . ' minutes et ' . $secondes . ' secondes';
      }
      return null;
  }

  // --- Combat ---
  public function Attaquer(Personnage $cible)
  {
    // Vérification si le personnage dort
    if ($this->estEndormi()) {
        echo "Le personnage " . $this->nom . " dort encore, il ne peut pas attaquer !<br>";
        return;
    }

    if ($this->id == $cible->id) {
        return self::CEST_MOI;
    }

    // On applique les dégâts
    return $cible->RecevoirDegats($this->degats);
  }

  public function RecevoirDegats($force)
  {
    $this->vie -= (int) $force;

    if ($this->vie <= 0) {
        $this->vie = 0;
        return self::PERSONNAGE_TUE;
    }
    
    return self::PERSONNAGE_FRAPPE;
  }

  // --- Getters et Setters (inchangés mais condensés ici) ---
  public function GetId() { return $this->id; }
  public function GetNom() { return $this->nom; }
  public function GetVie() { return $this->vie; }
  public function GetExperience() { return $this->experience; }
  public function GetDegats() { return $this->degats; }
  public function GetAtout() { return $this->atout; }
  public function GetType() { return $this->type; }
  public function GetTimeEndormi() { return $this->timeEndormi; }
  public function GetNiveau() { return $this->niveau; }

  public function SetId($id) { $this->id = (int) $id; }
  public function SetNom($nom) { if (is_string($nom)) $this->nom = $nom; }
  public function SetVie($vie) { $this->vie = (int) $vie; }
  public function SetExperience($experience) { $this->experience = (int) $experience; }
  public function SetDegats($degats) { $this->degats = (int) $degats; }
  public function SetAtout($atout) { $this->atout = (int) $atout; }
  public function SetType($type) { if (is_string($type)) $this->type = $type; }
  public function SetTimeEndormi($time) { $this->timeEndormi = (int) $time; }
  public function SetNiveau($niveau) { $this->niveau = (int) $niveau; }
}
?>