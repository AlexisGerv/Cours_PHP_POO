<?php

abstract class Personnage
{
  // Attributs correspondants aux champs de la BDD
  protected $id;
  protected $nom;
  protected $vie;
  protected $experience;
  protected $degats;
  protected $atout; // Attribut spécifique (ex: Puissance magique ou Protection)
  protected $type;  // 'Guerrier', 'Magicien', etc.
  protected $timeEndormi;
  protected $niveau;

  // Constantes pour la gestion des combats (valeurs de retour)
  const CEST_MOI = 1; // Si on se frappe soi-même
  const PERSONNAGE_TUE = 2; // Si l'adversaire meurt
  const PERSONNAGE_FRAPPE = 3; // Si le coup est porté

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

  // --- Getters ---

  public function GetId()
  {
    return $this->id;
  }
  public function GetNom()
  {
    return $this->nom;
  }
  public function GetVie()
  {
    return $this->vie;
  }
  public function GetExperience()
  {
    return $this->experience;
  }
  public function GetDegats()
  {
    return $this->degats;
  }
  public function GetAtout()
  {
    return $this->atout;
  }
  public function GetType()
  {
    return $this->type;
  }
  public function GetTimeEndormi()
  {
    return $this->timeEndormi;
  }
  public function GetNiveau()
  {
    return $this->niveau;
  }

  // --- Setters ---

  public function SetId($id)
  {
    $id = (int) $id;
    if ($id > 0) {
      $this->id = $id;
    }
  }

  public function SetNom($nom)
  {
    if (is_string($nom)) {
      $this->nom = $nom;
    }
  }

  public function SetVie($vie)
  {
    $vie = (int) $vie;
    if ($vie > 0) {
      $this->vie = $vie;
    }
  }

  public function SetExperience($experience)
  {
    $experience = (int) $experience;
    if ($experience >= 0) {
      $this->experience = $experience;
    }
  }

  public function SetDegats($degats)
  {
    $degats = (int) $degats;
    if ($degats >= 0) {
      $this->degats = $degats;
    }
  }

  public function SetAtout($atout)
  {
    $atout = (int) $atout;
    if ($atout >= 0) {
      $this->atout = $atout;
    }
  }

  public function SetType($type)
  {
    if (is_string($type)) {
      $this->type = $type;
    }
  }

  public function SetTimeEndormi($time)
  {
    $this->timeEndormi = $time;
  }

  public function SetNiveau($niveau)
  {
    $niveau = (int) $niveau;
    if ($niveau >= 0) {
      $this->niveau = $niveau;
    }
  }

  //--- Combat ---
  public function Attaquer(Personnage $cible)
  {
    $cible->RecevoirDegats($this->GetDegats());
  }

  public function RecevoirDegats($force)
  {
    $force = (int) $force;
    if ($force >= 0) {
      $this->vie -= $force;
    }
  }

}