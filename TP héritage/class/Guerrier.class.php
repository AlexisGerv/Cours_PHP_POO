<?php
class Guerrier extends Personnage
{
    public function RecevoirDegats($force)
    {
        $degats = $force - $this->atout;
        if ($degats < 0) {
            $degats = 0;
        }
        parent::RecevoirDegats($degats);
    }
}
