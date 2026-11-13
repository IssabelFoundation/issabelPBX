# IssabelPBX: deploy-modules

[Español](#español) · [English](#english)

## Español

Actualiza módulos de IssabelPBX desde los archivos confirmados en un clon de Git,
usando las rutinas de la instalación para dependencias, instalación, migraciones
y recarga. Producción conserva sus directorios habituales.

### Inicio rápido

Ejecutá en el servidor PBX, desde el clon con el código que querés instalar:

```sh
cd /usr/src/issabelPBX-git
sudo ./tools/deploy-modules
```

El comando detecta el repositorio desde la ubicación de `tools`, compara sus
módulos contra lo instalado y pregunta qué actualizar. No requiere conocer el
commit instalado ni preparar un plan manualmente. La fuente es el commit `HEAD`:
los cambios locales sin commit no se despliegan. La herramienta no descarga código
ni ejecuta `git pull`; actualizá y revisá el clon antes de usarla.

También se puede indicar otro clon:

```sh
sudo ./tools/deploy-modules update --repo /ruta/al/clon
```

Mantené `deploy-modules` y la carpeta `module-deploy` juntos, fuera del directorio
web. El clon debe ser independiente de los módulos instalados. Se admite un clon
bajo `/root`: el auxiliar nativo se prepara temporalmente para que el usuario de
Asterisk pueda ejecutarlo sin acceder a credenciales ni respaldos privados.

### Selección y aplicación

Por cada módulo instalado que tenga diferencias se muestran la versión, los
archivos afectados y si necesita ejecutar el instalador.

| Respuesta | Acción |
| --- | --- |
| `s` | Seleccionar el módulo. |
| Enter o `n` | Omitirlo. |
| `t` | Seleccionar el actual y los siguientes que ya estén instalados. No vuelve a seleccionar los omitidos anteriormente. |
| `d` | Mostrar los archivos afectados y volver a preguntar. |
| `q` | Cancelar sin aplicar. |

Los módulos ausentes se omiten y se enumeran al finalizar. Tener una carpeta de
módulo no equivale a estar instalado en la base de Issabel. Una dependencia ausente
o que necesita actualizarse se ofrece por separado; requiere confirmación expresa,
incluso después de responder `t`. Los módulos deshabilitados conservan su estado.

Aceptar un módulo autoriza reemplazar sus archivos diferentes, incluidas las
personalizaciones locales, por el contenido del commit seleccionado. El modo
interactivo conserva archivos que solo existen en producción y omite los archivos
`*custom*.conf`. Verifica nuevamente los destinos antes de escribir y se detiene
si detecta cambios respecto de la comparación aceptada.

La confirmación final ejecuta estas operaciones:

1. Comprueba rutas, permisos, dependencias, sintaxis PHP de archivos modificados,
   espacio, base de datos y conexión con Asterisk Manager (AMI).
2. Respalda y prepara los archivos antes de modificar los destinos.
3. Actualiza los archivos y ejecuta los instaladores necesarios en orden de
   dependencias. No permite bajar versiones.
4. Regenera y recarga una vez al terminar el lote, sin reiniciar Asterisk.
5. Verifica la respuesta de recarga de AMI, las versiones y estados instalados,
   la conexión y los archivos desplegados. Registra el commit además de la versión.

El modo interactivo ejecuta el instalador cuando falta el módulo, cambia su versión
o cambian sus scripts de instalación. Una reinstalación con la misma versión se
valida primero con las dependencias nativas. También comprueba si `framework`
necesita completar migraciones aunque su versión de módulo ya coincida.

`framework` compara los destinos reales de sus archivos web, `bin` y AGI, prepara
el paquete y ejecuta su instalador antes de los módulos dependientes. Respeta los
permisos de archivos existentes que conserva la copia nativa; el contenido debe
coincidir con Git. Las plantillas iniciales de `amp_conf/astetc`, como `manager.conf`,
no reemplazan la configuración activa. No ejecuta `install_amp` ni instala los
archivos del repositorio que no pertenecen a módulos.

### Requisitos y operación

- Linux, root y PHP CLI **7.4 o superior**, con POSIX, SimpleXML y SQLite3 con soporte
  de respaldo en línea. Los módulos elegidos también deben admitir ese PHP.
- Git, GNU `cp`, `tar` y `stat`, `runuser`, y `mysqldump` si la base es MySQL/MariaDB.
  `tar` debe admitir ACL, atributos extendidos y SELinux; con SELinux se necesita
  también `restorecon`.
- Base principal MySQL/MariaDB o SQLite3 accesible, AMI operativo, una cuenta de
  Asterisk que no sea root y AstDB SQLite legible (`astdb.sqlite3`).
- Permisos SQL para respaldar tablas, vistas, procedimientos, eventos y triggers.
  El dump usa `--lock-tables` sobre la base seleccionada, también para MyISAM;
  no solicita un bloqueo global con `--lock-all-tables`.
- Espacio para archivos originales, paquetes preparados y respaldos de configuración,
  base de datos y AstDB. El modo interactivo requiere una terminal; por SSH usá `ssh -t`.

Usá una ventana de mantenimiento sin ediciones de configuración ni operaciones
paralelas de Module Admin. El bloqueo `/var/lock/issabelpbx-deploy.lock` serializa
esta herramienta, pero la interfaz web y otros servicios no comparten ese bloqueo.
Las verificaciones no inmovilizan todos los procesos que escriben en la PBX.

Si ya hay cambios pendientes, el modo interactivo pregunta si querés incluirlos en
la recarga final. Rechazarlos cancela la operación. Si aparecen cambios pendientes
no aprobados después de comparar, se detiene antes de escribir. Los comandos
`plan`, `rollback` y `recover` requieren que no haya cambios pendientes.

Para aplicar la configuración pendiente por consola:

```sh
sudo amportal a r
```

Si el indicador vuelve a activarse, revisá los procesos o integraciones que generan
configuración después de la recarga. `retrieve_conf` marca cambios pendientes;
por sí solo no completa la aplicación. No limpies el indicador para eludir la
verificación del despliegue.

### Respaldos, historial y recuperación

El estado se guarda en `/var/lib/issabelpbx-deploy`, fuera del directorio web y
accesible solo por root. Conservá su clave, planes e historial con esa PBX.
Los respaldos no se eliminan automáticamente.

```sh
sudo ./tools/deploy-modules history
```

Cada intento de aplicación guarda su registro en `deployments/ID/record.json`.
Según la etapa alcanzada, contiene los respaldos SQL o SQLite, AstDB, archivos de
módulos y configuración, sus metadatos, archivos preparados y registros nativos.
Los respaldos son por almacén: no constituyen una instantánea atómica entre todos
los almacenes de la PBX. Los registros y diferencias pueden contener información
privada.

Un fallo de `mysqldump` muestra el error y guarda hasta 64 KiB en `mysqldump.stderr`,
con las contraseñas configuradas ocultas. Elimina el SQL incompleto y el archivo
temporal de credenciales antes de detenerse.

| Estado | Qué hacer |
| --- | --- |
| `complete` | El despliegue terminó y pasó sus comprobaciones. |
| `aborted-before-write` | No se desplegaron archivos. Corregí la causa y repetí el comando. |
| `auto-rolled-back` | Se restauraron las escrituras del parche y la configuración, y se recargó. Revisá el error antes de reintentar. |
| `recovery-required` u otro estado incompleto | Revisá el registro y los respaldos. Bloquea nuevos despliegues hasta resolverlo. |

Para revertir un parche completado que **no ejecutó instaladores**:

```sh
sudo ./tools/deploy-modules rollback --deployment ID
```

Verifica los respaldos, los archivos, la configuración y el historial antes de
restaurar; rechaza modificaciones posteriores. Restaura archivos y configuración
y recarga una vez. No restaura la base de datos ni elimina archivos ajenos.

Para un fallo de comprobación de archivos **posterior a una recarga exitosa**:

```sh
sudo ./tools/deploy-modules recover --deployment ID
```

`recover` admite únicamente ese caso. Verifica el plan firmado, los respaldos de
archivos afectados, las rutas, el historial, los archivos desplegados y la salud de
la instalación. Si todo coincide, conserva el registro original y marca el
intento como completado. No copia archivos, repite migraciones ni recarga Asterisk.

Una vez iniciado un instalador, los fallos no se revierten automáticamente solo
con archivos: el código y la base pueden depender entre sí. Revisá las migraciones
y los respaldos para reparar o restaurar explícitamente. Restaurar la base puede
perder cambios posteriores al respaldo. Los estados incompletos requieren
reconciliación antes de volver a desplegar.

### Comandos avanzados

**Comparación de archivos sin ejecutar Issabel:**

```sh
sudo ./tools/deploy-modules inspect \
  --repo /usr/src/issabelPBX-git --to HEAD --modules core,sipsettings \
  --modules-dir /var/www/html/admin/modules \
  --output /root/issabel-comparison
```

`inspect` genera `report.json` y `differences.diff`, sin cargar el bootstrap,
consultar SQL/AMI ni recargar. Compara versiones de archivos, no migraciones SQL.
Requiere root, PHP CLI 7.4+, POSIX, SimpleXML y Git; no requiere las herramientas
de respaldo ni SQLite3. No admite `framework`, cuyos destinos están distribuidos.
La salida debe ser un directorio nuevo, fuera del clon y del directorio web.
Los archivos exclusivos de producción aparecen en el diff como eliminaciones
comparativas, no como una autorización para borrarlos. El límite de contenido
por archivo es 1 MiB; `--max-diff-bytes N` permite hasta 16 MiB. Un informe fallido
conserva un marcador `INCOMPLETE` y no debe usarse como resultado completo.

**Plan explícito basado en un commit instalado conocido:**

```sh
sudo ./tools/deploy-modules plan \
  --repo /usr/src/issabelPBX-git --from COMMIT_BASE --to COMMIT_DESTINO \
  --modules core,sipsettings
sudo ./tools/deploy-modules apply --plan /ruta/al/plan.json
```

Este modo exige que los archivos afectados y las versiones instaladas coincidan
con el commit base; se detiene ante personalizaciones. Solo admite módulos ya
instalados y habilitados, excluye `framework` y exige subir la versión si cambian
scripts de instalación. Los parches de la misma versión no ejecutan instaladores.
Puede aplicar eliminaciones de archivos confirmadas en el diff; protege rutas
con `custom`. No usa un borrado general de directorios. Tras un despliegue registrado,
`--from` puede omitirse para usar el commit guardado por módulo.

Revisá el plan antes de aplicar. Se firma localmente y queda vinculado a la
herramienta y al estado de la instalación: si cambian, generá otro plan.

Todos los comandos admiten `--state-dir DIR` y `--bootstrap FILE`. El bootstrap se
detecta en `/etc/issabelpbx.conf` o `/etc/asterisk/issabelpbx.conf`. Usá un único
directorio de estado permanente por PBX. `--help` muestra la sintaxis disponible.

### Validación

```sh
php tools/module-deploy/tests/run.php
```

La suite usa repositorios temporales y una PBX simulada. Incluye selección,
dependencias, parches, instaladores, conflictos, respaldos, recarga y recuperación.
No ejecuta migraciones reales ni una recarga real de Asterisk. Validá el procedimiento
en una PBX de ensayo con las versiones y permisos del destino. La herramienta no
reinicia PHP ni invalida el OPcache de un proceso web separado.

## English

Updates IssabelPBX modules from committed files in a Git clone, using the installed
routines for dependency checks, installation, migrations and reload. Production
keeps its normal directories.

### Quick start

Run on the PBX server from the clone containing the code you want to install:

```sh
cd /usr/src/issabelPBX-git
sudo ./tools/deploy-modules
```

The command detects the repository from the location of `tools`, compares its
modules with the installation and asks what to update. No installed commit or
manually prepared plan is required. The source is commit `HEAD`: uncommitted local
changes are not deployed. The tool does not download code or run `git pull`;
update and review the clone before using it.

You can also specify another clone:

```sh
sudo ./tools/deploy-modules update --repo /path/to/clone
```

Keep `deploy-modules` and the `module-deploy` directory together, outside the web
root. The clone must be separate from installed modules. Clones under `/root` are
supported: the native helper is temporarily staged for the Asterisk account,
without exposing private credentials or backups.

### Selection and application

For each installed module with differences, the tool shows its version, affected
files and whether its installer needs to run. Prompts use these Spanish response keys:

| Response | Action |
| --- | --- |
| `s` | Select the module. |
| Enter or `n` | Skip it. |
| `t` | Select the current and remaining installed modules. Previously skipped modules remain skipped. |
| `d` | Show affected files and ask again. |
| `q` | Cancel without applying. |

Absent modules are skipped and listed at the end. A module directory alone does
not mean it is installed in Issabel's database. Missing dependencies or dependencies
requiring an update are offered separately and require explicit approval, including
after choosing `t`. Disabled modules retain their disabled state.

Accepting a module authorizes replacing its differing files, including local
customizations, with the selected commit's content. Interactive mode preserves
files found only in production and skips `*custom*.conf` files. Destinations are
checked again before writing; changes since the accepted comparison stop deployment.

The final confirmation performs these operations:

1. Checks paths, permissions, dependencies, modified PHP syntax, space, database
   access and the Asterisk Manager Interface (AMI) connection.
2. Backs up and stages files before modifying destinations.
3. Updates files and runs required installers in dependency order. Downgrades
   are refused.
4. Regenerates and reloads once at the end of the batch, without restarting Asterisk.
5. Checks the AMI reload response, installed versions and states, connectivity and
   deployed files. Records the commit as well as the module version.

Interactive mode runs the installer for a missing module, a version change or
changed installation scripts. Same-version reinstalls first pass native dependency
checks. It also checks whether `framework` needs to complete migrations even when
its recorded module version already matches.

For `framework`, the tool compares actual web, `bin` and AGI destinations, stages
the package and runs its installer before dependent modules. Verification respects
existing file permissions retained by the native copy; content must match Git.
Initial templates in `amp_conf/astetc`, such as `manager.conf`, do not replace active
configuration. The tool does not run `install_amp` or install repository files
that do not belong to modules.

### Requirements and operation

- Linux, root and PHP CLI **7.4 or later**, with POSIX, SimpleXML and SQLite3 online
  backup support. Selected modules must also support that PHP version.
- Git, GNU `cp`, `tar` and `stat`, `runuser`, and `mysqldump` for MySQL/MariaDB.
  `tar` must support ACLs, extended attributes and SELinux; `restorecon` is also
  required with SELinux.
- An accessible MySQL/MariaDB or SQLite3 main database, working AMI, a non-root
  Asterisk account and readable SQLite AstDB (`astdb.sqlite3`).
- SQL privileges to back up tables, views, routines, events and triggers. The dump
  uses `--lock-tables` on the selected database, including MyISAM tables; it does
  not request a global `--lock-all-tables` lock.
- Space for original files, staged packages and configuration, database and AstDB
  backups. Interactive mode requires a terminal; use `ssh -t` over SSH.

Use a maintenance window without concurrent configuration edits or Module Admin
operations. `/var/lock/issabelpbx-deploy.lock` serializes this tool, but the web UI
and other services do not share its lock. Checks do not freeze every PBX writer.

If changes are already pending, interactive mode asks whether to include them in
the final reload. Declining cancels the operation. Unapproved pending changes
appearing after comparison stop deployment before writing. `plan`, `rollback` and
`recover` require no pending changes.

To apply pending configuration from the console:

```sh
sudo amportal a r
```

If the flag becomes set again, check processes or integrations generating
configuration after the reload. `retrieve_conf` marks changes pending; running it
alone does not finish applying them. Do not clear the flag to bypass deployment
verification.

### Backups, history and recovery

State is stored in `/var/lib/issabelpbx-deploy`, outside the web root and accessible
only to root. Keep its key, plans and history with that PBX. Backups are not
removed automatically.

```sh
sudo ./tools/deploy-modules history
```

Each apply attempt records its journal in `deployments/ID/record.json`. Depending
on the stage reached, the directory contains SQL or SQLite backups, AstDB, module
and configuration files, their metadata, staged files and native logs. Backups
are per store, not a single atomic snapshot across all PBX stores. Logs and diffs
may contain private information.

A `mysqldump` failure reports its error and saves up to 64 KiB in `mysqldump.stderr`,
with configured passwords redacted. Incomplete SQL and temporary credential files
are removed before stopping.

| Status | Action |
| --- | --- |
| `complete` | Deployment finished and passed its checks. |
| `aborted-before-write` | No files were deployed. Correct the cause and retry. |
| `auto-rolled-back` | Patch writes and configuration were restored and reloaded. Review the error before retrying. |
| `recovery-required` or another incomplete state | Review the journal and backups. Further deployments are blocked until resolved. |

To roll back a completed patch that **did not run installers**:

```sh
sudo ./tools/deploy-modules rollback --deployment ID
```

Rollback checks backups, files, configuration and history before restoring, and
refuses subsequent modifications. It restores files and configuration and reloads
once. It does not restore the database or delete unrelated files.

For a file verification failure **after a successful reload**:

```sh
sudo ./tools/deploy-modules recover --deployment ID
```

`recover` only supports that case. It verifies the signed plan, affected-file
backups, paths, history, deployed files and installation health. If everything
matches, it preserves the original journal and marks the attempt complete. It does
not copy files, repeat migrations or reload Asterisk.

After an installer starts, failures do not trigger automatic file-only rollback:
code and database may depend on each other. Review migrations and backups to repair
or explicitly restore. Restoring a database can discard changes made after the
backup. Incomplete states must be reconciled before deploying again.

### Advanced commands

**File comparison without executing Issabel:**

```sh
sudo ./tools/deploy-modules inspect \
  --repo /usr/src/issabelPBX-git --to HEAD --modules core,sipsettings \
  --modules-dir /var/www/html/admin/modules \
  --output /root/issabel-comparison
```

`inspect` produces `report.json` and `differences.diff` without loading the bootstrap,
querying SQL/AMI or reloading. It compares file versions, not SQL migrations. It
requires root, PHP CLI 7.4+, POSIX, SimpleXML and Git, but not backup tools or SQLite3.
It excludes `framework`, whose destinations are distributed. Output must be a new
directory outside the clone and web root. Production-only files appear as removals
for comparison, not as authorization to delete them. The per-file content limit
is 1 MiB; `--max-diff-bytes N` allows up to 16 MiB. A failed report retains an
`INCOMPLETE` marker and must not be treated as complete.

**Explicit plan based on a known installed commit:**

```sh
sudo ./tools/deploy-modules plan \
  --repo /usr/src/issabelPBX-git --from BASE_COMMIT --to TARGET_COMMIT \
  --modules core,sipsettings
sudo ./tools/deploy-modules apply --plan /path/to/plan.json
```

This mode requires affected installed files and module versions to match the base
commit, stopping on local customizations. It accepts only installed, enabled
modules, excludes `framework`, and requires a version increase for installation
script changes. Same-version patches do not run installers. It can apply tracked
file deletions from the diff and protects paths containing `custom`. It does not
perform blanket directory deletion. After a recorded deployment, `--from` may be
omitted to use the commit recorded per module.

Review the plan before applying. Plans are signed locally and bound to the tool
and installation state; generate another plan if these change.

All commands accept `--state-dir DIR` and `--bootstrap FILE`. Bootstrap detection
checks `/etc/issabelpbx.conf` and `/etc/asterisk/issabelpbx.conf`. Use a single
permanent state directory per PBX. `--help` shows available syntax.

### Validation

```sh
php tools/module-deploy/tests/run.php
```

The suite uses temporary repositories and a simulated PBX, covering selection,
dependencies, patches, installers, conflicts, backups, reload and recovery. It
does not execute real migrations or a real Asterisk reload. Validate the procedure
on a test PBX with the destination's versions and permissions. The tool does not
restart PHP or invalidate a separate web process's OPcache.
