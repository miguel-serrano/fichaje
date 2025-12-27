<?php

namespace App\DDD\RegistroHorario\Domain;

class RegistroHorario
{
    public $id;
    public $userId;
    public $entrada;
    public $salida;
    
    public function __construct($id, $userId, $entrada, $salida)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->entrada = $entrada;
        $this->salida = $salida;
    }

    public function isAbierto()
    {
        return $this->salida === null;
    }

    public function segundosTrabajados()
    {
        if ($this->entrada && $this->salida) {
            return strtotime($this->salida) - strtotime($this->entrada);
        }
        return 0;
    }
}

