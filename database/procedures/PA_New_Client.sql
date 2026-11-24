CREATE OR REPLACE PROCEDURE comercial.PA_New_Client(

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

    p_acepta_habeas_data BOOLEAN DEFAULT FALSE

)
LANGUAGE plpgsql
AS
$$
BEGIN

    -----------------------------------------------------------------
    -- Validar documento
    -----------------------------------------------------------------

    IF EXISTS (
        SELECT 1
        FROM comercial.clientes
        WHERE id_tipo_documento = p_id_tipo_documento
          AND numero_documento = p_numero_documento
    ) THEN
        RAISE EXCEPTION
        'El documento % ya se encuentra registrado.',
        p_numero_documento;
    END IF;

    -----------------------------------------------------------------
    -- Validar correo
    -----------------------------------------------------------------

    IF EXISTS (
        SELECT 1
        FROM comercial.clientes
        WHERE correo = p_correo
    ) THEN
        RAISE EXCEPTION
        'El correo % ya está registrado.',
        p_correo;
    END IF;

    -----------------------------------------------------------------
    -- Insertar cliente
    -----------------------------------------------------------------

    INSERT INTO comercial.clientes
    (
        tipo_persona,
        id_tipo_documento,
        numero_documento,
        digito_verificacion,
        nombres,
        apellidos,
        razon_social,
        correo,
        telefono,
        direccion,
        id_ubicacion,
        id_responsable,
        acepta_habeas_data,
        fecha_aceptacion_habeas_data
    )
    VALUES
    (
        p_tipo_persona,
        p_id_tipo_documento,
        p_numero_documento,
        p_digito_verificacion,
        p_nombres,
        p_apellidos,
        p_razon_social,
        p_correo,
        p_telefono,
        p_direccion,
        p_id_ubicacion,
        p_id_responsable,
        p_acepta_habeas_data,

        CASE
            WHEN p_acepta_habeas_data
            THEN NOW()
            ELSE NULL
        END
    );

END;
$$;