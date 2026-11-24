-- ============================================================================
-- NESS-SOFT - ESQUEMA POSTGRESQL: HERRAMIENTA DE ASISTENCIA COMERCIAL
-- Control de accesos (seguridad) + gestión comercial (comercial)
-- 2 schemas · 16 tablas · 10 tipos ENUM
-- ============================================================================
-- CAMBIOS FRENTE A LA VERSIÓN ANTERIOR (27 tablas / 6 schemas):
--   * Se eliminan los schemas ecommerce, logistica, gerencia y postventa.
--   * Postventa se integra a comercial (casos_postventa).
--   * Se agrega una venta clara: ventas (cabecera) + detalle_venta.
--     facturas se fusiona en ventas; notas_contables sale del CRM.
--   * terceros pasa a clientes (solo clientes/prospectos, con ciclo de vida).
--   * Se agrega fidelización: clientes.etapa_cliente + movimientos_fidelizacion.
--   * La auditoría de "quién cambió qué" queda en seguridad.bitacora_eventos.
--
-- FLUJO COMERCIAL:
--   Captación  : clientes (PROSPECTO) -> oportunidades -> actividades
--   Conversión : oportunidades GANADA -> ventas (COTIZACION -> CONFIRMADA ->
--                FACTURADA) -> detalle_venta -> pagos -> comisiones
--   Retención  : casos_postventa -> movimientos_fidelizacion ->
--                clientes.etapa_cliente (NUEVO -> ACTIVO -> FIEL / EN_RIESGO)
-- ============================================================================


-- ============================================================================
-- 0. TIPOS (ENUM) COMPARTIDOS
-- Viven en "public" para ser accesibles desde cualquier schema.
-- ============================================================================

CREATE TYPE tipo_catalogo_enum AS ENUM ('PAIS', 'DEPARTAMENTO', 'CIUDAD', 'UNIDAD_MEDIDA', 'METODO_PAGO', 'TIPO_DOCUMENTO', 'CATEGORIA_PRODUCTO', 'MARCA');
CREATE TYPE tipo_persona_enum AS ENUM ('NATURAL', 'JURIDICA');
CREATE TYPE etapa_cliente_enum AS ENUM ('PROSPECTO', 'NUEVO', 'ACTIVO', 'FIEL', 'EN_RIESGO', 'INACTIVO');
CREATE TYPE etapa_oportunidad_enum AS ENUM ('PROSPECCION', 'CALIFICACION', 'PROPUESTA', 'NEGOCIACION', 'GANADA', 'PERDIDA');
CREATE TYPE estado_venta_enum AS ENUM ('COTIZACION', 'CONFIRMADA', 'FACTURADA', 'ANULADA');
CREATE TYPE tipo_actividad_enum AS ENUM ('LLAMADA', 'CORREO', 'REUNION', 'TAREA', 'NOTA');
CREATE TYPE tipo_evento_bitacora_enum AS ENUM ('SEGURIDAD', 'TRANSACCION');
CREATE TYPE tipo_caso_enum AS ENUM ('PQRS', 'GARANTIA');
CREATE TYPE naturaleza_producto_enum AS ENUM ('BIEN', 'SERVICIO');
CREATE TYPE tipo_mov_fidelizacion_enum AS ENUM ('ACUMULACION', 'REDENCION', 'AJUSTE');


-- ============================================================================
-- 1. CREACIÓN DE SCHEMAS
-- ============================================================================

CREATE SCHEMA IF NOT EXISTS seguridad;
CREATE SCHEMA IF NOT EXISTS comercial;

COMMENT ON SCHEMA seguridad IS 'Control de accesos: autenticación, roles, catálogos maestros, bitácora de eventos y notificaciones';
COMMENT ON SCHEMA comercial IS 'Captación, conversión y retención: clientes, pipeline, ventas, pagos, comisiones, postventa y fidelización';


-- ============================================================================
-- 2. SCHEMA SEGURIDAD
-- ============================================================================

-- roles: Roles de la aplicación con nivel jerárquico.
CREATE TABLE seguridad.roles (
  id_rol          SERIAL PRIMARY KEY,
  codigo          VARCHAR(20) NOT NULL UNIQUE,
  nombre          VARCHAR(50) NOT NULL,
  descripcion     VARCHAR(255),
  nivel_jerarquia INT NOT NULL,
  estado          BOOLEAN DEFAULT TRUE
);
COMMENT ON TABLE seguridad.roles IS 'Roles de la aplicación con nivel jerárquico.';

-- usuarios: Usuarios del sistema (autenticación, 2FA, bloqueo).
CREATE TABLE seguridad.usuarios (
  id_usuario               SERIAL PRIMARY KEY,
  id_rol                   INT NOT NULL REFERENCES seguridad.roles(id_rol),
  nombres                  VARCHAR(150) NOT NULL,
  username                 VARCHAR(50) NOT NULL UNIQUE,
  email                    VARCHAR(150) NOT NULL UNIQUE,
  password_hash            VARCHAR(255) NOT NULL,
  telefono                 VARCHAR(20),  -- Teléfono principal (celular o fijo)
  estado                   VARCHAR(15) DEFAULT 'ACTIVO',
  activo_2fa               BOOLEAN DEFAULT FALSE,
  ultimo_acceso            TIMESTAMP,
  fecha_creacion           TIMESTAMP DEFAULT now(),
  intentos_fallidos        INT DEFAULT 0 CHECK (intentos_fallidos >= 0),
  bloqueo_hasta            TIMESTAMP,
  requiere_cambio_password BOOLEAN DEFAULT FALSE,
  fecha_actualizacion      TIMESTAMP
);
COMMENT ON TABLE seguridad.usuarios IS 'Usuarios del sistema (autenticación, 2FA, bloqueo).';
CREATE INDEX idx_usuarios_rol ON seguridad.usuarios(id_rol);

-- bitacora_eventos: Bitácora de eventos de seguridad y transacciones (única fuente de auditoría).
CREATE TABLE seguridad.bitacora_eventos (
  id_evento       BIGSERIAL PRIMARY KEY,
  tipo_evento     tipo_evento_bitacora_enum NOT NULL,
  id_usuario      INT REFERENCES seguridad.usuarios(id_usuario),
  modulo          VARCHAR(30),
  entidad         VARCHAR(30),
  id_entidad      INT,
  accion          VARCHAR(20),
  estado_anterior VARCHAR(30),
  estado_nuevo    VARCHAR(30),
  direccion_ip    VARCHAR(45),
  user_agent      VARCHAR(255),
  sesion          UUID,
  severidad       VARCHAR(20),
  hash_integridad VARCHAR(128),
  observaciones   VARCHAR(255),
  fecha_evento    TIMESTAMP DEFAULT now()
);
COMMENT ON TABLE seguridad.bitacora_eventos IS 'Bitácora de eventos de seguridad y transacciones (única fuente de auditoría).';
CREATE INDEX idx_bitacora_eventos_usuario ON seguridad.bitacora_eventos(id_usuario);
CREATE INDEX idx_bitacora_fecha ON seguridad.bitacora_eventos(fecha_evento);
CREATE INDEX idx_bitacora_entidad ON seguridad.bitacora_eventos(entidad, id_entidad);

-- catalogos_generales: Catálogos maestros: ubicación, tipo de documento, categoría, marca, unidad, método de pago.
CREATE TABLE seguridad.catalogos_generales (
  id_catalogo   SERIAL PRIMARY KEY,
  tipo_catalogo tipo_catalogo_enum NOT NULL,
  id_padre      INT REFERENCES seguridad.catalogos_generales(id_catalogo),
  codigo        VARCHAR(20),
  nombre        VARCHAR(150) NOT NULL,
  estado        BOOLEAN DEFAULT TRUE,
  UNIQUE (tipo_catalogo, codigo)
);
COMMENT ON TABLE seguridad.catalogos_generales IS 'Catálogos maestros: ubicación, tipo de documento, categoría, marca, unidad, método de pago.';
CREATE INDEX idx_catalogos_generales_padre ON seguridad.catalogos_generales(id_padre);
CREATE INDEX idx_catalogos_tipo ON seguridad.catalogos_generales(tipo_catalogo);

-- notificaciones: Alertas dirigidas a usuarios (oportunidades, casos, clientes).
CREATE TABLE seguridad.notificaciones (
  id_notificacion    SERIAL PRIMARY KEY,
  tipo_entidad       VARCHAR(20) NOT NULL,  -- OPORTUNIDAD, CASO, CLIENTE
  id_entidad         INT NOT NULL,
  titulo             VARCHAR(150) NOT NULL,
  descripcion        VARCHAR(500),
  prioridad          VARCHAR(10) DEFAULT 'MEDIA',
  id_usuario_destino INT NOT NULL REFERENCES seguridad.usuarios(id_usuario),
  fecha_generacion   TIMESTAMP DEFAULT now(),
  estado             VARCHAR(15) DEFAULT 'ACTIVA',
  fecha_lectura      TIMESTAMP
);
COMMENT ON TABLE seguridad.notificaciones IS 'Alertas dirigidas a usuarios (oportunidades, casos, clientes).';
CREATE INDEX idx_notificaciones_usuario_destino ON seguridad.notificaciones(id_usuario_destino);


-- ============================================================================
-- 3. SCHEMA COMERCIAL
-- ============================================================================

-- clientes: Clientes y prospectos con su etapa en el ciclo de vida y asesor responsable.
CREATE TABLE comercial.clientes (
  id_cliente                   SERIAL PRIMARY KEY,
  tipo_persona                 tipo_persona_enum NOT NULL,
  id_tipo_documento            INT NOT NULL REFERENCES seguridad.catalogos_generales(id_catalogo),
  numero_documento             VARCHAR(20) NOT NULL,
  digito_verificacion          VARCHAR(1),
  nombres                      VARCHAR(150),
  apellidos                    VARCHAR(150),
  razon_social                 VARCHAR(200),
  correo                       VARCHAR(150) NOT NULL,
  telefono                     VARCHAR(20),  -- Teléfono principal (celular o fijo)
  direccion                    VARCHAR(200),
  id_ubicacion                 INT REFERENCES seguridad.catalogos_generales(id_catalogo),
  id_responsable               INT REFERENCES seguridad.usuarios(id_usuario) ON DELETE SET NULL,  -- Asesor comercial asignado
  acepta_habeas_data           BOOLEAN DEFAULT FALSE,
  fecha_aceptacion_habeas_data TIMESTAMP,
  etapa_cliente                etapa_cliente_enum NOT NULL DEFAULT 'PROSPECTO',  -- Ciclo de vida: PROSPECTO → NUEVO → ACTIVO → FIEL / EN_RIESGO / INACTIVO
  fecha_creacion               TIMESTAMP DEFAULT now(),
  fecha_actualizacion          TIMESTAMP,
  UNIQUE (id_tipo_documento, numero_documento)
);
COMMENT ON TABLE comercial.clientes IS 'Clientes y prospectos con su etapa en el ciclo de vida y asesor responsable.';
CREATE INDEX idx_clientes_ubicacion ON comercial.clientes(id_ubicacion);
CREATE INDEX idx_clientes_responsable ON comercial.clientes(id_responsable);
CREATE INDEX idx_clientes_etapa ON comercial.clientes(etapa_cliente);

-- contactos: Personas de contacto de cada cliente.
CREATE TABLE comercial.contactos (
  id_contacto  SERIAL PRIMARY KEY,
  id_cliente   INT NOT NULL REFERENCES comercial.clientes(id_cliente) ON DELETE CASCADE,
  nombre       VARCHAR(150) NOT NULL,
  cargo        VARCHAR(100),
  email        VARCHAR(150),
  telefono     VARCHAR(20),  -- Teléfono principal (celular o fijo)
  es_principal BOOLEAN DEFAULT FALSE
);
COMMENT ON TABLE comercial.contactos IS 'Personas de contacto de cada cliente.';
CREATE INDEX idx_contactos_cliente ON comercial.contactos(id_cliente);
CREATE UNIQUE INDEX uq_contactos_principal ON comercial.contactos(id_cliente) WHERE es_principal = TRUE;

-- productos: Catálogo de productos y servicios que se venden.
CREATE TABLE comercial.productos (
  id_producto         SERIAL PRIMARY KEY,
  sku                 VARCHAR(30) UNIQUE,
  nombre              VARCHAR(150) NOT NULL,
  descripcion         VARCHAR(255),
  id_categoria        INT REFERENCES seguridad.catalogos_generales(id_catalogo),
  id_marca            INT REFERENCES seguridad.catalogos_generales(id_catalogo),
  id_unidad_medida    INT NOT NULL REFERENCES seguridad.catalogos_generales(id_catalogo),
  naturaleza          naturaleza_producto_enum,
  precio_base         NUMERIC(15,2) NOT NULL CHECK (precio_base >= 0),
  estado              BOOLEAN DEFAULT TRUE,
  fecha_creacion      TIMESTAMP DEFAULT now(),
  fecha_actualizacion TIMESTAMP
);
COMMENT ON TABLE comercial.productos IS 'Catálogo de productos y servicios que se venden.';
CREATE INDEX idx_productos_categoria ON comercial.productos(id_categoria);
CREATE INDEX idx_productos_marca ON comercial.productos(id_marca);
CREATE INDEX idx_productos_unidad_medida ON comercial.productos(id_unidad_medida);

-- oportunidades: Pipeline de negocios en seguimiento (sin líneas ni totales: eso es la venta).
CREATE TABLE comercial.oportunidades (
  id_oportunidad        SERIAL PRIMARY KEY,
  id_cliente            INT NOT NULL REFERENCES comercial.clientes(id_cliente),
  id_vendedor           INT NOT NULL REFERENCES seguridad.usuarios(id_usuario),
  etapa                 etapa_oportunidad_enum NOT NULL DEFAULT 'PROSPECCION',  -- PROSPECCION → CALIFICACION → PROPUESTA → NEGOCIACION → GANADA / PERDIDA
  origen                VARCHAR(20),
  valor_estimado        NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (valor_estimado >= 0),  -- Valor estimado del negocio
  fecha_cierre_estimada DATE,
  fecha_cierre          DATE,  -- Fecha real de cierre (GANADA o PERDIDA)
  motivo_perdida        VARCHAR(150),  -- Obligatorio cuando etapa = PERDIDA
  fecha_creacion        TIMESTAMP DEFAULT now(),
  observaciones         VARCHAR(255),
  fecha_actualizacion   TIMESTAMP,
  CONSTRAINT chk_oportunidades_perdida CHECK (etapa <> 'PERDIDA' OR motivo_perdida IS NOT NULL)
);
COMMENT ON TABLE comercial.oportunidades IS 'Pipeline de negocios en seguimiento (sin líneas ni totales: eso es la venta).';
CREATE INDEX idx_oportunidades_cliente ON comercial.oportunidades(id_cliente);
CREATE INDEX idx_oportunidades_vendedor ON comercial.oportunidades(id_vendedor);
CREATE INDEX idx_oportunidades_etapa ON comercial.oportunidades(etapa);

-- ventas: La venta: cotización → confirmada → facturada. Cabecera con totales y datos de factura.
CREATE TABLE comercial.ventas (
  id_venta            SERIAL PRIMARY KEY,
  id_cliente          INT NOT NULL REFERENCES comercial.clientes(id_cliente),  -- Cliente de la venta
  id_oportunidad      INT REFERENCES comercial.oportunidades(id_oportunidad) ON DELETE SET NULL,  -- Oportunidad de origen (una oportunidad puede tener varias cotizaciones)
  id_vendedor         INT NOT NULL REFERENCES seguridad.usuarios(id_usuario),  -- Vendedor responsable
  estado              estado_venta_enum NOT NULL DEFAULT 'COTIZACION',  -- COTIZACION → CONFIRMADA → FACTURADA / ANULADA
  fecha_creacion      TIMESTAMP NOT NULL DEFAULT now(),  -- Fecha de la cotización / registro
  fecha_confirmacion  TIMESTAMP,  -- Cuando el cliente acepta (pasa a CONFIRMADA)
  fecha_vencimiento   TIMESTAMP,  -- Vigencia de la cotización o vencimiento de pago
  condiciones_pago    VARCHAR(20),
  subtotal            NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (subtotal >= 0),
  descuento_total     NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (descuento_total >= 0),
  impuesto_total      NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (impuesto_total >= 0),
  total               NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (total >= 0),
  prefijo             VARCHAR(10) NOT NULL DEFAULT '',  -- Vacío si no aplica; NOT NULL para que UNIQUE(prefijo, numero_factura) sea efectivo
  numero_factura      INT,  -- Asignado por el sistema contable al facturar
  fecha_factura       TIMESTAMP,
  cufe                VARCHAR(100),  -- Código único de factura electrónica (referencia; la emisión es del sistema contable)
  observaciones       VARCHAR(255),
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT now(),
  UNIQUE (prefijo, numero_factura),
  CONSTRAINT chk_ventas_facturada CHECK (estado <> 'FACTURADA' OR numero_factura IS NOT NULL)
);
COMMENT ON TABLE comercial.ventas IS 'La venta: cotización → confirmada → facturada. Cabecera con totales y datos de factura.';
CREATE INDEX idx_ventas_cliente ON comercial.ventas(id_cliente);
CREATE INDEX idx_ventas_oportunidad ON comercial.ventas(id_oportunidad);
CREATE INDEX idx_ventas_vendedor ON comercial.ventas(id_vendedor);
CREATE INDEX idx_ventas_estado ON comercial.ventas(estado);

-- detalle_venta: Líneas (productos) de cada venta.
CREATE TABLE comercial.detalle_venta (
  id_detalle           SERIAL PRIMARY KEY,
  id_venta             INT NOT NULL REFERENCES comercial.ventas(id_venta) ON DELETE CASCADE,
  id_producto          INT NOT NULL REFERENCES comercial.productos(id_producto),
  cantidad             NUMERIC(12,2) NOT NULL CHECK (cantidad > 0),
  precio_unitario      NUMERIC(15,2) NOT NULL CHECK (precio_unitario >= 0),
  porcentaje_descuento NUMERIC(5,2) DEFAULT 0 CHECK (porcentaje_descuento BETWEEN 0 AND 100),
  porcentaje_impuesto  NUMERIC(5,2) DEFAULT 0 CHECK (porcentaje_impuesto BETWEEN 0 AND 100),
  total                NUMERIC(15,2) NOT NULL CHECK (total >= 0)
);
COMMENT ON TABLE comercial.detalle_venta IS 'Líneas (productos) de cada venta.';
CREATE INDEX idx_detalle_venta_venta ON comercial.detalle_venta(id_venta);
CREATE INDEX idx_detalle_venta_producto ON comercial.detalle_venta(id_producto);

-- pagos: Pagos de una venta (puede haber varios por venta).
CREATE TABLE comercial.pagos (
  id_pago        SERIAL PRIMARY KEY,
  id_venta       INT NOT NULL REFERENCES comercial.ventas(id_venta),  -- Una venta puede tener varios pagos (parciales)
  id_metodo_pago INT NOT NULL REFERENCES seguridad.catalogos_generales(id_catalogo),
  fecha_pago     TIMESTAMP DEFAULT now(),
  valor          NUMERIC(15,2) NOT NULL CHECK (valor > 0),
  referencia     VARCHAR(50),
  estado         VARCHAR(15) DEFAULT 'PENDIENTE'
);
COMMENT ON TABLE comercial.pagos IS 'Pagos de una venta (puede haber varios por venta).';
CREATE INDEX idx_pagos_venta ON comercial.pagos(id_venta);
CREATE INDEX idx_pagos_metodo_pago ON comercial.pagos(id_metodo_pago);

-- comisiones: Comisión del vendedor por venta.
CREATE TABLE comercial.comisiones (
  id_comision    SERIAL PRIMARY KEY,
  id_venta       INT NOT NULL REFERENCES comercial.ventas(id_venta),
  id_vendedor    INT NOT NULL REFERENCES seguridad.usuarios(id_usuario),
  porcentaje     NUMERIC(5,2) CHECK (porcentaje BETWEEN 0 AND 100),
  valor_comision NUMERIC(15,2) CHECK (valor_comision >= 0),
  estado         VARCHAR(20) DEFAULT 'PENDIENTE',
  fecha_creacion TIMESTAMP DEFAULT now(),
  fecha_pago     TIMESTAMP
);
COMMENT ON TABLE comercial.comisiones IS 'Comisión del vendedor por venta.';
CREATE INDEX idx_comisiones_venta ON comercial.comisiones(id_venta);
CREATE INDEX idx_comisiones_vendedor ON comercial.comisiones(id_vendedor);

-- casos_postventa: Casos PQRS y garantías, integrados al cliente y a la venta.
CREATE TABLE comercial.casos_postventa (
  id_caso         SERIAL PRIMARY KEY,
  tipo_caso       tipo_caso_enum NOT NULL,
  id_cliente      INT NOT NULL REFERENCES comercial.clientes(id_cliente),
  id_venta        INT REFERENCES comercial.ventas(id_venta),
  id_producto     INT REFERENCES comercial.productos(id_producto) ON DELETE SET NULL,  -- Producto en garantía (opcional)
  id_responsable  INT REFERENCES seguridad.usuarios(id_usuario),
  asunto          VARCHAR(150) NOT NULL,
  descripcion     VARCHAR(500),
  prioridad       VARCHAR(10) DEFAULT 'MEDIA',
  estado          VARCHAR(15) DEFAULT 'ABIERTO',
  resultado       VARCHAR(20),
  valor_reembolso NUMERIC(15,2) DEFAULT 0 CHECK (valor_reembolso >= 0),
  fecha_reporte   TIMESTAMP DEFAULT now(),
  fecha_cierre    TIMESTAMP,
  calificacion    INT CHECK (calificacion BETWEEN 1 AND 5)  -- Encuesta de satisfacción 1-5 al cerrar el caso
);
COMMENT ON TABLE comercial.casos_postventa IS 'Casos PQRS y garantías, integrados al cliente y a la venta.';
CREATE INDEX idx_casos_postventa_cliente ON comercial.casos_postventa(id_cliente);
CREATE INDEX idx_casos_postventa_venta ON comercial.casos_postventa(id_venta);
CREATE INDEX idx_casos_postventa_producto ON comercial.casos_postventa(id_producto);
CREATE INDEX idx_casos_postventa_responsable ON comercial.casos_postventa(id_responsable);
CREATE INDEX idx_casos_estado ON comercial.casos_postventa(estado);

-- movimientos_fidelizacion: Puntos de fidelización por cliente (acumulación, redención, ajuste).
CREATE TABLE comercial.movimientos_fidelizacion (
  id_movimiento    SERIAL PRIMARY KEY,
  id_cliente       INT NOT NULL REFERENCES comercial.clientes(id_cliente),
  id_venta         INT REFERENCES comercial.ventas(id_venta) ON DELETE SET NULL,  -- Venta que originó el movimiento (si aplica)
  tipo_movimiento  tipo_mov_fidelizacion_enum NOT NULL,
  puntos           INT NOT NULL CHECK (puntos <> 0),  -- Positivo acumula, negativo redime
  descripcion      VARCHAR(150),
  fecha_movimiento TIMESTAMP NOT NULL DEFAULT now()
);
COMMENT ON TABLE comercial.movimientos_fidelizacion IS 'Puntos de fidelización por cliente (acumulación, redención, ajuste).';
CREATE INDEX idx_movimientos_fidelizacion_cliente ON comercial.movimientos_fidelizacion(id_cliente);
CREATE INDEX idx_movimientos_fidelizacion_venta ON comercial.movimientos_fidelizacion(id_venta);

-- actividades: Seguimiento comercial y de postventa (llamadas, correos, reuniones, tareas).
CREATE TABLE comercial.actividades (
  id_actividad        SERIAL PRIMARY KEY,
  tipo_actividad      tipo_actividad_enum NOT NULL,
  id_cliente          INT NOT NULL REFERENCES comercial.clientes(id_cliente) ON DELETE CASCADE,  -- Toda actividad pertenece a un cliente
  id_oportunidad      INT REFERENCES comercial.oportunidades(id_oportunidad) ON DELETE SET NULL,
  id_caso             INT REFERENCES comercial.casos_postventa(id_caso) ON DELETE SET NULL,  -- Seguimiento de postventa
  asunto              VARCHAR(150) NOT NULL,
  descripcion         VARCHAR(500),
  fecha_programada    TIMESTAMP,
  fecha_realizada     TIMESTAMP,
  estado              VARCHAR(15) DEFAULT 'PENDIENTE',
  resultado           VARCHAR(255),
  id_usuario_asignado INT NOT NULL REFERENCES seguridad.usuarios(id_usuario),
  fecha_creacion      TIMESTAMP DEFAULT now()
);
COMMENT ON TABLE comercial.actividades IS 'Seguimiento comercial y de postventa (llamadas, correos, reuniones, tareas).';
CREATE INDEX idx_actividades_cliente ON comercial.actividades(id_cliente);
CREATE INDEX idx_actividades_oportunidad ON comercial.actividades(id_oportunidad);
CREATE INDEX idx_actividades_caso ON comercial.actividades(id_caso);
CREATE INDEX idx_actividades_usuario_asignado ON comercial.actividades(id_usuario_asignado);

-- ============================================================================
-- REGLAS DE NEGOCIO QUE NO SE EXPRESAN EN EL DDL (implementar en la aplicación
-- o con triggers)
--   * ventas.id_cliente debe coincidir con oportunidades.id_cliente cuando
--     ventas.id_oportunidad no es NULL.
--   * ventas.subtotal/descuento_total/impuesto_total/total son un resumen de
--     detalle_venta: recalcular al guardar las líneas.
--   * Al pasar una oportunidad a GANADA, la venta pasa a CONFIRMADA y el
--     cliente a etapa NUEVO; EN_RIESGO / INACTIVO por inactividad comercial.
--   * movimientos_fidelizacion: el saldo de puntos es SUM(puntos) por cliente.
--   * Cambios relevantes (estado de venta, etapa, caso) se registran en
--     seguridad.bitacora_eventos.
-- ============================================================================