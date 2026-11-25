-- Trigger: trg_auditar_usuarios
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

DROP TRIGGER IF EXISTS trg_auditar_usuarios ON seguridad.usuarios;
CREATE TRIGGER trg_auditar_usuarios
AFTER UPDATE ON seguridad.usuarios
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_auditar_usuarios();
