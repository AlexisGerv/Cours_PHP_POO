<?php
class Brute extends Personnage
{
    const MAX_VIE = 110; // Solide

    public function __construct(array $donnees)
    {
        if (!isset($donnees['vie'])) $donnees['vie'] = self::MAX_VIE;
        if (!isset($donnees['degats'])) $donnees['degats'] = 18; 
        
        parent::__construct($donnees);
    }

    public function Attaquer(Personnage $cible)
    {
        $degatsDeBase = $this->degats;
        
        // La Brute ajoute son atout (force) aux dégâts
        $bonus = $this->atout; 
        $this->degats += $bonus;
        
        echo "La Brute charge ! (Bonus de force : +" . $bonus . ")<br>";
        
        $retour = parent::Attaquer($cible);
        
        // On remet les dégâts normaux
        $this->degats = $degatsDeBase;
        
        return $retour;
    }
}