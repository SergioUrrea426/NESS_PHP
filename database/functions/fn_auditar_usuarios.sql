-- Function: seguridad.fn_auditar_usuarios
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

CREATE OR REPLACE FUNCTION seguridad.fn_auditar_usuarios()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_actor_id INT;
    v_ip       VARCHAR(45);
    v_ua       VARCHAR(255);
    v_sesion   UUID;
BEGIN
    BEGIN
        v_actor_id := NULLIF(current_setting('app.current_user_id', true), '')::INT;
    EXCEPTION WHEN OTHERS THEN
        v_actor_id := NULL;
    END;

    v_ip := NULLIF(current_setting('app.client_ip', true), '');
    v_ua := NULLIF(current_setting('app.user_agent', true), '');

    BEGIN
        v_sesion := NULLIF(current_setting('app.session_uuid', true), '')::UUID;
    EXCEPTION WHEN OTHERS THEN
        v_sesion := NULL;
    END;

    IF TG_OP = 'UPDATE' THEN
        IF OLD.id_rol IS DISTINCT FROM NEW.id_rol THEN
            PERFORM seguridad.fn_registrar_evento(
                'SEGURIDAD'::tipo_evento_bitacora_enum,
                COALESCE(v_actor_id, NEW.id_usuario),
                'SEGURIDAD',
                'usuarios',
                NEW.id_usuario,
                'CAMBIO_ROL',
                OLD.id_rol::TEXT,
                NEW.id_rol::TEXT,
                v_ip, v_ua, v_sesion, 'ALTA',
                format('Cambio de rol en usuario %s', NEW.username)
            );
        END IF;

        IF OLD.estado IS DISTINCT FROM NEW.estado THEN
            PERFORM seguridad.fn_registrar_evento(
                'SEGURIDAD'::tipo_evento_bitacora_enum,
                COALESCE(v_actor_id, NEW.id_usuario),
                'SEGURIDAD',
                'usuarios',
                NEW.id_usuario,
                'CAMBIO_ESTADO',
                OLD.estado,
                NEW.estado,
                v_ip, v_ua, v_sesion, 'ALTA',
                format('Cambio de estado en usuario %s', NEW.username)
            );
        END IF;

        IF OLD.activo_2fa IS DISTINCT FROM NEW.activo_2fa THEN
            PERFORM seguridad.fn_registrar_evento(
                'SEGURIDAD'::tipo_evento_bitacora_enum,
                COALESCE(v_actor_id, NEW.id_usuario),
                'SEGURIDAD',
                'usuarios',
                NEW.id_usuario,
                'CAMBIO_2FA',
                OLD.activo_2fa::TEXT,
                NEW.activo_2fa::TEXT,
                v_ip, v_ua, v_sesion, 'MEDIA',
                format('Cambio de 2FA en usuario %s', NEW.username)
            );
        END IF;
    END IF;

    RETURN NEW;
END;
$$;
