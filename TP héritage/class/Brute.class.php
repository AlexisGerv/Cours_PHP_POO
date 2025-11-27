<?php
class Brute extends Personnage
{
    // La Brute utilise son atout pour FRAPPER plus fort
    public function Attaque(Personnage $cible)
    {
        // On récupère les dégâts de base
        $degatsDeBase = $this->degats;
        
        // On ajoute l'atout aux dégâts
        $bonus = $this->atout; 
        
        // On boost temporairement les dégâts le temps de l'attaque
        $this->degats += $bonus;
        
        echo "La Brute charge ! (Bonus de force : +" . $bonus . ")<br>";
        
        // On lance l'attaque standard avec la force augmentée
        $retour = parent::Attaquer($cible);
        
        // On remet les dégâts normaux après le coup
        $this->degats = $degatsDeBase;
        
        return $retour;
    }
}