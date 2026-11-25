-- Trigger: trg_usuarios_fecha_actualizacion
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

DROP TRIGGER IF EXISTS trg_usuarios_fecha_actualizacion ON seguridad.usuarios;
CREATE TRIGGER trg_usuarios_fecha_actualizacion
BEFORE UPDATE ON seguridad.usuarios
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_set_fecha_actualizacion();
