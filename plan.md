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
