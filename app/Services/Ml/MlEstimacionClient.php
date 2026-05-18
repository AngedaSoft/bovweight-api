<?php

namespace App\Services\Ml;

/**
 * Contrato del cliente que invoca el microservicio de estimacion.
 *
 * Aplica DIP: el caso de uso de estimacion depende de esta abstraccion,
 * no de Guzzle ni del transporte HTTP concreto. Para pruebas se sustituye
 * por un fake (ver tests/Feature/EstimacionTest.php).
 */
interface MlEstimacionClient
{
    /**
     * @param  array{raza:string,imagen?:string,perimetro_toracico_cm?:float,largo_cuerpo_cm?:float}  $payload
     * @return array<string,mixed>
     *
     * @throws MlEstimacionException
     */
    public function estimar(array $payload): array;
}
