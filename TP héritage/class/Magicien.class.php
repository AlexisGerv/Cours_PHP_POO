<?php
class Magicien extends Personnage
{
  public function LancerUnSort(Personnage $cible)
  {
    // Vérification si le magicien dort
    if ($this->estEndormi()) {
        echo "Le magicien dort encore !";
        return;
    }

    $duree = $this->atout * 6 * 3600; // Secondes
    
    // Le sort marche : on augmente l'atout (magie)
    if ($this->atout < 100) {
        $this->atout++;
    }

    $cible->SetTimeEndormi(time() + $duree);
    echo "Le magicien " . $this->nom . " a endormi " . $cible->GetNom() . " (Réveil dans " . ($this->atout * 6) . " heures).<br>";
  }
}
?>