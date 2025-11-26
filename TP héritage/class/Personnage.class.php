<?php

abstract class Personnage
{
    protected $id;
    protected $nom;
    protected $vie;
    protected $experience;
    protected $degats;
    protected $atout;
    protected $type;
    protected $timeEndormi;
    public function __construct($id, $nom, $vie, $experience, $degats, $atout, $type, $timeEndormi)
    {
        $this->SetId($id);
        $this->SetNom($nom);
        $this->SetVie($vie);
        $this->SetExperience($experience);
        $this->SetDegats($degats);
        $this->SetAtout($atout);
        $this->SetType($type);
        $this->SetTimeEndormi($timeEndormi);
    }

    public function hydrate(array $donnees)
    {
        foreach ($donnees as $key => $value) {
            $method = 'Set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
    public function SetId($id)
    {
        $this->id = $id;
    }
    public function SetNom($nom)
    {
        $this->nom = $nom;
    }
    public function SetVie($vie)
    {
        $this->vie = $vie;
    }
    public function SetExperience($experience)
    {
        $this->experience = $experience;
    }
    public function SetDegats($degats)
    {
        $this->degats = $degats;
    }
    public function SetAtout($atout)
    {
        $this->atout = $atout;
    }
    public function SetType($type)
    {
        $this->type = $type;
    }
    public function SetTimeEndormi($timeEndormi)
    {
        $this->timeEndormi = $timeEndormi;
    }
    public function GetId()
    {
        return $this->id;
    }
    public function GetNom()
    {
        return $this->nom;
    }
    public function GetVie()
    {
        return $this->vie;
    }
    public function GetExperience()
    {
        return $this->experience;
    }
    public function GetDegats()
    {
        return $this->degats;
    }
    public function GetAtout()
    {
        return $this->atout;
    }
    public function GetType()
    {
        return $this->type;
    }
    public function GetTimeEndormi()
    {
        return $this->timeEndormi;
    }

}