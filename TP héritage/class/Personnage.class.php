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

  // Constantes pour la gestion des combats (valeurs de retour)
  const CEST_MOI = 1; // Si on se frappe soi-même
  const PERSONNAGE_TUE = 2; // Si l'adversaire meurt
  const PERSONNAGE_FRAPPE = 3; // Si le coup est porté

  public function __construct(array $donnees)
  {
    $this->hydrate($donnees);
    // Définit le type automatiquement basé sur le nom de la classe (ex: 'Guerrier')
    // static::class permet de récupérer le nom de la classe fille si hérité
    $this->setType(static::class); 
  }

  // Hydratation : assigne les valeurs aux attributs
  public function hydrate(array $donnees)
  {
    foreach ($donnees as $key => $value) {
      // On récupère le nom du setter correspondant à l'attribut (ex: setNom)
      $method = 'set' . ucfirst($key);

      if (method_exists($this, $method)) {
        $this->$method($value);
      }
    }
  }

  // --- Getters ---

  public function GetId() { return $this->id; }
  public function GetNom() { return $this->nom; }
  public function GetVie() { return $this->vie; }
  public function GetExperience() { return $this->experience; }
  public function GetDegats() { return $this->degats; }
  public function GetAtout() { return $this->atout; }
  public function GetType() { return $this->type; }
  public function GetTimeEndormi() { return $this->timeEndormi; }

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
    if ($vie >= 0 && $vie <= 1000) { // Exemple de limite max, à adapter
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

  public function  SetTimeEndormi($time)
  {
    // Le SQL indique un type DATE, mais on gère ici la donnée brute
    $this->timeEndormi = $time;
  }
}