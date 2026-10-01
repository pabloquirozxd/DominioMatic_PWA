<?php

namespace App\Imports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // WithHeadingRow convierte las cabeceras a minúsculas y usa guiones bajos.
        // Ej: "Cargo / Posición" -> "cargo_posicion"
        
        return new Contact([
            'first_name' => $row['nombre'],
            'last_name'  => $row['apellido'],
            'email'      => $row['correo'],
            'phone'      => $row['telefono'] ?? null,
            'type'       => $row['tipo'] ?? 'primary', // Valor por defecto si viene vacío
            'position'   => $row['cargo_posicion'] ?? null,
        ]);
    }
}