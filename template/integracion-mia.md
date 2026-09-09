# Integración NOVA ↔ mIA (Mesa de Ayuda) — módulo `gobierno_datos/integracion_mia/`

> Última revisión: 2026-09-07. Antes de tocar cualquier archivo de
> `template/gobierno_datos/integracion_mia/`, leer este documento completo.

## 1. Qué es esto

NOVA necesita datos de tickets de mIA (la Mesa de Ayuda interna de Organización MAS,
`chat_web_interno` / `miachat`). Este módulo es la pieza, del lado de NOVA, que trae esos datos.

Es una carpeta **aislada**, con su propia base de datos, sin tocar `conection.php`,
`sesion.php` ni ningún otro módulo de la plataforma — mismo criterio que ya usan
`gobierno_datos/bitacora_produccion/` y `gobierno_datos/bitacora_mantenimiento/`.

## 2. La decisión de diseño que hay que entender antes que nada

**NOVA no tiene salida pública y no la tendrá.** Por eso el diseño está invertido respecto a lo
que uno esperaría por defecto:

- ❌ mIA **NUNCA** llama a NOVA, ni empuja nada hacia acá.
- ✅ **NOVA llama a mIA**, periódicamente, y le pregunta "¿hay tickets nuevos?".

Es el mismo principio que ya usa mIA con sus agentes ETL on-premise (ej. el de báscula): quien
está detrás de una red cerrada es quien debe llamar hacia afuera, nunca al revés. mIA sí tiene
salida pública (es un servicio en la nube), así que es NOVA quien inicia siempre la conversación.

```
NOVA (cron, cada ~15 min)                          mIA (nube, siempre disponible)
┌─────────────────────────┐                        ┌──────────────────────────────────┐
│ consultar_mia.php        │  GET /api/nova/pull/    │ Backend/app/services/nova/router  │
│  1. calcula su cursor    │  tickets?since=...       │  - valida X-Nova-Api-Key          │
│     (MAX(fecha_act.) ya  │ ────────────────────►   │  - valida nova_sync_enabled       │
│     guardado)            │                          │  - SOLO LECTURA sobre tickets    │
│  2. llama con la llave   │  ◄──── JSON con los      │  - registra la consulta          │
│  3. hace upsert en        │       tickets pendientes │    (nova_sync_log, para el panel │
│     tickets_mia          │                          │    admin de mIA)                 │
└─────────────────────────┘                        └──────────────────────────────────┘
```

## 3. Archivos de este módulo

| Archivo | Rol |
|---|---|
| `conexion_mia.php` | PDO propio y exclusivo hacia la base `mia_datos`. No reutiliza `conection.php` ni la Postgres de `bitacora_mantenimiento`. |
| `config_mia.example.php` | Plantilla versionada en git — nombres de las constantes, sin valores reales. |
| `config_mia.local.php` | Valores **reales** (llave + URL de mIA) — está en `.gitignore`, nunca se sube. Hay que crearlo a mano en cada servidor. |
| `sync_logic.php` | El motor real de la sincronización (`ejecutar_sync_tickets()`) — una sola función, compartida por los dos disparadores de abajo. Nunca se llama directo por HTTP. |
| `consultar_mia.php` | Disparador **por cron** — wrapper de línea de comandos sobre `sync_logic.php`. Ver §6. |
| `panel.php` | Disparador **manual** — botón "🔄 Sync mIA" en `template/analitica/dashboard_bitacora.php` (el dashboard de Bitácora de Mantenimiento más reciente, con D3.js/GSAP — no el de `gobierno_datos/bitacora_mantenimiento/`, que es la versión anterior). Exige sesión iniciada (`sesion.php::verificarAutenticacion()`) antes de correr el sync. |

## 4. Base de datos: `mia_datos` (exclusiva, no compartir)

Se creó una base **nueva y separada** — no usa `usuarios`, `control_molienda`, `maquinas` ni la
Postgres de `bitacora_mantenimiento`. Así, un problema en esta integración no puede afectar a
ningún otro módulo de la plataforma.

```sql
CREATE DATABASE IF NOT EXISTS mia_datos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tickets_mia (
    id_ticket_mia INT PRIMARY KEY,
    asunto VARCHAR(500),
    estado VARCHAR(100),
    prioridad VARCHAR(50),
    categoria VARCHAR(200),
    subcategoria VARCHAR(200),
    mesa_servicio VARCHAR(100),
    departamento VARCHAR(200),
    sede VARCHAR(200),
    solicitante_email VARCHAR(255),
    especialista_email VARCHAR(255),
    tipo_ticket VARCHAR(100),
    -- OJO con la precisión — ver §7, es la causa de un bug real ya encontrado y corregido
    fecha_creacion DATETIME(6) NULL,
    fecha_actualizacion DATETIME(6) NULL,
    fecha_cierre DATETIME(6) NULL,
    sincronizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mia_sync_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consultado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    since_enviado DATETIME NULL,
    registros_recibidos INT DEFAULT 0,
    estado VARCHAR(20),
    error TEXT NULL
) ENGINE=InnoDB;
```

`mia_sync_log` es el historial de las propias consultas de NOVA — para diagnosticar problemas
sin depender de mirar el panel del lado de mIA.

## 5. Configuración (`config_mia.local.php`)

Crear este archivo a mano en cada servidor (nunca viaja con git):

```php
<?php
define('MIA_API_KEY', 'LA_LLAVE_REAL_AQUI');
define('MIA_BASE_URL', 'https://miachat.organizacionmas.com'); // dominio real de mIA en producción
```

- **`MIA_API_KEY`** debe ser **exactamente igual** a `NOVA_PULL_API_KEY` configurada del lado de
  mIA (su `.env`/`docker-compose.prod.yml`). Es la llave que mIA exige en el header
  `X-Nova-Api-Key` — sin ella, o con una llave distinta, mIA responde `401`.
- **`MIA_BASE_URL`** es el dominio público real de mIA. **Nunca** `localhost` en producción —
  ese valor solo se usó durante el desarrollo de este módulo, cuando NOVA y mIA convivían en el
  mismo servidor de pruebas.

## 6. Puesta en marcha — programar el cron (pendiente de hacer en cada servidor)

Este script **no corre solo** hasta que se programe. Mismo patrón que ya usa la plataforma en
`template/activador.js` (node-cron), pero aquí es más simple: un cron de sistema que invoca PHP
directo.

```bash
# crontab -e
*/15 * * * * php /var/www/fmt/template/gobierno_datos/integracion_mia/consultar_mia.php >> /var/log/mia_sync.log 2>&1
```

Para probar manualmente antes de dejarlo en cron:
```bash
php /var/www/fmt/template/gobierno_datos/integracion_mia/consultar_mia.php
```
Imprime `OK: N registro(s) recibidos y guardados`, o el error si algo falló (llave incorrecta,
mIA no disponible, `nova_sync_enabled` apagado del lado de mIA, etc.).

**Alternativa manual desde el navegador**: el botón "🔄 Sync mIA" en
`template/analitica/dashboard_bitacora.php` (el dashboard de Bitácora de Mantenimiento vigente)
dispara el mismo motor (`panel.php` → `sync_logic.php`) con un clic, para cualquiera con sesión
iniciada — útil para forzar una sincronización puntual sin esperar al cron, o mientras el cron
todavía no esté programado.

## 7. Cómo funciona el cursor (importante para no reintroducir el bug ya corregido)

NOVA **no tiene una tabla de cursor aparte** — cada vez que `consultar_mia.php` corre, calcula
`SELECT MAX(fecha_actualizacion) FROM tickets_mia` y le manda ese valor a mIA como `since`. mIA
solo devuelve tickets con `fecha_actualizacion` estrictamente mayor a eso.

**Por qué las columnas de fecha son `DATETIME(6)` y no `DATETIME` a secas**: mIA (Postgres) guarda
las fechas con microsegundos. Si la columna de NOVA (MySQL) las trunca a segundos enteros, el
cursor que NOVA calcula queda "por debajo" del valor real que tiene mIA, y el mismo ticket se
vuelve a traer en cada corrida, en un loop infinito (inofensivo por el `ON DUPLICATE KEY UPDATE`,
pero incorrecto e innecesario). **Ya se corrigió una vez durante el desarrollo — si se recrea esta
tabla desde cero, no olvidar `DATETIME(6)`, no `DATETIME`.**

## 8. Checklist para llevar esto a producción

- [ ] Copiar esta carpeta (`template/gobierno_datos/integracion_mia/`) al servidor real de NOVA.
- [ ] Crear la base `mia_datos` y sus tablas ahí (script de §4 — con `DATETIME(6)`).
- [ ] Crear `config_mia.local.php` **directamente en ese servidor** con la llave real (coordinada
      con quien administre mIA) y `MIA_BASE_URL` apuntando al dominio público real de mIA.
- [ ] Confirmar que el servidor de NOVA tiene salida a internet hacia ese dominio (una prueba
      simple de `curl`/conectividad antes de confiar en el cron).
- [ ] Programar el cron de §6.
- [ ] Coordinar con el equipo de mIA: que `NOVA_PULL_API_KEY` esté configurada en su entorno real
      y que un Admin encienda `nova_sync_enabled` desde su panel — sin eso, mIA responde `401`/`503`
      aunque todo lo de acá esté perfecto.
- [ ] Probar `php consultar_mia.php` a mano una vez contra el entorno real antes de confiar
      solamente en el cron.

## 9. Filtro de seguridad del lado de mIA: solo mesa MANTENIMIENTO

mIA aplica un filtro de seguridad, **fail-closed**, que restringe qué tickets puede ver NOVA
por mesa de servicio (`clave nova_mesas_permitidas` en `SystemConfig`, del lado de mIA — hoy
configurada solo con `MANTENIMIENTO`). NOVA no controla ni conoce este filtro: simplemente
recibe menos tickets de los que existen en total en mIA, y eso es intencional.

- De las 6 mesas que existen como concepto en mIA (`TI`, `MANTENIMIENTO`, `COMPRAS`, `NC`,
  `FINANZAS`, `VENTAS`), solo 4 llegan a tener tickets en la tabla que consulta este módulo
  (`NC` y `VENTAS` usan otros modelos, nunca tickets). De esas 4, **solo `MANTENIMIENTO`** está
  autorizada hoy para NOVA.
- Si en el futuro se necesita traer más mesas a NOVA, el cambio se hace **del lado de mIA**
  (agregar la mesa a `nova_mesas_permitidas`, separada por coma) — no requiere ningún cambio en
  este módulo de NOVA.
- Si alguna vez `nova_mesas_permitidas` queda vacía por error de configuración del lado de mIA,
  el diseño es fail-closed: NOVA recibirá **cero tickets**, nunca todos — nunca hay que
  interpretar "0 registros recibidos" de forma prolongada como una falla de NOVA sin antes
  descartar esto del lado de mIA.

## 10. Errores comunes (ver `mia_sync_log.error` para el detalle exacto)

| Síntoma | Causa probable |
|---|---|
| HTTP 401 al llamar a mIA | `MIA_API_KEY` no coincide con `NOVA_PULL_API_KEY` del lado de mIA |
| HTTP 503 al llamar a mIA | `nova_sync_enabled` está apagado del lado de mIA (un Admin lo prende desde su panel) |
| Error de red / timeout | `MIA_BASE_URL` mal configurada, o no hay salida a internet desde el servidor de NOVA hacia ese dominio |
| El mismo ticket se repite en cada corrida | Columnas de fecha sin precisión de microsegundos — ver §7 |

## 11. Referencia

Contexto completo del diseño (incluyendo por qué se descartó primero un modelo donde mIA
empujaba hacia NOVA) en la bitácora del lado de mIA:
`BITACORA_REESTRUCTURACION_PERMISOS.md` (fuera de este repo, en la raíz de `MIA PRUEBAS`).
