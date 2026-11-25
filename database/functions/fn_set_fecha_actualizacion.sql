-- Function: seguridad.fn_set_fecha_actualizacion
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

CREATE OR REPLACE FUNCTION seguridad.fn_set_fecha_actualizacion()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.fecha_actualizacion := (now() AT TIME ZONE 'America/Bogota');
    RETURN NEW;
END;
$$;
