<?php
class Magicien extends Personnage
{

  public function __construct(array $donnees)
  {
    parent::__construct($donnees);
  }

  // --- Combat ---
  public function LancerUnSort(Personnage $cible)
  {
    $duree = $this->atout * 6 * 3600; // Conversion en secondes
    $cible->SetTimeEndormi(time() + $duree);
    echo "Le magicien " . $this->nom . " a lancé un sort sur " . $cible->nom . " et l'a endormi pendant " . $duree . " secondes.";
  }
}