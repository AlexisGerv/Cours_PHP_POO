<?php
class Guerrier extends Personnage
{
    const MAX_VIE = 140; // Sac à PV

    public function __construct(array $donnees)
    {
        if (!isset($donnees['vie'])) $donnees['vie'] = self::MAX_VIE;
        if (!isset($donnees['degats'])) $donnees['degats'] = 10; // Frappe peu fort
        parent::__construct($donnees);
    }
    public function RecevoirDegats($force)
    {
        // Si le guerrier a un atout > 0, il réduit les dégâts
        if ($this->atout > 0) {
            echo "Le Guerrier utilise son bouclier (Atout " . $this->atout . ") !<br>";
            $force -= $this->atout;
        }

        // On s'assure que les dégâts ne sont pas négatifs (soin)
        if ($force < 0) {
            $force = 0;
        }

        // On appelle la méthode parente pour appliquer la perte de vie réelle
        return parent::RecevoirDegats($force);
    }
}