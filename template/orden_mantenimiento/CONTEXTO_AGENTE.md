# Contexto del Agente: Sistema de Orden de Mantenimiento (S.O.M. V2)

Este documento describe la arquitectura, flujo de datos y estructura del módulo **Orden de Mantenimiento V2**, para proporcionar contexto a Claude Code (o cualquier agente de IA) al momento de realizar modificaciones, depurar o expandir este sistema.

## 1. Descripción General
El **S.O.M. V2** es una digitalización del "Formato 001 Modernizado" para la gestión de mantenimientos (locativos, mecánicos, eléctricos). Permite crear, guardar borradores, procesar, visualizar y enviar a una bitácora central los reportes de mantenimiento, incluyendo evidencias fotográficas, firmas digitales y tablas dinámicas de insumos.

## 2. Estructura de Archivos
El código fuente de este módulo se encuentra en `/var/www/fmt/template/orden_mantenimiento/`.

| Archivo | Propósito |
|---------|-----------|
| `index.php` | Formulario principal (UI). Contiene las 8 secciones del formato (clasificación, equipo, mediciones, firmas, ejecución, inocuidad, control de partes y evidencias). |
| `script.js` | Lógica de la interfaz (Frontend). Controla el Canvas para las firmas (soporte touch/mouse), la inserción/eliminación dinámica de filas en tablas y el toggle de secciones. |
| `guardado.js` | Sistema de autoguardado y borradores. Serializa el estado del formulario (incluyendo tablas dinámicas y Canvas a Base64) y se comunica con los endpoints de borrador. |
| `procesar.php` | Controlador de guardado final. Recibe el `POST`, procesa archivos subidos (imágenes) y decodifica las firmas Base64. Guarda el registro en un archivo JSON segmentado por Sede y Mes (`YYYY-MM.json`). |
| `visor.php` | Visor de impresión PDF. Lee un registro del JSON y utiliza una plantilla HTML (`../plantillas/formulario001.html`) reemplazando tokens (ej. `{{ordendetrabajo}}`) para generar la vista final imprimible. |
| `galeria.php` | Historial de órdenes. Lee los archivos JSON generados y muestra un listado de las órdenes. Permite ver el detalle (`visor.php`) o enviar la orden a la bitácora central. |
| `enviar_a_bitacora.php` | Endpoint que toma un registro JSON local y lo envía vía `POST` interno a `http://localhost/template/gobierno_datos/bitacora_mantenimiento/recoleccion_envio.php`. |
| `guardar_borrador.php` | Endpoint para guardar el estado temporal (JSON) en la carpeta `guardados/`. |
| `cargar_borrador.php` | Endpoint para leer un borrador específico. |
| `borrar_guardado.php` | Endpoint para limpiar el borrador después de que la orden es procesada exitosamente. |
| `ordenes_pendientes.php` | (No detallado pero presente) Interfaz para listar y retomar borradores guardados temporalmente. |

## 3. Flujo de Datos y Estado (State Management)

### 3.1. Formularios y Tablas Dinámicas
Las tablas dinámicas (Herramientas, Piezas, Materiales y Mediciones Predictivas) utilizan arrays en los atributos `name` de los inputs (ej. `tool_cant[]`, `tool_desc[]`). 
El archivo `script.js` expone funciones globales (`window.addRow`, `window.deleteRow`) para clonar la primera fila y limpiar sus valores.

### 3.2. Firmas Digitales (Canvas)
- **UI:** Gestionado en `script.js` (`setupCanvas`). Captura eventos de puntero y táctiles para dibujar.
- **Preparación de envío:** Al hacer submit o guardar borrador (`guardado.js`), se extrae el contenido del Canvas mediante `canvas.toDataURL()` y se inyecta en un input oculto (ej. `#canvas_solicitante_input`).
- **Backend:** `procesar.php` recibe el Base64, lo decodifica y lo guarda como un archivo `.png` físico en `../../archivos/generados/orden_mantenimiento/evidencias/`.

### 3.3. Sistema de Borradores (Drafts)
`guardado.js` tiene la lógica crítica para evitar la pérdida de datos:
1. `guardarBorradorServidor()` recopila `FormData`, captura selectores deshabilitados y extrae los Canvas a Base64. Envía este JSON a `guardar_borrador.php`.
2. `cargarBorradorAutomaticoV2()` es llamado al inicio si hay una bandera en `localStorage` (`cargarBorradorV2`). Se encarga de reconstruir el formulario, re-disparar los clics en los botones de "Agregar fila" para recrear el tamaño de los arrays y re-dibujar las imágenes Base64 en los Canvas.

### 3.4. Persistencia JSON
Los datos finales no van a una base de datos relacional estándar, sino que se almacenan en un esquema JSON basado en archivos:
- **Ruta:** `../../archivos/generados/orden_mantenimiento/{SEDE}/{YYYY-MM}.json`
- **Estructura Interna:** Un array de objetos, donde cada objeto contiene:
  - `id` (Uniqid)
  - `timestamp`
  - `usuario_creador` y `sede`
  - `datos` (Todos los campos de texto y arrays serializados)
  - `evidencias` (Nombres de archivos de las fotos)
  - `firmas` (Nombres de archivos PNG de los Canvas)

### 3.5. Integración con Bitácora
El sistema local de "S.O.M. V2" actúa como un sistema de origen. `galeria.php` permite al usuario auditar los registros locales y "Subirlos" mediante `enviar_a_bitacora.php`. Este script hace un re-mapeo de campos (`tipo_mantenimiento_especial`, `especialidad`, `zona`) para que coincidan con la estructura que espera la bitácora central (`gobierno_datos`).

## 4. Convenciones de Estilo y Visualización
- Se usan utilidades CSS custom y variables de tema (e.g. `var(--primary)`, `var(--accent2)`, `var(--border)`).
- **Tema Visual:** `orden_mantenimiento_v2.css`
- **Renderizado PDF/Impresión:** `visor.php` utiliza el esquema de plantillas antiguas (`formulario001.html`). Usa la técnica de inyección de cadenas (`str_replace`) para renderizar un documento oficial (apto para `window.print()`). Las iteraciones para tablas dinámicas están capadas por un límite duro en PHP (max 8 herramientas, max 8 materiales, max 10 mediciones).

## 5. Directrices para el Agente (Claude Code)
1. **Modificar Formularios:** Si agregas un campo nuevo a `index.php`, asegúrate de que el visor de impresión (`../plantillas/formulario001.html` y `visor.php`) esté preparado para recibir y mostrar ese dato a través de un tag `{{nuevo_campo}}`.
2. **Tablas Dinámicas:** Si agregas una tabla dinámica nueva, recuerda registrar su botón/ID en el diccionario `tablasConfig` de `guardado.js` para que el autoguardado sepa cuántas filas clonar al recuperar el borrador.
3. **Firmas y Evidencias:** Cualquier nueva imagen generada o subida debe procesarse a través del callback seguro en `procesar.php` (`$processImage` o `$processSignature`).
4. **Integridad JSON:** No elimines campos preexistentes de `procesar.php`, ya que `galeria.php` y el histórico dependen de la estructura antigua. Si cambias la estructura de los datos, asegúrate de mantener compatibilidad hacia atrás en `visor.php`.
