-- Function: seguridad.fn_bitacora_before_insert
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE OR REPLACE FUNCTION seguridad.fn_bitacora_before_insert()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_prev_hash VARCHAR(128);
    v_payload   TEXT;
BEGIN
    IF NEW.fecha_evento IS NULL THEN
        NEW.fecha_evento := (now() AT TIME ZONE 'America/Bogota');
    END IF;

    SELECT hash_integridad
      INTO v_prev_hash
      FROM seguridad.bitacora_eventos
     ORDER BY id_evento DESC
     LIMIT 1;

    v_prev_hash := COALESCE(v_prev_hash, 'GENESIS_NESS_SOFT');

    v_payload := concat_ws('|',
        v_prev_hash,
        NEW.tipo_evento::TEXT,
        COALESCE(NEW.id_usuario::TEXT, 'NULL'),
        COALESCE(NEW.modulo, ''),
        COALESCE(NEW.entidad, ''),
        COALESCE(NEW.id_entidad::TEXT, 'NULL'),
        COALESCE(NEW.accion, ''),
        COALESCE(NEW.estado_anterior, ''),
        COALESCE(NEW.estado_nuevo, ''),
        COALESCE(NEW.direccion_ip, ''),
        COALESCE(NEW.sesion::TEXT, ''),
        COALESCE(NEW.severidad, ''),
        COALESCE(NEW.observaciones, ''),
        to_char(NEW.fecha_evento, 'YYYY-MM-DD"T"HH24:MI:SS.US')
    );

    NEW.hash_integridad := encode(digest(v_payload, 'sha256'), 'hex');
    RETURN NEW;
END;
$$;
