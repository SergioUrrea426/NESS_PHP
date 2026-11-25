-- Trigger: trg_proteger_bitacora_update
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

DROP TRIGGER IF EXISTS trg_proteger_bitacora_update ON seguridad.bitacora_eventos;
CREATE TRIGGER trg_proteger_bitacora_update
BEFORE UPDATE ON seguridad.bitacora_eventos
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_proteger_bitacora();
