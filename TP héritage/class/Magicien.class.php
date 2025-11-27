<?php
class Magicien extends Personnage
{
    const MAX_VIE = 80; // Un peu fragile

    public function __construct(array $donnees)
    {
        // Stats par défaut si non fournies
        if (!isset($donnees['vie'])) $donnees['vie'] = self::MAX_VIE;
        if (!isset($donnees['degats'])) $donnees['degats'] = 15;
        
        parent::__construct($donnees);
    }

    public function LancerUnSort(Personnage $cible)
    {
        // Règle 1 : Ne peut pas se lancer un sort à lui-même
        if ($this->id == $cible->GetId()) {
            echo "Le magicien est idiot, il essaie de s'ensorceler lui-même !<br>";
            return;
        }

        // Règle 2 : Doit avoir de l'atout (magie)
        if ($this->atout == 0) {
            echo "Le magicien n'a plus assez de magie (Atout 0) !<br>";
            return;
        }

        // Durée du sommeil = Atout * 6 heures
        $duree = ($this->atout * 6) * 3600;

        $cible->SetTimeEndormi(time() + $duree);
        
        echo "Le magicien " . $this->nom . " endort " . $cible->GetNom() . 
             " grâce à son atout de " . $this->atout . " (Durée : " . ($this->atout * 6) . " heures).<br>";
    }
}