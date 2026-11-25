-- Function: seguridad.fn_registrar_evento
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE OR REPLACE FUNCTION seguridad.fn_registrar_evento(
    p_tipo_evento     tipo_evento_bitacora_enum,
    p_id_usuario      INT,
    p_modulo          VARCHAR(30),
    p_entidad         VARCHAR(30),
    p_id_entidad      INT,
    p_accion          VARCHAR(20),
    p_estado_anterior VARCHAR(30) DEFAULT NULL,
    p_estado_nuevo    VARCHAR(30) DEFAULT NULL,
    p_direccion_ip    VARCHAR(45) DEFAULT NULL,
    p_user_agent      VARCHAR(255) DEFAULT NULL,
    p_sesion          UUID DEFAULT NULL,
    p_severidad       VARCHAR(20) DEFAULT 'MEDIA',
    p_observaciones   VARCHAR(255) DEFAULT NULL
) RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
    v_prev_hash VARCHAR(128);
    v_payload   TEXT;
    v_hash      VARCHAR(128);
    v_id_evento BIGINT;
    v_fecha     TIMESTAMP := (now() AT TIME ZONE 'America/Bogota');
BEGIN
    SELECT hash_integridad
      INTO v_prev_hash
      FROM seguridad.bitacora_eventos
     ORDER BY id_evento DESC
     LIMIT 1;

    v_prev_hash := COALESCE(v_prev_hash, 'GENESIS_NESS_SOFT');

    v_payload := concat_ws('|',
        v_prev_hash,
        p_tipo_evento::TEXT,
        COALESCE(p_id_usuario::TEXT, 'NULL'),
        COALESCE(p_modulo, ''),
        COALESCE(p_entidad, ''),
        COALESCE(p_id_entidad::TEXT, 'NULL'),
        COALESCE(p_accion, ''),
        COALESCE(p_estado_anterior, ''),
        COALESCE(p_estado_nuevo, ''),
        COALESCE(p_direccion_ip, ''),
        COALESCE(p_sesion::TEXT, ''),
        COALESCE(p_severidad, ''),
        COALESCE(p_observaciones, ''),
        to_char(v_fecha, 'YYYY-MM-DD"T"HH24:MI:SS.US')
    );

    v_hash := encode(digest(v_payload, 'sha256'), 'hex');

    INSERT INTO seguridad.bitacora_eventos (
        tipo_evento, id_usuario, modulo, entidad, id_entidad,
        accion, estado_anterior, estado_nuevo, direccion_ip,
        user_agent, sesion, severidad, hash_integridad,
        observaciones, fecha_evento
    ) VALUES (
        p_tipo_evento, p_id_usuario, p_modulo, p_entidad, p_id_entidad,
        p_accion, p_estado_anterior, p_estado_nuevo, p_direccion_ip,
        p_user_agent, p_sesion, p_severidad, v_hash,
        p_observaciones, v_fecha
    )
    RETURNING id_evento INTO v_id_evento;

    RETURN v_id_evento;
END;
$$;
