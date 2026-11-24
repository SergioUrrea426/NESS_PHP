CREATE OR REPLACE PROCEDURE comercial.PA_Update_Client(
    p_id_cliente INT,
    p_tipo_persona tipo_persona_enum,
    p_id_tipo_documento INT,
    p_numero_documento VARCHAR(20),
    p_digito_verificacion VARCHAR(1),
    p_nombres VARCHAR(150),
    p_apellidos VARCHAR(150),
    p_razon_social VARCHAR(200),
    p_correo VARCHAR(150),
    p_telefono VARCHAR(20),
    p_direccion VARCHAR(200),
    p_id_ubicacion INT,
    p_id_responsable INT,
    p_acepta_habeas_data BOOLEAN
)
LANGUAGE plpgsql
AS
$$
BEGIN

    -- Validar que el cliente exista
    IF NOT EXISTS (
        SELECT 1
        FROM comercial.clientes
        WHERE id_cliente = p_id_cliente
    ) THEN
        RAISE EXCEPTION 'No existe ningún cliente con el ID %', p_id_cliente;
    END IF;

    -- Validar que el documento no pertenezca a otro cliente
    IF EXISTS (
        SELECT 1
        FROM comercial.clientes
        WHERE id_tipo_documento = p_id_tipo_documento
          AND numero_documento = p_numero_documento
          AND id_cliente <> p_id_cliente
    ) THEN
        RAISE EXCEPTION 'El documento % ya está registrado por otro cliente.', p_numero_documento;
    END IF;

    -- Validar que el correo no pertenezca a otro cliente
    IF EXISTS (
        SELECT 1
        FROM comercial.clientes
        WHERE correo = p_correo
          AND id_cliente <> p_id_cliente
    ) THEN
        RAISE EXCEPTION 'El correo % ya está registrado por otro cliente.', p_correo;
    END IF;

    UPDATE comercial.clientes
    SET
        tipo_persona = COALESCE(p_tipo_persona, tipo_persona),
        id_tipo_documento = COALESCE(p_id_tipo_documento, id_tipo_documento),
        numero_documento = COALESCE(p_numero_documento, numero_documento),
        digito_verificacion = COALESCE(p_digito_verificacion, digito_verificacion),
        nombres = COALESCE(p_nombres, nombres),
        apellidos = COALESCE(p_apellidos, apellidos),
        razon_social = COALESCE(p_razon_social, razon_social),
        correo = COALESCE(p_correo, correo),
        telefono = COALESCE(p_telefono, telefono),
        direccion = COALESCE(p_direccion, direccion),
        id_ubicacion = COALESCE(p_id_ubicacion, id_ubicacion),
        id_responsable = COALESCE(p_id_responsable, id_responsable),
        acepta_habeas_data = COALESCE(p_acepta_habeas_data, acepta_habeas_data),
        fecha_aceptacion_habeas_data = CASE
            WHEN p_acepta_habeas_data = TRUE
                 AND acepta_habeas_data = FALSE
            THEN NOW()
            ELSE fecha_aceptacion_habeas_data
        END,
        fecha_actualizacion = NOW()
    WHERE id_cliente = p_id_cliente;

END;
$$;