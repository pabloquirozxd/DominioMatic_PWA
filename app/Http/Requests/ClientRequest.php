<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->website) {
            $website = trim($this->website);

            if (!preg_match("~^(?:f|ht)tps?://~i", $website)) {
                $website = "https://" . $website;
            }

            $this->merge([
                'website' => $website,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'in:company,person',
            ],

            'company_name' => [
                'required_if:type,company',
                'nullable',
                'string',
                'max:255',
            ],

            // Detalles de la Empresa / Cuenta (Mapeado a 'phone' en lugar de 'company_phone')
            'phone'         => ['nullable', 'string', 'max:50'],
            'company_phone' => ['nullable', 'string', 'max:50'], // Mantenido para retrocompatibilidad en payloads
            'tax_id'        => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes'         => ['nullable', 'string', 'max:2000'],

            // Datos del Contacto Principal
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
            'position'   => ['nullable', 'string', 'max:255'],

            'language'       => ['required', 'string', 'max:100'],
            'website'        => ['nullable', 'url', 'max:255'],
            'portal_enabled' => ['boolean'],

            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'            => 'Seleccione si la cuenta es una Empresa o un Cliente Individual.',
            'type.in'                  => 'El tipo de cuenta seleccionado no es válido.',
            'company_name.required_if' => 'El nombre de la empresa es obligatorio para cuentas de tipo Empresa.',
            'first_name.required'      => 'El nombre del contacto principal es obligatorio.',
            'email.email'              => 'Ingrese una dirección de correo electrónico válida.',
            'website.url'              => 'Ingrese una URL de sitio web válida (ej. https://ejemplo.com).',
            'notes.max'                => 'Las notas no pueden superar los 2000 caracteres.',
            'contact_id.exists'        => 'El contacto seleccionado no existe en la base de datos.',
        ];
    }
}