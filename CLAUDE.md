# Instrucciones para Claude - PLATAFORMA-OMAS

Lee el archivo `README.md` en la raíz antes de responder cualquier pregunta sobre este proyecto. Contiene el mapa completo de la arquitectura, módulos, rutas de persistencia y convenciones del sistema.

## Reglas de Comportamiento:
1. **Contexto Primero**: No explores carpetas a ciegas sin antes consultar el `README.md`.
2. **Patrón de Creación**: Para crear nuevos formatos, DEBES leer `.agent/workflows/crear-formato.md` y respetar el diseño visual documentado en `.agent/workflows/estilos-css.md`. Usa la estructura de `template/inspeccion empaque/` como referencia (es el estándar actual).
3. **Módulo Legacy vs Estándar Nuevo**: Verifica siempre en qué estado de migración se encuentra el módulo antes de modificarlo (mPDF vs jsPDF, etc).
4. **Persistencia JSON**: Respeta siempre la ruta de guardado por sede especificada en el README.
