-- Function: seguridad.fn_registrar_intento_fallido
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

CREATE OR REPLACE FUNCTION seguridad.fn_registrar_intento_fallido(
    p_id_usuario INT,
    p_max_intentos INT DEFAULT 5,
    p_minutos_bloqueo INT DEFAULT 15,
    p_ip VARCHAR(45) DEFAULT NULL,
    p_user_agent VARCHAR(255) DEFAULT NULL
) RETURNS TABLE (
    intentos_actuales INT,
    bloqueado_hasta TIMESTAMP
)
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
    v_intentos INT;
    v_bloqueo  TIMESTAMP;
    v_ahora    TIMESTAMP := (now() AT TIME ZONE 'America/Bogota');
BEGIN
    UPDATE seguridad.usuarios
       SET intentos_fallidos = COALESCE(intentos_fallidos, 0) + 1,
           bloqueo_hasta = CASE
               WHEN COALESCE(intentos_fallidos, 0) + 1 >= p_max_intentos
               THEN v_ahora + make_interval(mins => p_minutos_bloqueo)
               ELSE bloqueo_hasta
           END
     WHERE id_usuario = p_id_usuario
    RETURNING seguridad.usuarios.intentos_fallidos, seguridad.usuarios.bloqueo_hasta
         INTO v_intentos, v_bloqueo;

    PERFORM seguridad.fn_registrar_evento(
        'SEGURIDAD'::tipo_evento_bitacora_enum,
        p_id_usuario,
        'AUTH',
        'usuarios',
        p_id_usuario,
        'LOGIN_FALLIDO',
        NULL,
        format('intentos=%s;bloqueo=%s', v_intentos, COALESCE(v_bloqueo::TEXT, 'NINGUNO')),
        p_ip,
        p_user_agent,
        NULL,
        CASE WHEN v_bloqueo IS NOT NULL THEN 'ALTA' ELSE 'MEDIA' END,
        'Intento de autenticación fallido'
    );

    intentos_actuales := v_intentos;
    bloqueado_hasta := v_bloqueo;
    RETURN NEXT;
END;
$$;
