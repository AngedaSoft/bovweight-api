<?php

namespace Database\Seeders;

use App\Models\Raza;
use Illuminate\Database\Seeder;

class RazaSeeder extends Seeder
{
    /**
     * Catalogo inicial alineado al microservicio bovweight-ml-service.
     * Los codigos deben coincidir (en mayusculas) con los del catalogo Python.
     */
    private const RAZAS = [
        ['BRAHMAN',   ['factor_k' => 10800, 'peso_min' => 180, 'peso_max' => 950]],
        ['BRANGUS',   ['factor_k' => 10500, 'peso_min' => 180, 'peso_max' => 900]],
        ['GYR',       ['factor_k' => 11000, 'peso_min' => 170, 'peso_max' => 850]],
        ['HOLSTEIN',  ['factor_k' => 10200, 'peso_min' => 200, 'peso_max' => 900]],
        ['JERSEY',    ['factor_k' => 10800, 'peso_min' => 150, 'peso_max' => 600]],
        ['CHAROLAIS', ['factor_k' => 10400, 'peso_min' => 200, 'peso_max' => 1100]],
        ['ANGUS',     ['factor_k' => 10600, 'peso_min' => 200, 'peso_max' => 1000]],
        ['CRIOLLO',   ['factor_k' => 10700, 'peso_min' => 150, 'peso_max' => 700]],
    ];

    public function run(): void
    {
        foreach (self::RAZAS as [$nombre, $parametros]) {
            Raza::updateOrCreate(
                ['nombre' => $nombre],
                ['parametros_morfologicos' => $parametros],
            );
        }
    }
}
