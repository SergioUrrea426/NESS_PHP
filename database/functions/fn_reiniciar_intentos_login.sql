-- Function: seguridad.fn_reiniciar_intentos_login
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

CREATE OR REPLACE FUNCTION seguridad.fn_reiniciar_intentos_login(
    p_id_usuario INT,
    p_ip VARCHAR(45) DEFAULT NULL,
    p_user_agent VARCHAR(255) DEFAULT NULL,
    p_sesion UUID DEFAULT NULL
) RETURNS VOID
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
    v_ahora TIMESTAMP := (now() AT TIME ZONE 'America/Bogota');
BEGIN
    UPDATE seguridad.usuarios
       SET intentos_fallidos = 0,
           bloqueo_hasta = NULL,
           ultimo_acceso = v_ahora
     WHERE id_usuario = p_id_usuario;

    PERFORM seguridad.fn_registrar_evento(
        'SEGURIDAD'::tipo_evento_bitacora_enum,
        p_id_usuario,
        'AUTH',
        'usuarios',
        p_id_usuario,
        'LOGIN_EXITOSO',
        NULL,
        'ACTIVO',
        p_ip,
        p_user_agent,
        p_sesion,
        'BAJA',
        'Inicio de sesión exitoso'
    );
END;
$$;
