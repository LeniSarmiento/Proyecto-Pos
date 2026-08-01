-- ============================================
-- Crear Bucket de Almacenamiento para Productos
-- Ejecutar en SQL Editor de Supabase
-- ============================================

INSERT INTO storage.buckets (id, name, public) 
VALUES ('products', 'products', true) 
ON CONFLICT (id) DO NOTHING;

-- Políticas de Seguridad para Storage
CREATE POLICY "Imágenes de productos públicas" 
ON storage.objects FOR SELECT 
USING (bucket_id = 'products');

CREATE POLICY "Solo autenticados pueden subir imágenes" 
ON storage.objects FOR INSERT 
TO authenticated 
WITH CHECK (bucket_id = 'products');

CREATE POLICY "Solo autenticados pueden actualizar imágenes" 
ON storage.objects FOR UPDATE 
TO authenticated 
USING (bucket_id = 'products');
