-- Script para migrar datos de registro_horarios a time_entries
-- Ejecutar este script manualmente cuando estés listo

-- 1. Copiar todos los datos de registro_horarios a time_entries
INSERT INTO time_entries (id, user_id, entrada, salida, created_at, updated_at)
SELECT 
    id,
    user_id,
    entrada,
    salida,
    COALESCE(created_at, entrada) as created_at,
    COALESCE(updated_at, GREATEST(entrada, COALESCE(salida, entrada))) as updated_at
FROM registro_horarios;

-- 2. Verificar que los datos se copiaron correctamente
-- SELECT COUNT(*) as registro_horarios_count FROM registro_horarios;
-- SELECT COUNT(*) as time_entries_count FROM time_entries;

-- 3. Cuando estés seguro de que todo está bien, puedes eliminar la tabla antigua:
-- DROP TABLE registro_horarios;

-- NOTA: Ejecuta las consultas de verificación antes de eliminar la tabla original
