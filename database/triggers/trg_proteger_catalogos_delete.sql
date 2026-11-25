-- Trigger: trg_proteger_catalogos_delete
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

DROP TRIGGER IF EXISTS trg_proteger_catalogos_delete ON seguridad.catalogos_generales;
CREATE TRIGGER trg_proteger_catalogos_delete
BEFORE DELETE ON seguridad.catalogos_generales
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_proteger_catalogos();
