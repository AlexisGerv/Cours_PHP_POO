<?php
abstract class Personnage
{
  protected $id;
  protected $nom;
  protected $vie = 100;       // Par défaut 100 pv
  protected $experience = 0;  // Par défaut 0 xp (Corrige votre erreur actuelle)
  protected $degats = 25;      // Par défaut 0 dégâts
  protected $atout = 0;
  protected $type;
  protected $timeEndormi = 0; // Par défaut 0 (réveillé)
  protected $niveau = 1;      // Par défaut niveau 1

  const CEST_MOI = 1;
  const PERSONNAGE_TUE = 2;
  const PERSONNAGE_FRAPPE = 3;

  public function __construct(array $donnees)
    {
        $this->setType(strtolower(get_class($this)));

        $this->hydrate($donnees);
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
  /**
     * Calcule l'atout en fonction des dégâts reçus (100 - vie)
     * Règles : 
     * 0-25 dégâts (Vie 75-100) => Atout 4
     * 25-50 dégâts (Vie 50-75) => Atout 3
     * 50-75 dégâts (Vie 25-50) => Atout 2
     * 75-90 dégâts (Vie 10-25) => Atout 1
     * > 90 dégâts  (Vie 0-10)  => Atout 0
     */
    private function calculerAtout()
    {
        $degatsSubis = 100 - $this->vie;

        if ($degatsSubis >= 0 && $degatsSubis < 25) {
            $this->atout = 4;
        } elseif ($degatsSubis >= 25 && $degatsSubis < 50) {
            $this->atout = 3;
        } elseif ($degatsSubis >= 50 && $degatsSubis < 75) {
            $this->atout = 2;
        } elseif ($degatsSubis >= 75 && $degatsSubis < 90) {
            $this->atout = 1;
        } else {
            $this->atout = 0;
        }
    }

  // --- Getters et Setters 
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
 public function SetVie($vie)
    {
        $vie = (int) $vie;
        if ($vie > 100) $vie = 100; // Plafond
        if ($vie < 0) $vie = 0;     // Plancher

        $this->vie = $vie;
        
        // Calcul automatique de l'atout selon les règles du TP
        $this->calculerAtout(); 
    }
  public function SetExperience($experience) { $this->experience = (int) $experience; }
  public function SetDegats($degats) { $this->degats = (int) $degats; }
  public function SetAtout($atout) { $this->atout = (int) $atout; }
  public function SetType($type) { if (is_string($type)) $this->type = $type; }
  public function SetTimeEndormi($time) { $this->timeEndormi = (int) $time; }
  public function SetNiveau($niveau) { $this->niveau = (int) $niveau; }
}
?>