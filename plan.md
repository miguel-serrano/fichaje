# Plan de Cambios Realizados

## Índice de Puntos Clave

1.  **Refactorización de Entidades:**
    *   Creación del Value Object `RegistroHorarioId`.
    *   Refactorización de la entidad `RegistroHorario` a un objeto de dominio rico (propiedades privadas, métodos de fábrica, `setId`).
    *   Modificación de la entidad `User` para actuar como `Aggregate Root` de `RegistroHorario`.
2.  **Eliminación de Repositorios:**
    *   Eliminación de `RegistroHorarioRepositoryInterface` y su implementación.
    *   Eliminación del Service Provider asociado a `RegistroHorario`.
3.  **Actualización de Repositorios:**
    *   Implementación de `findByUuid` en `UserRepositoryInterface` y `EloquentUserRepository`.
    *   `EloquentUserRepository` ahora maneja la persistencia transaccional del agregado `User`, incluyendo la inserción/actualización de `RegistroHorario` y la asignación de IDs generados.
4.  **Actualización de la Capa de Aplicación:**
    *   `RegistroHorarioService` refactorizado para depender de `UserRepositoryInterface` y utilizar los métodos del agregado `User`.
    *   Añadido el método `hasOpenRegistro` a `RegistroHorarioService`.
    *   `FicharEntrada`, `FicharSalida` y `ObtenerSegundosAcumulados` delegan al servicio refactorizado.
5.  **Actualización de Controladores:**
    *   `RegistroHorarioController` refactorizado para inyección de dependencias adecuada y uso de `RegistroHorarioService` actualizado.
    *   `UserController` refactorizado para pasar la entidad `User` completa (no solo su array) a la vista `show`.
6.  **Actualización de Migraciones:**
    *   Modificación de la migración `create_registro_horarios_table` para que la clave foránea `user_id` referencie `users.id` (entero) en lugar de `users.uuid`.
7.  **Actualización de Consultas (Queries):**
    *   Modificación de `GetUserDailyRegistrosQuery` para esperar un `int` como `userId`.
    *   Modificación de `GetUserByIdQueryHandler` para devolver la entidad `User` directamente y para realizar el `cast` del ID a `int`.
8.  **Correcciones y Manejo de Errores:**
    *   Eliminación de referencias residuales a `RegistroHorarioRepositoryInterface` en otros Handlers y comandos.
    *   Corrección del error `SQLSTATE[23000]: Integrity constraint violation: 1452 FOREIGN KEY constraint failed` mediante la actualización de la migración.
    *   Corrección del error `Exception: No se puede fichar la entrada para un usuario no guardado.` en tests, asegurando que el usuario se persista antes de manipular sus fichajes.
    *   Manejo de `UserNotFoundException` en `UserController@show`.
    *   Eliminación de la validación errónea de "User has not permissions" en `GetUserByIdQueryHandler`.
9.  **Cambios en la Interfaz de Usuario:**
    *   Redirección de las acciones de fichar (`ficharEntrada`, `ficharSalida`) a la página de detalle del usuario (`users.show/{id}`).
    *   Actualización de la plantilla `resources/views/users/show.blade.php` para mostrar todos los registros de fichajes (`allRegistros`) y el resumen diario (`dailyRegistros`), accediendo a las propiedades de la entidad `User` directamente.

## Lista de Tareas (TODO)

1.  [completed] Refactorizar la entidad RegistroHorario (propiedades privadas, VOs, métodos create/fromPrimitives).
2.  [completed] Crear el Value Object RegistroHorarioId.
3.  [completed] Modificar la entidad User para que actúe como Aggregate Root de RegistroHorario.
4.  [completed] Eliminar la interfaz e implementación del repositorio de RegistroHorario.
5.  [completed] Actualizar UserRepository para gestionar la persistencia del agregado completo.
6.  [completed] Adaptar los Command Handlers para que usen UserRepository.
7.  [completed] Revisar los controladores y el paso de parámetros a los comandos.
8.  [completed] Actualizar las pruebas unitarias y de integración.
9.  [completed] Implementar Command/Query Bus completo con Laravel Tactician.
10. [completed] Refactorizar RegistroHorario a TimeTracking con CQRS puro.
11. [completed] Crear capa de dominio compartido con interfaces y value objects.
12. [completed] Actualizar controladores para usar buses en lugar de handlers directos.
13. [completed] Renombrar bounded contexts para mejor lenguaje de dominio.
14. [completed] Verificar que todos los tests funcionen con la nueva arquitectura.

## Mejoras Arquitectónicas Implementadas (2026-01-01)

### 9. **Implementación de Command/Query Bus Completo:**
   * Creación de `CommandBusInterface` y `QueryBusInterface` en la capa de dominio compartido.
   * Implementación de adaptadores para Laravel Tactician (`LaravelTacticianCommandBus`, `LaravelTacticianQueryBus`).
   * Registro centralizado de commands/queries en `DDDServiceProvider`.
   * Eliminación de inyección directa de handlers en controladores.

### 10. **Refactorización a CQRS Puro:**
   * Conversión de `RegistroHorario` a `TimeTracking` con mejor lenguaje de dominio.
   * Creación de Commands: `ClockInCommand`, `ClockOutCommand`.
   * Creación de Queries: `GetAccumulatedSecondsQuery`, `HasOpenTimeEntryQuery`.
   * Implementación de handlers dedicados para cada command/query.
   * Eliminación de wrappers simples (`FicharEntrada`, `FicharSalida`, `ObtenerSegundosAcumulados`).

### 11. **Capa de Dominio Compartido:**
   * Creación de `Shared` bounded context con interfaces comunes.
   * Implementación de Value Objects base (`StringValueObject`, `IntValueObject`).
   * Separación clara entre puertos (interfaces) y adaptadores (implementaciones).

### 12. **Mejora en Naming y Consistencia:**
   * Renombrado de `RegistroHorario` a `TimeEntry` para mejor expresividad.
   * Renombrado de `RegistroHorarioId` a `TimeEntryId`.
   * Métodos con nombres más expresivos: `clockIn`, `clockOut`, `getAccumulatedSeconds`, `hasOpenTimeEntry`.
   * Consistencia en el uso de `UserRepositoryInterface` en toda la aplicación.

### 13. **Arquitectura Hexagonal:**
   * Separación clara entre dominio, aplicación e infraestructura.
   * Interfaces de dominio implementadas en la capa de infraestructura.
   * Controladores que solo manejan concerns HTTP, delegando lógica a buses.

### 14. **Beneficios Obtenidos:**
   * **Consistencia**: Todos los casos de uso siguen el patrón Command/Query Bus.
   * **Testabilidad**: Separación clara de responsabilidades facilita testing.
   * **Escalabilidad**: Fácil agregar nuevos bounded contexts siguiendo los mismos patrones.
   * **Mantenibilidad**: Código más organizado y predecible.
   * **Expresividad**: Mejor lenguaje de dominio con `TimeTracking` vs `RegistroHorario`.

### 15. **Estructura Final de Bounded Contexts:**
```
app/DDD/
├── Shared/
│   ├── Domain/
│   │   ├── Bus/
│   │   │   ├── CommandBusInterface.php
│   │   │   └── QueryBusInterface.php
│   │   └── ValueObject/
│   │       ├── StringValueObject.php
│   │       └── IntValueObject.php
│   └── Infrastructure/
│       └── Bus/
│           ├── LaravelTacticianCommandBus.php
│           └── LaravelTacticianQueryBus.php
├── User/
│   ├── Application/
│   │   ├── Command/
│   │   ├── Query/
│   │   └── Handler/
│   ├── Domain/
│   └── Infrastructure/
└── TimeTracking/
    ├── Application/
    │   ├── Command/
    │   │   ├── ClockInCommand.php
    │   │   └── ClockOutCommand.php
    │   ├── Query/
    │   │   ├── GetAccumulatedSecondsQuery.php
    │   │   └── HasOpenTimeEntryQuery.php
    │   └── Handler/
    ├── Domain/
    │   ├── TimeEntry.php
    │   └── ValueObjects/
    │       └── TimeEntryId.php
    └── Services/
        └── TimeTrackingService.php
```
