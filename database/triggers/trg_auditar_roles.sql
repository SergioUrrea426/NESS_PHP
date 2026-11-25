-- Trigger: trg_auditar_roles
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

DROP TRIGGER IF EXISTS trg_auditar_roles ON seguridad.roles;
CREATE TRIGGER trg_auditar_roles
AFTER INSERT OR UPDATE ON seguridad.roles
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_auditar_roles();
