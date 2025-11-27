<?php
class Assassin extends Personnage
{
    const MAX_VIE = 75; 

    public function __construct(array $donnees)
    {
        if (!isset($donnees['vie'])) {
            $donnees['vie'] = self::MAX_VIE;
        }
        if (!isset($donnees['degats'])) {
            $donnees['degats'] = 25; // Très mal
        }
        
        parent::__construct($donnees);
    }

    public function Attaquer(Personnage $cible)
    {
        // Spécialité : Coup Sournois
        // Plus l'atout est élevé (donc moins l'assassin est blessé), plus il est précis
        // Atout 4 (Pleine vie) = Gros bonus de dégâts
        
        $bonus = $this->atout * 2; // Bonus de 0 à 8 points
        
        // On augmente temporairement les dégâts
        $this->degats += $bonus;
        
        echo "L'Assassin surgit de l'ombre ! (Bonus précision : +$bonus) <br>";
        
        $retour = parent::Attaquer($cible);
        
        // On retire le bonus
        $this->degats -= $bonus;
        
        return $retour;
    }
}