<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restaura la BD activa a partir de un dump SQL de MariaDB/MySQL.
 *
 * Usar con cuidado: borra todas las tablas existentes antes de importar.
 * Pensado como utilidad de sincronizacion entre los entornos del equipo.
 *
 *  Uso:
 *    php artisan db:restore-from-dump path/al/dump.sql
 *    php artisan db:restore-from-dump path/al/dump.sql --no-drop  (solo importa, no borra)
 */
class RestoreFromDumpCommand extends Command
{
    protected $signature = 'db:restore-from-dump {path : Ruta al archivo .sql} {--no-drop : Saltar el DROP de tablas existentes}';

    protected $description = 'Restaura la BD activa desde un archivo SQL dump (MariaDB/MySQL).';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! is_file($path) || ! is_readable($path)) {
            $this->error("No se puede leer el archivo: {$path}");
            return self::FAILURE;
        }

        $conexion = DB::connection();
        $driver = $conexion->getDriverName();
        if ($driver !== 'mysql') {
            $this->error("Esta herramienta solo soporta MySQL/MariaDB. Driver actual: {$driver}");
            return self::FAILURE;
        }

        $this->info("Conectado a: {$conexion->getDatabaseName()} ({$driver})");

        if (! $this->option('no-drop')) {
            $this->warn('Borrando tablas existentes para evitar choques con el CREATE del dump...');
            $this->dropTodasLasTablas($conexion);
        }

        $this->info("Importando {$path}...");
        $contenido = file_get_contents($path);
        $statements = $this->dividirEnStatements($contenido);

        $bar = $this->output->createProgressBar(count($statements));
        $bar->start();

        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($statements as $stmt) {
                try {
                    DB::unprepared($stmt);
                } catch (\Throwable $e) {
                    $this->newLine();
                    $this->error('Fallo en statement: ' . substr($stmt, 0, 200));
                    throw $e;
                }
                $bar->advance();
            }
        } finally {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
        }

        $bar->finish();
        $this->newLine();
        $this->info('Restore completo.');

        $this->mostrarConteos($conexion);

        return self::SUCCESS;
    }

    private function dropTodasLasTablas($conexion): void
    {
        Schema::disableForeignKeyConstraints();
        $tables = $conexion->select('SHOW TABLES');
        $col = 'Tables_in_' . $conexion->getDatabaseName();
        foreach ($tables as $t) {
            $arr = (array) $t;
            $name = $arr[$col] ?? array_values($arr)[0];
            DB::statement("DROP TABLE IF EXISTS `{$name}`");
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Divide el dump en sentencias por punto y coma teniendo cuidado con
     * los strings comillados y comentarios.
     */
    private function dividirEnStatements(string $sql): array
    {
        $lineas = explode("\n", $sql);
        $statements = [];
        $buffer = '';
        foreach ($lineas as $linea) {
            $trim = trim($linea);
            if ($trim === '' || str_starts_with($trim, '--')) {
                continue;
            }
            $buffer .= $linea . "\n";
            if (str_ends_with($trim, ';')) {
                $statements[] = trim($buffer);
                $buffer = '';
            }
        }
        if (trim($buffer) !== '') {
            $statements[] = trim($buffer);
        }
        return array_filter($statements, fn ($s) => $s !== '');
    }

    private function mostrarConteos($conexion): void
    {
        $tablas = $conexion->select('SHOW TABLES');
        $col = 'Tables_in_' . $conexion->getDatabaseName();
        $this->newLine();
        $this->info('Conteo final por tabla:');
        foreach ($tablas as $t) {
            $arr = (array) $t;
            $nombre = $arr[$col] ?? array_values($arr)[0];
            $n = $conexion->table($nombre)->count();
            $this->line(sprintf('  %-32s %s', $nombre, $n));
        }
    }
}
