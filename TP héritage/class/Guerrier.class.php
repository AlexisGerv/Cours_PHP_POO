<?php
class Guerrier extends Personnage
{
    public function RecevoirDegats($force)
    {
        // Spécificité Guerrier : Protection grâce à l'atout
        $degats = $force - $this->atout;
        
        // On augmente l'atout (protection) car il s'en est servi
        if ($this->atout < 100) { // On met une limite arbitraire pour pas que ça devienne infini
            $this->atout++;
        }

        if ($degats < 0) {
            $degats = 0;
        }

        // Appel de la méthode parente pour baisser la vie
        return parent::RecevoirDegats($degats);
    }
}
?>