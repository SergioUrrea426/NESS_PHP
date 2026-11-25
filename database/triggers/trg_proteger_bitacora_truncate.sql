-- Trigger: trg_proteger_bitacora_truncate
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

DROP TRIGGER IF EXISTS trg_proteger_bitacora_truncate ON seguridad.bitacora_eventos;
CREATE TRIGGER trg_proteger_bitacora_truncate
BEFORE TRUNCATE ON seguridad.bitacora_eventos
FOR STATEMENT
EXECUTE FUNCTION seguridad.fn_proteger_bitacora();
