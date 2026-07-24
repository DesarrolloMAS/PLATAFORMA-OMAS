# 🏗️ PLATAFORMA-OMAS | Contexto de Desarrollo para IA

Este documento proporciona un mapa técnico de alta densidad para optimizar la comprensión del sistema y reducir el consumo de tokens.

## 🚀 Arquitectura del Sistema
- **Frontend**: HTML5, Vanilla JS, CSS3. Librerías: SweetAlert2, Select2, jQuery (mínimo).
- **Backend**: PHP 7.4+ (Lógica procesal).
- **Persistencia Mixta**:
  - **JSON**: Almacenamiento principal de registros operativos.
    - Módulos con sede: `archivos/generados/[modulo]/[sede]/[YYYY-MM].json` (ej: molienda).
    - Módulos sin sede: `archivos/generados/[modulo]/[YYYY-MM].json` (ej: empaque_v2, inspeccion_empaque).
  - **SQL (MariaDB)**: 4 bases de datos activas:
    - `usuarios`: Tabla de usuarios, cargos, sedes y roles.
    - `control_molienda`: Control de turnos ZC/ZB.
    - `maquinas`: Gestión de equipos y mantenimiento.
  - **Sincronización**: Módulo `gobierno_datos/bitacora_produccion/sincronizador.php` para integración con Postgres y Excel.

## 📁 Mapa de Directorios Clave
- `/template`: **Núcleo del sistema**.
  - **Módulos Operativos**:
    - `/molienda_v2`: Reporte de producción de molienda (3 turnos, ZC/ZB). Usa `procesar.php`.
    - `/empaque_v2`: Control de empaque con catálogo de empaques.
    - `/inspeccion empaque`: Inspección de materiales de empaque.
    - `/Calidad`: PNC, liberaciones, etiquetado, muestras.
    - `/HSEQ`: Investigación de accidentes y revisión de muestras.
    - `/envasado`, `/premezclas`, `/reprocesos`: Otros módulos de producción.
    - `/bodegas`: Inspección de bodegas (múltiples bodegas por sede). Persistencia mixta: JSON + archivos `ultima_verificacion_[Bodega].xlsx.txt` para estado de última revisión por bodega.
    - `/orden_mantenimiento`, `/liberaciones_mant`, `/termohigrometros`: Mantenimiento e instrumentos.
    - `/gobierno_datos`: Sincronización Postgres/Excel (bitacora_produccion).
  - `conection.php`: 4 conexiones PDO: `$pdoUsuarios`, `$pdoControl`, `$pdoControl_zs`, `$pdomaquinas`.
  - `procesar.php` (en cada módulo): Endpoint POST que retorna JSON y persiste en archivo.
  - `visor_[nombre].php`: Renderiza el JSON de un registro como vista imprimible.
  - `/central_documental`: **Central de Documentación y SharePoint**
    - `hub_reportes.php`: Panel central de reportes.
    - `galeria_unificada.php`: Galería de documentos con exportación a PDF y SharePoint.
    - `sharepoint_upload.php`: Endpoint que procesa e inicia la subida de los archivos.
    - `generate_pdf_headless.js`: Script de Puppeteer para compilar PDF al vuelo.
- `/archivos/generados`: Almacén persistente de archivos JSON y PDFs. Subdirectorios reales:
  - `molienda/ZC/`, `molienda/ZS/`: JSON mensuales de molienda.
  - `empaque_v2/`, `inspeccion_empaque/`, `envasado/`, `premezclas/`: Otros módulos.
  - `Calidad/`, `HSEQ/`, `PNC/`, `reprocesos_zc/`, `reprocesos_zs/`: Calidad y HSEQ.
  - `orden_mantenimiento/`, `liberaciones_mant/`, `termohigrometros/`: Mantenimiento.
  - Carpetas PDF con nombres propios: `pdfsC_M/`, `pdfsINS/`, `proces_molienda_pdf/`, `premezclas_pdfs/`, `envasado_pdf/`, `reprocesos_zc_pdf/`, etc.
- `/archivos/formularios_guardados`: Borradores de formularios en proceso.
- `/data/borradores`: Borradores alternativos.
- `/.agent/workflows`: Procedimientos estandarizados para la IA (ej: `crear-formato.md`).

## 🛠️ Estándares de Datos y Turnos
- **Sedes**: `ZS` (Zona Sur), `ZC` (Zona Centro), `ZB` (Buga).
- **Turnos**: ZC/ZB usan 3 turnos por día; ZS usa 2 turnos por día.
- **Lógica Lineal**: Los registros se guardan cronológicamente. Si un día tiene < 3 turnos (o 2 en ZS), el sistema asigna el nuevo registro al día pendiente más antiguo antes de avanzar a la fecha enviada.
- **Identificadores**: Uso de `uniqid()` para `id` de cada registro dentro del JSON.
- **Firmas**: Validación obligatoria por cédula (`cedula_u`) contra la tabla `usuarios`. Retorna nombre del firmante.
- **Sesión**: Variables clave: `$_SESSION['sede']`, `$_SESSION['id_usuario']`, `$_SESSION['rol']`, `$_SESSION['area']`, `$_SESSION['cargo']`.
- **Roles**: `adm` / `1` = Administrador; `3` = Operador. Áreas: `Operaciones`, `Calidad`.

## 📜 Convenciones de Nombres
- **Formularios**: `index.php` o `[nombre].html` dentro de la carpeta del módulo (ej: `empaque_v2.html`).
- **Procesamiento**: `procesar.php` o `procesar_[nombre].php` recibe POST y retorna `{status, message}` en JSON.
- **Visores**: `visor_[nombre].php` para renderizar el JSON de un registro en formato imprimible/PDF.
- **Galerías**: `galeria_[nombre].php` o `rev_[nombre].php` para listar y filtrar registros del módulo.
- **Config de estructura**: `config_[sede].json` (ej: `config_ZC.json`) en algunos módulos para definir productos activos.

## 🔄 Estado de Migración (Legacy vs. Nuevo Estándar)
El sistema se encuentra en transición desde tecnologías antiguas hacia un estándar más moderno y estético.
- **Patrón Legacy (Evitar para código nuevo)**: Formularios de múltiples pasos, persistencia mixta (BD/Excel/JSON antiguo), exportación PDF desde backend usando **mPDF**.
- **Nuevo Estándar (Referencia obligatoria)**: Formularios HTML limpios (diseño "Cyberpunk", colores neón/oscuros), comunicación vía `fetch` asíncrono, almacenamiento JSON consolidado por sede/mes, renderizado PDF en frontend con **jsPDF + html2canvas**. (Ejemplo de referencia: `inspeccion empaque/`).

## 🔗 Integraciones
- **PDF**: Generación vía mPDF (PHP, `vendor/`) o Puppeteer headless (`template/central_documental/generate_pdf_headless.js`, usa `chrome-linux64/`).
- **SharePoint**: Subida automatizada vía `template/central_documental/sharepoint_upload.php` y `template/uploader_selective.js`.
- **Menús por Rol/Área**: Archivos `menu_[perfil].html` (ej: `menu_adm_calidad.html`, `menu_produccion.html`, `menu_mantenimiento.html`, `menu_almacen.html`, `menu_hseq.html`).

---
> [!TIP]
> Al trabajar en un módulo, prioriza leer el `procesar.php` para entender la estructura del JSON que maneja ese módulo específico.
