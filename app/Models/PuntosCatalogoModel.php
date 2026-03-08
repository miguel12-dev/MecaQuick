<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Acceso al catálogo de puntos de inspección (puntos_catalogo).
 */
final class PuntosCatalogoModel extends BaseModel
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarActivos(): array
    {
        return $this->fetchAll(
            'SELECT id, numero_punto, categoria, descripcion, unidad_medida
             FROM puntos_catalogo
             WHERE activo = 1
             ORDER BY numero_punto ASC'
        );
    }
}
