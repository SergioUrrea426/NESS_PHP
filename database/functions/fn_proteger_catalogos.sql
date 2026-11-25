-- Function: seguridad.fn_proteger_catalogos
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

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
