# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [Sin versión] - 2026-01-02

### Añadido
- Sistema de autorización basado en `remember_token` para control de acceso a usuarios
- Vista de usuarios (`resources/views/users/index.blade.php`) que muestra lista de usuarios según permisos (solo para admins)
  - **Columna de acciones con botón de activación/desactivación** de usuarios (solo visible para admins)
  - Botones "Activar" (verde) y "Desactivar" (rojo) para cambiar estado `is_active` de usuarios
- Vista detallada (`resources/views/users/detail.blade.php`) con información completa de fichajes para usuarios regulares:
  - Sección de información personal del usuario con badge de estado (Activo/Inactivo)
  - **Banner de advertencia amarillo** para usuarios inactivos explicando que no pueden fichar hasta ser activados
  - Sección "Todos los Fichajes" (desplegable) con tabla completa de registros
  - Sección "Resumen Diario" (desplegables por día) con fichajes cerrados agrupados por fecha
  - Total acumulado mensual de horas trabajadas
  - Botones para expandir/colapsar todos los desplegables
  - Opción de cerrar fichajes abiertos directamente desde la tabla
- **Sistema de control de acceso basado en flag `is_active`**:
  - Solo usuarios con `is_active = true` pueden fichar entrada y salida
  - Los usuarios inactivos reciben mensaje de error al intentar fichar
  - Los usuarios inactivos pueden acceder a `/users` pero no pueden usar el sistema de fichaje
  - Administradores pueden activar/desactivar usuarios mediante botón toggle
- Lógica administrativa: usuarios con `remember_token = 'soyAdm1n'` pueden ver todos los usuarios en formato tabla
- Lógica de usuario regular: usuarios sin privilegios ven su propia información detallada con todos sus fichajes

### Modificado
- **UserController** (`app/Http/Controllers/User/UserController.php`):
  - Método `index()` completamente refactorizado para implementar control de acceso diferenciado:
    - Para admins: muestra vista `users.index` con tabla de todos los usuarios
    - Para usuarios regulares: muestra vista `users.detail` con información completa de fichajes
  - **Nuevo método `toggleActive()`**: Permite activar/desactivar usuarios
    - Recibe el ID del usuario como parámetro
    - Cambia el estado `is_active` del usuario usando modelo Eloquent
    - Redirige a `users.index` con mensaje de éxito personalizado
  - Integración con `GetAuthenticatedUserQuery` para obtener usuario autenticado
  - Integración con `GetUserDailyRegistrosQuery` para obtener resumen diario de fichajes
  - Verificación de `remember_token` en modelo Eloquent para determinar permisos
  - Paso de datos completos de fichajes: `allRegistros`, `dailyRegistros`, `totalMes`

- **RegistroHorarioController** (`app/Http/Controllers/RegistroHorario/RegistroHorarioController.php`):
  - **Método `ficharEntrada()`**:
    - Redirección cambiada de `registro_horario.index` a `users.index`
    - **Validación agregada**: Verifica que el usuario esté activo (`is_active = true`) antes de permitir fichar
    - Si usuario inactivo, retorna error: "Tu cuenta está inactiva. Contacta con un administrador para activarla."
  - **Método `ficharSalida()`**:
    - Redirección cambiada de `registro_horario.index` a `users.index`
    - **Validación agregada**: Verifica que el usuario esté activo antes de permitir cerrar fichaje
    - Mensaje de éxito diferenciado si se cierra un fichaje específico
  - Ahora después de fichar entrada/salida, el usuario es llevado a la página de usuarios

- **Vista Registro Horario** (`resources/views/registro_horario.blade.php`):
  - **Removido botón "Fichar Salida"**: Los usuarios ahora solo pueden fichar entrada desde esta vista
  - **Agregado mensaje informativo**: Cuando hay un fichaje abierto, se muestra un mensaje con enlace a `/users` indicando que pueden cerrar el fichaje desde allí
  - Simplificación de interfaz: Única acción disponible es "Fichar Entrada"

### Técnico
- Flujo de autenticación y autorización en `UserController::index()`:
  1. Se obtiene el usuario autenticado mediante `GetAuthenticatedUserQuery` (DDD Query Bus)
  2. Se consulta el modelo Eloquent para verificar `remember_token`
  3. Se determina si `$isAdmin = ($eloquentUser->remember_token === 'soyAdm1n')`
  4. **Si es admin**:
     - Se ejecuta `GetAllUsersWithTimeQuery` para obtener todos los usuarios
     - Se renderiza vista `users.index` con tabla simple de usuarios
  5. **Si NO es admin**:
     - Se ejecuta `GetUserDailyRegistrosQuery` para obtener resumen diario
     - Se obtienen todos los fichajes mediante `$authenticatedUser->registrosHorarios()`
     - Se renderiza vista `users.detail` con información completa de fichajes

- Queries DDD utilizadas:
  - `GetAuthenticatedUserQuery`: Obtiene usuario autenticado del dominio
  - `GetAllUsersWithTimeQuery`: Obtiene todos los usuarios con información de tiempo (solo admin)
  - `GetUserDailyRegistrosQuery`: Obtiene fichajes agrupados por día y total mensual

- Rutas afectadas:
  - `/users` (GET) - Muestra vista diferenciada según rol (tabla de usuarios o detalle de fichajes)
  - **`/users/{id}/toggle-active` (PATCH) - Nueva ruta para activar/desactivar usuarios (solo admins)**
  - `/registro-horario/entrada` (POST) - Redirige a `/users` después de fichar, **valida is_active**
  - `/registro-horario/salida` (POST) - Redirige a `/users` después de fichar, **valida is_active**

- **Validación de usuarios activos en fichaje**:
  1. Al intentar fichar entrada o salida, se obtiene el usuario autenticado
  2. Se consulta el modelo Eloquent para verificar `is_active`
  3. Si `is_active = false`, se retorna error y se redirige sin crear/cerrar fichaje
  4. El error indica al usuario que su cuenta está inactiva y debe contactar un administrador

### Tests
- **Nuevos tests para funcionalidad `is_active`** en `tests/Feature/User/UserManagementTest.php`:
  - `test_can_toggle_user_active_status()`: Verifica que se puede activar/desactivar un usuario correctamente
    - Crea un usuario y verifica estado inicial activo
    - Desactiva el usuario y verifica cambio en BD
    - Activa el usuario nuevamente y verifica cambio en BD
    - Valida mensajes de éxito apropiados para cada acción

- **Nuevos tests para usuarios inactivos** en `tests/Feature/RegistroHorario/RegistroHorarioTest.php`:
  - `test_inactive_user_cannot_fichar_entrada()`: Verifica que usuarios inactivos no pueden fichar entrada
    - Desactiva usuario autenticado
    - Intenta fichar entrada
    - Verifica error apropiado y que no se creó registro en BD
  - `test_inactive_user_cannot_fichar_salida()`: Verifica que usuarios inactivos no pueden fichar salida
    - Ficha entrada mientras usuario está activo
    - Desactiva usuario
    - Intenta fichar salida
    - Verifica error apropiado

- **Cobertura de tests**: 89 tests pasando con 311 assertions
  - Todos los tests existentes continúan pasando
  - Nuevos tests cubren escenarios de usuarios activos/inactivos
  - Validación completa del flujo de activación/desactivación

### Corregido
- **Bug en vista `users.index.blade.php`**: Error "Call to a member function name() on array"
  - **Causa**: La query `GetAllUsersWithTimeQuery` devuelve un array de arrays (mediante `$user->toArray()`), no objetos del dominio
  - **Solución**: Vista actualizada para acceder a datos mediante notación de array (`$user['name']`) en lugar de métodos (`$user->name()`)
  - **Archivos afectados**:
    - `resources/views/users/index.blade.php`: Cambiado acceso a propiedades de objeto a array
    - `$user->name()` → `$user['name']`
    - `$user->email()->getValue()` → `$user['email']`
    - `$user->uuid()->getValue()` → `$user['uuid']`
    - `$user->isActive()` → `$user['is_active']`

- **26 tests fallaban por falta de autenticación**:
  - **Causa**: Las rutas protegidas por middleware `auth` requerían usuarios autenticados, pero los tests no autenticaban usuarios
  - **Solución**: Actualización completa de todos los tests feature para incluir autenticación
  - **Archivos corregidos**:
    - `tests/Feature/User/UserManagementTest.php`: Agregado usuario admin autenticado en setUp()
    - `tests/Feature/RegistroHorario/RegistroHorarioTest.php`: Agregado usuario autenticado en setUp() y actualizado para usar el usuario autenticado en lugar de pasar userUuid
    - `tests/Feature/Integration/UserRegistroHorarioIntegrationTest.php`: Agregado usuario admin autenticado y actualizado flujo de tests
    - `tests/Feature/ExampleTest.php`: Actualizado para esperar redirección a login en lugar de 200
  - **Cambios específicos en tests**:
    - Creación de usuario autenticado mediante `\App\Models\User::create()` en setUp()
    - Uso de `$this->actingAs($user)` para autenticar al usuario en cada test
    - Eliminación de parámetros `userUuid` en peticiones POST de fichaje (ahora se obtiene del usuario autenticado)
    - Actualización de redirecciones esperadas: `registro_horario.index` → `users.index`
    - Actualización de estructura de vistas esperadas en RegistroHorarioController::index()

- **Modelo User (Eloquent)**: Campo `remember_token` no era fillable
  - **Causa**: El campo `remember_token` no estaba en el array `$fillable`, causando que los tests no pudieran crear usuarios admin
  - **Solución**: Agregado `'remember_token'` al array `$fillable` en `app/Models/User.php`
  - **Impacto**: Ahora los tests pueden crear usuarios admin con `remember_token = 'soyAdm1n'` correctamente

### Notas
- El campo `remember_token` se utiliza temporalmente para control de roles
- Valor especial: `'soyAdm1n'` otorga permisos de administrador
- **Control de acceso mediante `is_active`**:
  - Los usuarios nuevos deben ser activados por un administrador antes de poder fichar
  - Los usuarios inactivos pueden acceder a `/users` para ver su información pero no pueden usar el sistema de fichaje
  - Solo administradores pueden activar/desactivar usuarios mediante el botón toggle en la vista de usuarios
  - Los intentos de fichaje por usuarios inactivos resultan en mensaje de error informativo
- **Flujo de fichaje simplificado**:
  - Desde `/registro-horario`: Solo se puede fichar entrada
  - Desde `/users`: Se pueden cerrar fichajes específicos usando el botón "Cerrar Fichaje" en cada registro abierto
  - Mensaje informativo en `/registro-horario` con enlace a `/users` cuando hay un fichaje abierto
- Los usuarios regulares ven una vista detallada con:
  - Badge de estado (Activo/Inactivo) en información personal
  - Banner de advertencia si están inactivos
  - Todos sus fichajes históricos en tabla desplegable
  - Resumen diario de fichajes cerrados agrupados por fecha
  - Total acumulado del mes actual
  - Capacidad de cerrar fichajes abiertos desde la interfaz
- Los administradores ven una tabla simple con todos los usuarios del sistema
  - Columna adicional con botones de activación/desactivación
  - Acceso completo a gestión de usuarios
- Interfaz completamente responsive con secciones colapsables mediante JavaScript
- **Importante**: Estructura de datos diferente según vista:
  - Vista `users.index` (admin): Trabaja con arrays de usuarios
  - Vista `users.detail` (usuario regular): Trabaja con objetos del dominio User
