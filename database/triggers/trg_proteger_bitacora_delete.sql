-- Trigger: trg_proteger_bitacora_delete
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

DROP TRIGGER IF EXISTS trg_proteger_bitacora_delete ON seguridad.bitacora_eventos;
CREATE TRIGGER trg_proteger_bitacora_delete
BEFORE DELETE ON seguridad.bitacora_eventos
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_proteger_bitacora();
