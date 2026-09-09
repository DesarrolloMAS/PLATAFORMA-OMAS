# Instrucciones para Claude - PLATAFORMA-OMAS

Lee el archivo `README.md` en la raíz antes de responder cualquier pregunta sobre este proyecto. Contiene el mapa completo de la arquitectura, módulos, rutas de persistencia y convenciones del sistema.

## Reglas de Comportamiento:
1. **Contexto Primero**: No explores carpetas a ciegas sin antes consultar el `README.md`.
2. **Patrón de Creación**: Para crear nuevos formatos, DEBES leer `.agent/workflows/crear-formato.md` y respetar el diseño visual documentado en `.agent/workflows/estilos-css.md`. Usa la estructura de `template/inspeccion empaque/` como referencia (es el estándar actual).
3. **Módulo Legacy vs Estándar Nuevo**: Verifica siempre en qué estado de migración se encuentra el módulo antes de modificarlo (mPDF vs jsPDF, etc).
4. **Persistencia JSON**: Respeta siempre la ruta de guardado por sede especificada en el README.
5. **Patrón de Menús**: Para crear o rediseñar páginas de menú (no formularios), DEBES leer `template/crear-menu-hub.md` — documenta el patrón "Hub Radial" (métricas de posición, engranaje central, tema claro/invertido). Referencia actual: `template/menu_adm.html`, `template/menu_administracion.html`, `template/menu_mantenimiento.html`.
6. **Integración con mIA**: Para tocar cualquier archivo de `template/gobierno_datos/integracion_mia/`, DEBES leer `template/integracion-mia.md` primero — documenta el diseño (NOVA consulta a mIA, nunca al revés, porque NOVA no tiene salida pública), la base de datos exclusiva `mia_datos`, la configuración requerida y el cron pendiente de programar.
