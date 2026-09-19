<?php

namespace App\Console\Commands;

use App\Models\Mesa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarMesas extends Command
{
    protected $signature = 'mesas:importar';

    protected $description = 'Importa las mesas electorales desde el archivo CSV';

    public function handle(): int
    {
        $archivo = database_path('data/mesas_odpe_lambayeque.csv');

        if (!file_exists($archivo)) {
            $this->error('No se encontró el archivo:');
            $this->error($archivo);

            return Command::FAILURE;
        }

        $handle = fopen($archivo, 'r');

        if (!$handle) {
            $this->error('No se pudo abrir el archivo CSV.');

            return Command::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Leer encabezados
        |--------------------------------------------------------------------------
        */

        $headers = fgetcsv($handle, 0, ',');

        if (!$headers) {
            fclose($handle);

            $this->error('El archivo CSV no contiene encabezados.');

            return Command::FAILURE;
        }

        // Eliminar BOM de UTF-8 si existe.
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        $headers = array_map(
            fn ($header) => trim($header),
            $headers
        );

        $requeridos = [
            'numero_mesa',
            'odpe',
            'departamento',
            'provincia',
            'distrito',
            'pagina_fuente',
            'estado',
        ];

        foreach ($requeridos as $campo) {
            if (!in_array($campo, $headers)) {
                fclose($handle);

                $this->error("Falta la columna '{$campo}' en el CSV.");

                return Command::FAILURE;
            }
        }

        $insertados = 0;
        $actualizados = 0;
        $errores = 0;

        $this->info('Iniciando importación de mesas...');

        DB::beginTransaction();

        try {

            while (($fila = fgetcsv($handle, 0, ',')) !== false) {

                if (count($fila) !== count($headers)) {
                    $errores++;
                    continue;
                }

                $datos = array_combine($headers, $fila);

                $numeroMesa = trim($datos['numero_mesa']);

                /*
                |--------------------------------------------------------------------------
                | Garantizamos siempre 6 dígitos
                |--------------------------------------------------------------------------
                */

                $numeroMesa = str_pad(
                    $numeroMesa,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

                if (!preg_match('/^\d{6}$/', $numeroMesa)) {
                    $errores++;
                    continue;
                }

                $mesa = Mesa::where('numero_mesa', $numeroMesa)->first();

                $valores = [
                    'odpe' => trim($datos['odpe']),
                    'departamento' => trim($datos['departamento']),
                    'provincia' => trim($datos['provincia']),
                    'distrito' => trim($datos['distrito']),

                    'pagina_fuente' =>
                        $datos['pagina_fuente'] !== ''
                            ? (int) $datos['pagina_fuente']
                            : null,

                    'estado' =>
                        trim($datos['estado']) !== ''
                            ? trim($datos['estado'])
                            : 'PENDIENTE',
                ];

                if ($mesa) {

                    $mesa->update($valores);

                    $actualizados++;

                } else {

                    Mesa::create([
                        'numero_mesa' => $numeroMesa,
                        ...$valores,
                    ]);

                    $insertados++;
                }
            }

            DB::commit();

            fclose($handle);

            $this->newLine();

            $this->info('Importación finalizada correctamente.');

            $this->table(
                ['Concepto', 'Cantidad'],
                [
                    ['Nuevas mesas', $insertados],
                    ['Mesas actualizadas', $actualizados],
                    ['Filas con errores', $errores],
                    ['Total en base de datos', Mesa::count()],
                ]
            );

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            DB::rollBack();

            fclose($handle);

            $this->error('Error durante la importación:');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}