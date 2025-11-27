<?php
// class/Guerrier.class.php

class Guerrier extends Personnage
{
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