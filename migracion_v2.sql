-- Ejecutar una sola vez sobre la base anterior. No elimina contactos.
ALTER TABLE contactos ADD COLUMN favorito TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
-- Los contactos antiguos reciben la fecha de migración, no su fecha original.
