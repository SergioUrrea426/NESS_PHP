-- Function: seguridad.fn_auditar_roles
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

CREATE OR REPLACE FUNCTION seguridad.fn_auditar_roles()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_actor_id INT;
    v_ip       VARCHAR(45);
BEGIN
    BEGIN
        v_actor_id := NULLIF(current_setting('app.current_user_id', true), '')::INT;
    EXCEPTION WHEN OTHERS THEN
        v_actor_id := NULL;
    END;
    v_ip := NULLIF(current_setting('app.client_ip', true), '');

    IF TG_OP = 'INSERT' THEN
        PERFORM seguridad.fn_registrar_evento(
            'SEGURIDAD'::tipo_evento_bitacora_enum,
            v_actor_id, 'SEGURIDAD', 'roles', NEW.id_rol,
            'CREAR_ROL', NULL,
            format('codigo=%s;nivel=%s;activo=%s', NEW.codigo, NEW.nivel_jerarquia, NEW.estado),
            v_ip, NULL, NULL, 'ALTA', 'Creación de rol'
        );
        RETURN NEW;
    ELSIF TG_OP = 'UPDATE' THEN
        IF OLD.nivel_jerarquia IS DISTINCT FROM NEW.nivel_jerarquia
           OR OLD.estado IS DISTINCT FROM NEW.estado
           OR OLD.codigo IS DISTINCT FROM NEW.codigo THEN
            PERFORM seguridad.fn_registrar_evento(
                'SEGURIDAD'::tipo_evento_bitacora_enum,
                v_actor_id, 'SEGURIDAD', 'roles', NEW.id_rol,
                'ACTUALIZAR_ROL',
                format('codigo=%s;nivel=%s;activo=%s', OLD.codigo, OLD.nivel_jerarquia, OLD.estado),
                format('codigo=%s;nivel=%s;activo=%s', NEW.codigo, NEW.nivel_jerarquia, NEW.estado),
                v_ip, NULL, NULL, 'ALTA', 'Modificación de rol'
            );
        END IF;
        RETURN NEW;
    END IF;

    RETURN NULL;
END;
$$;
