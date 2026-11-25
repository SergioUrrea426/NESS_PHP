-- Function: seguridad.fn_proteger_bitacora
-- Extracted from database/sql/Seguridad_triggers_fns.sql.

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
