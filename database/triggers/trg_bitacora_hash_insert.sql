-- Trigger: trg_bitacora_hash_insert
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

DROP TRIGGER IF EXISTS trg_bitacora_hash_insert ON seguridad.bitacora_eventos;
CREATE TRIGGER trg_bitacora_hash_insert
BEFORE INSERT ON seguridad.bitacora_eventos
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_bitacora_before_insert();
