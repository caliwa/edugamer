<?php

namespace App\Livewire\Videogame\Auth;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate; // <-- Cambiamos la importación
use Livewire\Component;

#[Layout('components.layouts.blank')]
class RegisterComponent extends Component
{
    // --- Cédula ---
    #[Validate('required', message: 'El campo cédula es obligatorio.')]
    #[Validate('string', message: 'La cédula debe ser un texto.')]
    #[Validate('max:255', message: 'La cédula no puede exceder los 255 caracteres.')]
    #[Validate('unique:users,cedula', message: 'Esta cédula ya se encuentra registrada.')]
    public string $cedula = '';

    // --- Nombre de Usuario ---
    #[Validate('required', message: 'El nombre de usuario es obligatorio.')]
    #[Validate('string', message: 'El usuario debe ser un texto.')]
    #[Validate('max:255', message: 'El usuario no puede exceder los 255 caracteres.')]
    #[Validate('unique:users,name', message: 'Este nombre de usuario ya está en uso.')]
    public string $username = '';

    // --- Primer Nombre ---
    #[Validate('required', message: 'El primer nombre es obligatorio.')]
    #[Validate('string', message: 'El primer nombre debe ser un texto.')]
    #[Validate('max:255', message: 'El primer nombre no puede exceder los 255 caracteres.')]
    public string $firstName = '';

    // --- Segundo Nombre (Opcional) ---
    #[Validate('nullable')]
    #[Validate('string', message: 'El segundo nombre debe ser un texto.')]
    #[Validate('max:255', message: 'El segundo nombre no puede exceder los 255 caracteres.')]
    public string $secondName = '';

    // --- Primer Apellido ---
    #[Validate('required', message: 'El primer apellido es obligatorio.')]
    #[Validate('string', message: 'El primer apellido debe ser un texto.')]
    #[Validate('max:255', message: 'El primer apellido no puede exceder los 255 caracteres.')]
    public string $firstSurname = '';

    // --- Segundo Apellido (Opcional) ---
    #[Validate('nullable')]
    #[Validate('string', message: 'El segundo apellido debe ser un texto.')]
    #[Validate('max:255', message: 'El segundo apellido no puede exceder los 255 caracteres.')]
    public string $secondSurname = '';

    // --- Contraseña ---
    #[Validate('required', message: 'La contraseña es obligatoria.')]
    #[Validate('string', message: 'La contraseña debe ser un texto.')]
    #[Validate('min:8', message: 'La contraseña debe tener al menos 8 caracteres.')]
    #[Validate('confirmed', message: 'Las contraseñas no coinciden.')]
    public string $password = '';

    // --- Confirmación de Contraseña ---
    public string $password_confirmation = '';

    public function register()
    {
        // El método validate() leerá automáticamente todos los atributos #[Validate]
        $validated = $this->validate();

        $user = User::create([
            'cedula' => $validated['cedula'],
            'name' => $validated['username'],
            'first_name' => $validated['firstName'],
            'second_name' => $validated['secondName'],
            'first_surname' => $validated['firstSurname'],
            'second_surname' => $validated['secondSurname'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        Flux::toast('¡Bienvenido, ' . $user->name . '! Registro exitoso.', variant: 'success');

        return $this->redirectRoute('menu.home', navigate: true);
    }

    public function render()
    {
        return view('livewire.videogame.auth.register-component');
    }
}