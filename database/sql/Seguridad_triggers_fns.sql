-- =============================================================================
-- NESS SOFT: SEGURIDAD, AUDITORIA Y ENCAPSULAMIENTO EN POSTGRESQL
-- Archivo: database/sql/Seguridad_triggers_fns.sql
-- =============================================================================

SET TIME ZONE 'America/Bogota';
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- =============================================================================
-- 1. FUNCIÓN CENTRALIZADA PARA INSERTAR EN BITÁCORA CON HASH DE INTEGRIDAD
--    Cumple RF-006, RNF-003 y RNF-044
-- =============================================================================
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

-- =============================================================================
-- TRIGGER BEFORE INSERT PARA GARANTIZAR QUE CUALQUIER INSERCIÓN DIRECTA EN
-- seguridad.bitacora_eventos TAMBIÉN CALCULE SU hash_integridad
-- =============================================================================
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

DROP TRIGGER IF EXISTS trg_bitacora_hash_insert ON seguridad.bitacora_eventos;
CREATE TRIGGER trg_bitacora_hash_insert
BEFORE INSERT ON seguridad.bitacora_eventos
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_bitacora_before_insert();

-- ----------------------------------------------------------------------------
-- 2. TRG-SEC-001: INMUTABILIDAD DE BITÁCORA (IMPEDIR UPDATE / DELETE / TRUNCATE)
--    Cumple RNF-044
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION seguridad.fn_proteger_bitacora()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    RAISE EXCEPTION
        'Operación % denegada: seguridad.bitacora_eventos es inmutable (RNF-044).',
        TG_OP
        USING ERRCODE = '42501';
    RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS trg_proteger_bitacora_update ON seguridad.bitacora_eventos;
CREATE TRIGGER trg_proteger_bitacora_update
BEFORE UPDATE ON seguridad.bitacora_eventos
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_proteger_bitacora();

DROP TRIGGER IF EXISTS trg_proteger_bitacora_delete ON seguridad.bitacora_eventos;
CREATE TRIGGER trg_proteger_bitacora_delete
BEFORE DELETE ON seguridad.bitacora_eventos
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_proteger_bitacora();

DROP TRIGGER IF EXISTS trg_proteger_bitacora_truncate ON seguridad.bitacora_eventos;
CREATE TRIGGER trg_proteger_bitacora_truncate
BEFORE TRUNCATE ON seguridad.bitacora_eventos
FOR STATEMENT
EXECUTE FUNCTION seguridad.fn_proteger_bitacora();

-- ----------------------------------------------------------------------------
-- 3. TRG-SEC-004: MANTENER fecha_actualizacion EN SEGURIDAD
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION seguridad.fn_set_fecha_actualizacion()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.fecha_actualizacion := (now() AT TIME ZONE 'America/Bogota');
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_usuarios_fecha_actualizacion ON seguridad.usuarios;
CREATE TRIGGER trg_usuarios_fecha_actualizacion
BEFORE UPDATE ON seguridad.usuarios
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_set_fecha_actualizacion();

-- ----------------------------------------------------------------------------
-- 4. TRG-SEC-002: AUDITORÍA DE CAMBIOS RELEVANTES EN seguridad.usuarios
--    Registra cambios de rol, estado o activo_2fa (RF-006, RNF-003, HU-002, HU-006)
-- ----------------------------------------------------------------------------
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

DROP TRIGGER IF EXISTS trg_auditar_usuarios ON seguridad.usuarios;
CREATE TRIGGER trg_auditar_usuarios
AFTER UPDATE ON seguridad.usuarios
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_auditar_usuarios();

-- ----------------------------------------------------------------------------
-- 5. TRG-SEC-003: AUDITORÍA DE CAMBIOS EN seguridad.roles (RF-003, RNF-003)
-- ----------------------------------------------------------------------------
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

DROP TRIGGER IF EXISTS trg_auditar_roles ON seguridad.roles;
CREATE TRIGGER trg_auditar_roles
AFTER INSERT OR UPDATE ON seguridad.roles
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_auditar_roles();

-- ----------------------------------------------------------------------------
-- 6. TRG-SEC-005: PROTECCIÓN DE CATÁLOGOS MAESTROS (RF-007, RN-08)
--    Impide eliminar físicamente un catálogo con registros asociados
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION seguridad.fn_proteger_catalogos()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_en_uso BOOLEAN := FALSE;
BEGIN
    SELECT EXISTS (SELECT 1 FROM seguridad.catalogos_generales WHERE id_padre = OLD.id_catalogo)
        OR EXISTS (SELECT 1 FROM comercial.clientes WHERE id_tipo_documento = OLD.id_catalogo OR id_ubicacion = OLD.id_catalogo)
        OR EXISTS (SELECT 1 FROM comercial.productos WHERE id_categoria = OLD.id_catalogo OR id_marca = OLD.id_catalogo OR id_unidad_medida = OLD.id_catalogo)
        OR EXISTS (SELECT 1 FROM comercial.pagos WHERE id_metodo_pago = OLD.id_catalogo)
      INTO v_en_uso;

    IF v_en_uso THEN
        RAISE EXCEPTION
            'No se puede eliminar el catálogo ID % (%): tiene registros asociados. Cámbielo a INACTIVO (RN-08).',
            OLD.id_catalogo, OLD.nombre
            USING ERRCODE = '23503';
    END IF;

    RETURN OLD;
END;
$$;

DROP TRIGGER IF EXISTS trg_proteger_catalogos_delete ON seguridad.catalogos_generales;
CREATE TRIGGER trg_proteger_catalogos_delete
BEFORE DELETE ON seguridad.catalogos_generales
FOR EACH ROW
EXECUTE FUNCTION seguridad.fn_proteger_catalogos();

-- ----------------------------------------------------------------------------
-- 7. FUNCIONES ENCAPSULADAS DE BLOQUEO Y REINICIO DE INTENTOS DE LOGIN
--    Cumple RF-001 y RF-004
-- ----------------------------------------------------------------------------
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

-- AUN NOOOOOOOOOO POR QUE HAY QUE COORDINAR CON NICOLD Y SERGIO --------------
-- ----------------------------------------------------------------------------
-- 8. POLÍTICA DE PRIVILEGIOS PARA EL USUARIO DE APLICACIÓN LARAVEL (ness_app)
--    Ajusta 'ness_app' al usuario real de tu .env en PostgreSQL
-- ----------------------------------------------------------------------------
-- REVOKE UPDATE, DELETE, TRUNCATE ON seguridad.bitacora_eventos FROM ness_app;
-- GRANT SELECT, INSERT ON seguridad.bitacora_eventos TO ness_app;
-- GRANT USAGE, SELECT ON SEQUENCE seguridad.bitacora_eventos_id_evento_seq TO ness_app;
-- GRANT EXECUTE ON FUNCTION seguridad.fn_registrar_evento(
--     tipo_evento_bitacora_enum, INT, VARCHAR, VARCHAR, INT, VARCHAR,
--     VARCHAR, VARCHAR, VARCHAR, VARCHAR, UUID, VARCHAR, VARCHAR
-- ) TO ness_app;
-- GRANT EXECUTE ON FUNCTION seguridad.fn_registrar_intento_fallido(INT, INT, INT, VARCHAR, VARCHAR) TO ness_app;
-- GRANT EXECUTE ON FUNCTION seguridad.fn_reiniciar_intentos_login(INT, VARCHAR, VARCHAR, UUID) TO ness_app;

-- ----------------------------------------------------------------------------------------
-- -- AUN NOOOOOOOOOO POR QUE HAY QUE COORDINAR CON NICOLD Y SERGIO --------------
-- -------------------------------------------------------------------------------
-- 9. REFERENCIA COMENTADA: REGLAS CRUZADAS SEGURIDAD <-> COMERCIAL
-- Alcance normativo: RF-009, RN-09, HU-011 (LEY 1581 de 2012 -HABEAS DATA)
-- NOTA: SE DEJA COMENTADO POR QUE EL DOMINIO COMERCIAL CORRESPONDE AL FRENTE COMERCIAL;
-- QUEDA AQUI COMO REFERENCIA DE TRAZABILIDAD DE SEGURIDAD.
-- -------------------------------------------------------------------------------
-- CREATE OR REPLACE FUNCTION comercial.fn_auditar_habeas_data()
-- RETURNS TRIGGER
-- LANGUAGE plpgsql
-- AS $$
-- DECLARE
--     v_actor_id INT;
--     v_ip       VARCHAR(45);
-- BEGIN
--     BEGIN
--         v_actor_id := NULLIF(current_setting('app.current_user_id', true), '')::INT;
--     EXCEPTION WHEN OTHERS THEN
--         v_actor_id := NEW.id_responsable;
--     END;
--     v_ip := NULLIF(current_setting('app.client_ip', true), '');
--
--     IF OLD.acepta_habeas_data IS DISTINCT FROM NEW.acepta_habeas_data THEN
--         IF NEW.acepta_habeas_data IS TRUE AND NEW.fecha_aceptacion_habeas_data IS NULL THEN
--             NEW.fecha_aceptacion_habeas_data := (now() AT TIME ZONE 'America/Bogota');
--         END IF;
--
--         PERFORM seguridad.fn_registrar_evento(
--             'SEGURIDAD'::tipo_evento_bitacora_enum,
--             v_actor_id,
--             'CLIENTES',
--             'clientes',
--             NEW.id_cliente,
--             CASE WHEN NEW.acepta_habeas_data THEN 'HABEAS_DATA_ACEPTADO' ELSE 'HABEAS_DATA_REVOCADO' END,
--             OLD.acepta_habeas_data::TEXT,
--             NEW.acepta_habeas_data::TEXT,
--             v_ip,
--             NULL,
--             NULL,
--             'ALTA',
--             format('Consentimiento Habeas Data cliente ID %s', NEW.id_cliente)
--         );
--     END IF;
--
--     RETURN NEW;
-- END;
-- $$;
--
-- DROP TRIGGER IF EXISTS trg_auditar_habeas_data ON comercial.clientes;
-- CREATE TRIGGER trg_auditar_habeas_data
-- BEFORE UPDATE ON comercial.clientes
-- FOR EACH ROW
-- EXECUTE FUNCTION comercial.fn_auditar_habeas_data();
--
-- CREATE OR REPLACE FUNCTION comercial.fn_validar_consentimiento_actividad()
-- RETURNS TRIGGER
-- LANGUAGE plpgsql
-- AS $$
-- DECLARE
--     v_acepta BOOLEAN;
-- BEGIN
--     SELECT acepta_habeas_data
--       INTO v_acepta
--       FROM comercial.clientes
--      WHERE id_cliente = NEW.id_cliente;
--
--     IF COALESCE(v_acepta, FALSE) IS NOT TRUE THEN
--         RAISE EXCEPTION
--             'Contacto comercial bloqueado: el cliente ID % no tiene consentimiento de datos personales vigente (RN-09 / Ley 1581).',
--             NEW.id_cliente
--             USING ERRCODE = '42501';
--     END IF;
--
--     RETURN NEW;
-- END;
-- $$;
--
-- DROP TRIGGER IF EXISTS trg_validar_consentimiento_actividad ON comercial.actividades;
-- CREATE TRIGGER trg_validar_consentimiento_actividad
-- BEFORE INSERT ON comercial.actividades
-- FOR EACH ROW
-- EXECUTE FUNCTION comercial.fn_validar_consentimiento_actividad();|