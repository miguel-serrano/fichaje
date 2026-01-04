# Sistema de Activación de Usuarios

## Descripción

El sistema de activación de usuarios controla el acceso a funcionalidades de la aplicación basándose en el estado de activación del usuario y la aceptación de términos y condiciones.

## Campos de Base de Datos

| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| `is_active` | boolean | `false` | Indica si el usuario está activado por un administrador |
| `accepted_terms` | boolean | `false` | Indica si el usuario ha aceptado los términos de la versión beta |

## Flujo de Usuario

```
Usuario se registra
    ↓
is_active = false, accepted_terms = false
    ↓
Redirige a /bienvenido
    ↓
Ve mensaje: "Cuenta pendiente de activación"
    ↓
Marca checkbox y envía → accepted_terms = true
    ↓
Se queda en /bienvenido con confirmación
    ↓
Puede navegar a Seguimiento (/user/me) ✓
Intenta ir a Fichar → Redirige a /bienvenido ✗
    ↓
Admin activa usuario (is_active = true)
    ↓
Usuario puede usar Fichar ✓
```

## Permisos por Estado

| Estado | Seguimiento (`/user/me`) | Fichar (`/registro-horario`) |
|--------|--------------------------|------------------------------|
| `is_active = false` | ✓ Permitido | ✗ Bloqueado |
| `is_active = true` | ✓ Permitido | ✓ Permitido |

## Componentes

### Middleware: `CheckUserActive`

**Ubicación:** `app/Http/Middleware/CheckUserActive.php`

Verifica si el usuario tiene `is_active = true`. Si no, redirige a `/bienvenido` con mensaje de error.

```php
public function handle(Request $request, Closure $next): Response
{
    if (! auth()->user()->is_active) {
        return redirect()->route('bienvenido')
            ->with('error', 'Tu cuenta está pendiente de activación...');
    }
    return $next($request);
}
```

**Registro en Kernel:** `'active' => \App\Http\Middleware\CheckUserActive::class`

### Controlador: `BienvenidoController`

**Ubicación:** `app/Http/Controllers/BienvenidoController.php`

| Método | Ruta | Descripción |
|--------|------|-------------|
| `index()` | GET `/bienvenido` | Muestra la página de bienvenida |
| `acceptTerms()` | POST `/bienvenido/accept-terms` | Guarda `accepted_terms = true` |

### Vista: `bienvenido.blade.php`

**Ubicación:** `resources/views/bienvenido.blade.php`

Muestra contenido dinámico según el estado del usuario:

- **Si `is_active = false`:** Panel amarillo con aviso de cuenta pendiente
- **Si `accepted_terms = false`:** Formulario con checkbox para aceptar términos
- **Si `accepted_terms = true`:** Panel verde de confirmación

### Rutas Protegidas

```php
Route::middleware('active')->group(function () {
    Route::get('/registro-horario', ...);
    Route::post('/registro-horario/entrada', ...);
    Route::post('/registro-horario/salida/{id?}', ...);
});
```

## Migración

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('accepted_terms')->default(false)->after('is_active');
});
```

## Activación por Administrador

Los administradores pueden activar/desactivar usuarios desde:

- **Ruta:** `/users` (listado de usuarios)
- **Acción:** Toggle en el botón de estado activo
- **Endpoint:** `PATCH /user/{id}/toggle-active`

## Configuración del Modelo User

```php
protected $fillable = [
    'uuid', 'name', 'email', 'password',
    'is_active', 'accepted_terms', 'remember_token',
];

protected $casts = [
    'is_active' => 'boolean',
    'accepted_terms' => 'boolean',
];
```

## Entidad de Dominio

En `app/DDD/User/Domain/Entity/User.php`, el método `create()` establece `isActive = false` por defecto:

```php
public static function create(Email $email, string $name): self
{
    return new self(null, Uuid::generate(), $email, $name, false);
}
```
