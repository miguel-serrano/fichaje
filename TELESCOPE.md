# Laravel Telescope

## Instalación

Laravel Telescope v5.16.0 está instalado en este proyecto.

## Acceso al Dashboard

El dashboard de Telescope está disponible en:

```
http://localhost/telescope
```

## Configuración

### Archivo de Configuración

La configuración principal se encuentra en `config/telescope.php`.

### Variables de Entorno

- `TELESCOPE_ENABLED`: Habilita/deshabilita Telescope (default: true)
- `TELESCOPE_DOMAIN`: Subdominio opcional para Telescope
- `TELESCOPE_PATH`: Ruta del dashboard (default: 'telescope')

### Service Provider

El service provider está ubicado en `app/Providers/TelescopeServiceProvider.php`.

#### Filtros Configurados

En entorno local, Telescope registra todo. En otros entornos, solo registra:
- Excepciones reportables
- Peticiones fallidas
- Jobs fallidos
- Tareas programadas
- Entradas con tags monitoreados

#### Datos Sensibles Ocultos

En entornos no locales, se ocultan:
- Parámetros: `_token`
- Headers: `cookie`, `x-csrf-token`, `x-xsrf-token`

## Autorización

### Entorno Local

En el entorno `local`, el acceso es completamente libre.

### Entornos No Locales

El acceso está controlado por el gate `viewTelescope` en `TelescopeServiceProvider.php`:

```php
protected function gate(): void
{
    Gate::define('viewTelescope', function ($user) {
        return in_array($user->email, [
            // Añadir emails de usuarios autorizados
        ]);
    });
}
```

Para dar acceso a usuarios específicos en producción, añade sus emails al array.

## Watchers Disponibles

Telescope incluye watchers para:

- **Batch**: Monitorea batch jobs
- **Cache**: Operaciones de caché
- **Command**: Comandos Artisan
- **Dump**: Volcados de variables (dd, dump)
- **Event**: Eventos disparados
- **Exception**: Excepciones y errores
- **Gate**: Autorizaciones
- **HTTP Client**: Peticiones HTTP salientes
- **Job**: Jobs en cola
- **Log**: Logs de la aplicación
- **Mail**: Emails enviados
- **Model**: Operaciones Eloquent
- **Notification**: Notificaciones
- **Query**: Queries SQL
- **Redis**: Comandos Redis
- **Request**: Peticiones HTTP
- **Schedule**: Tareas programadas
- **View**: Renderizado de vistas

## Base de Datos

Telescope utiliza una tabla principal:
- `telescope_entries`: Almacena todas las entradas

## Comandos Útiles

### Limpiar entradas antiguas
```bash
vendor/bin/sail artisan telescope:prune
```

### Pausar grabación
```bash
vendor/bin/sail artisan telescope:pause
```

### Reanudar grabación
```bash
vendor/bin/sail artisan telescope:resume
```

### Limpiar caché
```bash
vendor/bin/sail artisan telescope:clear
```

## Modo Oscuro

Para habilitar el modo oscuro de Telescope, descomenta la siguiente línea en `TelescopeServiceProvider.php`:

```php
public function register(): void
{
    Telescope::night();
    // ...
}
```

## Consideraciones de Rendimiento

- En producción, considera usar `telescope:prune` regularmente para limpiar datos antiguos
- Configura los watchers específicos que necesites en `config/telescope.php`
- Usa el filtro en el service provider para limitar qué se registra

## Documentación Oficial

https://laravel.com/docs/10.x/telescope
