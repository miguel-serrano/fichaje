<?php

namespace App\DDD\RegistroHorario\Services;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Carbon\Carbon;

class RegistroHorarioService
{
    protected UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function ficharEntrada(string $userUuid): void
    {
        $user = $this->repository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $user->ficharEntrada();

        $this->repository->save($user);
    }

    public function ficharSalida(string $userUuid): void
    {
        $user = $this->repository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $user->ficharSalida();

        $this->repository->save($user);
    }

    public function segundosAcumulados(string $userUuid): int
    {
        $user = $this->repository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $today = Carbon::now()->startOfDay();
        $suma = 0;

        foreach ($user->registrosHorarios() as $registro) {
            if (Carbon::instance($registro->entrada())->isSameDay($today)) {
                $suma += $registro->segundosTrabajados();
            }
        }
        
        return $suma;
    }

    public function hasOpenRegistro(string $userUuid): bool
    {
        $user = $this->repository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        foreach ($user->registrosHorarios() as $registro) {
            if ($registro->isAbierto()) {
                return true;
            }
        }
        return false;
    }
}

