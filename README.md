# 🎯 GERO ACTIVIDADES - Sistema de Gestión de Actividades

Sistema web completo para la gestión de actividades, personal, contratos, divisiones y reportes con control de acceso basado en roles (RBAC).

---

## 📋 Características

✅ **Control de Acceso Basado en Roles (RBAC)**
- 5 roles predeterminados (Administrador, Gerente, Supervisor, Personal, Visualizador)
- 32 permisos granulares por módulo
- Asignación flexible de permisos a roles

✅ **Módulos Principales**
- 👥 **Gestión de Usuarios** - Crear, editar, eliminar usuarios con autenticación segura
- 🏢 **Gestión de Divisiones** - Organizar personal en divisiones con jefes asignados
- 👔 **Gestión de Personal** - Registros completos de empleados con asignación a divisiones y contratos
- 📜 **Gestión de Contratos** - Administración de contratos con número de pagos y alcances
- 🎯 **Gestión de Alcances** - Definir alcances dentro de cada contrato
- 📅 **Gestión de Actividades** - Crear actividades vinculadas a personal y alcances
- 🔑 **Gestión de Roles** - Definir roles y asignar permisos
- ⚙️ **Gestión de Permisos** - Control granular de permisos por módulo y acción
- 📊 **Tablero Kanban** - Gestión visual de tareas con columnas, tarjetas, listas, etiquetas y cronómetro

✅ **Dashboard Ejecutivo**
- Estadísticas por división
- Gráficos de actividades completadas, pendientes y en progreso
- Estadísticas por personal

✅ **Reportes PDF**
- Generación de reportes de actividades completadas
- Exportación de datos

✅ **Tablero Kanban** (Nuevo)
- 📋 Tableros múltiples con usuarios asignados
- 🏷️ Tarjetas organizadas en columnas editables
- 📝 Listas de tareas dentro de tarjetas con estado completado/pendiente
- ⏱️ Cronómetro integrado para registrar tiempo en tareas
- 🏷️ Etiquetas personalizables por tablero
- 📌 Prioridades configurables (alta, media, baja)
- 🔐 Permisos granulares por usuario y tablero
- 📅 Fechas de inicio y fin en tarjetas
- 👥 Asignación de usuarios responsables a tarjetas
- 📊 Checklist dentro de tarjetas
- 🔄 Drag & drop de tarjetas entre columnas
- 📋 Historial de cambios en tarjetas

✅ **Características Técnicas**
- Arquitectura MVC limpia
- Base de datos MySQL normalizada
- Contraseñas hasheadas con bcrypt
- Validación de formularios en lado cliente y servidor
- Responsive design con Bootstrap 5
- Iconos con Bootstrap Icons
- Funcionamiento offline (bibliotecas locales)
- Sortable.js para drag & drop en tablero

---

## 🚀 Instalación Rápida

### Requisitos

- **PHP 7.4+**
- **MySQL 5.7+** o **MariaDB 10.3+**
- **XAMPP**, **WAMP**, o servidor web similar
- **Navegador moderno** (Chrome, Firefox, Safari, Edge)

### Pasos de Instalación

#### 1️⃣ Clonar o descargar el proyecto
```bash
# Si usas git
git clone https://github.com/Gero72-2025/gero_actividades.git

# O descargar manualmente
# Coloca la carpeta en: C:\xampp\htdocs\gero_activities
```

#### 2️⃣ Configurar la base de datos (config/config.php)
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Tu usuario MySQL
define('DB_PASS', '');                // Tu contraseña MySQL
define('DB_NAME', 'gestor_actividades');
```

#### 3️⃣ Ejecutar instalación automática
```bash
# Opción A: Desde navegador
# Ve a: http://localhost/gero_activities/auto_setup.php

# Opción B: Desde línea de comandos
cd C:\xampp\htdocs\gero_activities
php auto_setup.php
```

#### 4️⃣ Verificar instalación (opcional)
```bash
# Desde navegador
# Ve a: http://localhost/gero_activities/verify_setup.php

# Desde línea de comandos
php verify_setup.php
```

#### 5️⃣ Acceder al sistema
```
URL: http://localhost/gero_activities/
Email: admin@admin.com
Password: Admin.62
```

---

## 📁 Estructura del Proyecto

```
gero_activities/
├── app/
│   ├── bootstrap.php          # Carga inicial
│   ├── helpers.php            # Funciones auxiliares
│   ├── controllers/           # Controladores MVC
│   │   ├── Actividades.php
│   │   ├── Personal.php
│   │   ├── Contratos.php
│   │   ├── Divisiones.php
│   │   ├── Roles.php
│   │   ├── Permisos.php
│   │   ├── Usuarios.php
│   │   ├── Tablero.php       # Gestión de tableros Kanban
│   │   └── Pages.php
│   ├── models/                # Modelos de datos
│   │   ├── ActividadModel.php
│   │   ├── PersonalModel.php
│   │   ├── ContratoModel.php
│   │   ├── TableroModel.php   # Modelo para tablero Kanban
│   │   └── ...
│   ├── libraries/             # Clases base
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   ├── Core.php
│   │   └── fpdf/              # Librería PDF
│   └── views/                 # Vistas (HTML)
│       ├── layouts/
│       ├── actividades/
│       ├── personal/
│       ├── contratos/
│       ├── tablero/           # Vistas del tablero Kanban
│       └── ...
├── config/
│   └── config.php             # Configuración
├── database_scripts/
│   ├── 09_tablero_actividades.sql # Tablas/permisos del tablero (usado por auto_setup)
│   └── 10_restaurar_admin.sql     # Script utilitario para restaurar permisos admin
├── public/
│   ├── index.php              # Punto de entrada
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   ├── img/
│   └── lib/                   # Librerías locales
│       ├── bootstrap/
│       ├── jquery/
│       ├── bootstrap-icons/
│       └── chartjs/
├── auto_setup.php             # 🔧 Instalación automática
├── verify_setup.php           # ✅ Verificación de instalación
├── SETUP_GUIDE.md             # 📖 Guía de instalación
└── README.md                  # Este archivo
```

---

## 🔐 Seguridad

### Medidas implementadas

✅ **Autenticación**
- Login con email y contraseña
- Sesiones PHP seguras
- Contraseñas hasheadas con bcrypt

✅ **Control de Acceso**
- Verificación de permisos en cada acción
- Roles basados en RBAC
- Validación en controladores

✅ **Protección de Datos**
- Sanitización de inputs
- Prepared statements (PDO)
- CSRF protection con sesiones

### Primeros pasos de seguridad

1. **Cambiar contraseña del admin** inmediatamente después de instalar
2. **Eliminar los archivos de setup** (auto_setup.php, verify_setup.php) después de instalar
3. **Usar HTTPS** en producción
4. **Configurar credenciales de BD** seguras

---

## 👤 Roles y Permisos

### Roles Predeterminados

| Rol               | Acceso                                        |
|-------------------|-----------------------------------------------|
| **Administrador** | Acceso completo a toda la plataforma          |
| **Gerente**       | Reportes, contratos, actividades, personal    |
| **Supervisor**    | Actividades y personal asignado a su división |
| **Personal**      | Solo sus propias actividades                  |
| **Visualizador**  | Solo lectura en reportes                      |

### Módulos y Permisos

Cada módulo tiene permisos granulares:
- `modulo.ver` - Ver listado
- `modulo.crear` - Crear registros
- `modulo.editar` - Editar registros
- `modulo.eliminar` - Eliminar registros
- `modulo.reporte` - Generar reportes (actividades)

### Permisos Granulares del Tablero

Además de los permisos globales, el tablero tiene permisos específicos **por usuario y por tablero**:

| Permiso | Descripción |
|---------|-------------|
| `tablero_ver` | Ver el tablero asignado |
| `tablero_crear` | Crear nuevos tableros |
| `tablero_editar` | Editar configuración del tablero |
| `tablero_eliminar` | Eliminar tableros |
| `tablero_asignar` | Asignar usuarios al tablero y gestionar permisos |
| `tarjeta_ver` | Ver tarjetas en el tablero |
| `tarjeta_crear` | Crear nuevas tarjetas |
| `tarjeta_editar` | Editar tarjetas |
| `tarjeta_eliminar` | Eliminar tarjetas |
| `tarjeta_asignar` | Asignar usuarios a tarjetas |
| `lista_crear` | Crear listas de tareas en tarjetas |
| `lista_editar` | Editar listas de tareas |
| `lista_eliminar` | Eliminar listas de tareas |
| `tarea_crear` | Crear tareas dentro de listas |
| `tarea_editar` | Editar tareas |
| `tarea_eliminar` | Eliminar tareas |
| `tarea_tiempo_editar` | Editar manualmente tiempo registrado en tareas |

**Nota:** Los usuarios también pueden marcar tareas/tarjetas como completadas si tienen acceso visual al tablero (`tablero_ver`).

---

## 🔧 Configuración

### config/config.php

```php
// Base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gestor_actividades');

// Rutas
define('APPROOT', dirname(dirname(__FILE__)) . '/app');
define('URLROOT', 'http://localhost/gero_activities');

// Sitio
define('SITENAME', 'Gero Actividades');
```

---

## 📊 Base de Datos

### Tablas Principales

- **usuario**       - Usuarios del sistema
- **roles**         - Roles disponibles
- **permisos**      - Permisos del sistema
- **role_permiso**  - Relación roles-permisos
- **usuario_role**  - Relación usuarios-roles
- **division**      - Divisiones organizacionales
- **personal**      - Registros de personal
- **contratos**     - Contratos
- **alcances**      - Alcances de contratos
- **actividades**   - Actividades del sistema

### Diagrama de relaciones

```
usuario → usuario_role → roles → role_permiso → permisos
  ↓
personal → division
  ↓
  ├→ contratos → alcances → actividades
  └→ actividades

tablero → tablero_usuario_permiso → usuario
  ↓
  ├→ tablero_columnas
  │
  ├→ tablero_tarjetas → usuario (responsable)
  │   ├→ tablero_tiempos
  │   ├→ tablero_tareas (listas)
  │   ├→ tablero_tarea_detalles (items tareas)
  │   ├→ tablero_etiquetas
  │   └→ tablero_tarjeta_etiqueta
  │
  ├→ tablero_prioridades
  │
  └→ tablero_historial_tarjeta
```

---

## 🚀 Uso

### Crear un Usuario Nuevo

1. Inicia sesión como Admin
2. Ve a **Gestión de Usuarios**
3. Haz clic en **Crear Nuevo Usuario**
4. Completa los datos
5. Asigna roles desde **Gestión de Roles**

### Crear una División

1. Ve a **Gestión de Divisiones**
2. Haz clic en **Crear Nueva División**
3. Ingresa nombre, siglas
4. Asigna un jefe (opcional)

### Crear Personal

1. Ve a **Gestión de Personal**
2. Haz clic en **Añadir Personal**
3. Vincula a un usuario existente
4. Asigna a una división (opcional)
5. Asigna un contrato (opcional)

### Crear Contrato

1. Ve a **Gestión de Contratos**
2. Haz clic en **Añadir Contrato**
3. Ingresa descripción, fechas, número de pagos
4. Crea alcances dentro del contrato

### Crear Actividades

1. Ve a **Gestión de Actividades**
2. Crea nuevas actividades o usa el calendario
3. Asigna a personal y alcances
4. Establece fechas y descripción

### Crear un Tablero

1. Ve a **Tablero**
2. Haz clic en **Crear Nuevo Tablero**
3. Ingresa nombre y descripción
4. Da permisos a usuarios específicos desde **Asignar Usuario al Tablero**
5. Personaliza columnas (estados): Pendiente, En Progreso, En Revisión, Completada
6. Crea tarjetas dentro de las columnas
7. Agrega tareas a las tarjetas
8. Asigna etiquetas y prioridades
9. Registra tiempo con el cronómetro integrado
10. Marca tareas como completadas conforme avanzan

---

## 📝 API Endpoints

El sistema utiliza rutas tipo MVC:

```
GET    /modulo/               - Listar registros
GET    /modulo/add            - Mostrar formulario crear
POST   /modulo/add            - Guardar nuevo registro
GET    /modulo/edit/ID        - Mostrar formulario editar
POST   /modulo/edit/ID        - Guardar cambios
POST   /modulo/delete/ID      - Eliminar registro
GET    /actividades/reporte   - Generar PDF
```

Ejemplos:
- `GET /usuarios/` - Listar usuarios
- `POST /usuarios/add` - Crear usuario
- `POST /usuarios/edit/5` - Editar usuario ID 5
- `POST /usuarios/delete/5` - Eliminar usuario ID 5

### Endpoints del Tablero

```
GET    /tablero/                           - Listar tableros asignados
POST   /tablero/add                        - Crear nuevo tablero
GET    /tablero/index/ID_TABLERO           - Ver tablero (vista Kanban)
POST   /tablero/add_usuario                - Asignar usuario a tablero
POST   /tablero/get_usuario_permiso_tablero - Obtener permisos del usuario
POST   /tablero/toggle_tarjeta_completado  - Marcar tarjeta como finalizada
POST   /tablero/toggle_tarjeta_tarea_detalle - Marcar tarea como completada
POST   /tablero/start_timer                - Iniciar cronómetro en tarjeta
POST   /tablero/stop_timer                 - Detener cronómetro
POST   /tablero/update_tarea_detalle_tiempo_manual - Editar tiempo manualmente
POST   /tablero/add_tarjeta_tarea          - Crear tarea en tarjeta
POST   /tablero/delete_tarjeta_tarea       - Eliminar tarea
```

---

## 🐛 Solución de Problemas

### Error: "No se puede conectar a MySQL"
- Verifica que XAMPP está ejecutándose
- Verifica las credenciales en config/config.php
- Asegúrate que el servicio MySQL esté activo

### Error: "Tabla no existe"
- Ejecuta nuevamente `auto_setup.php`
- Verifica que la BD fue creada correctamente

### Error: "Permiso denegado"
- Verifica que el usuario tiene el rol correcto
- Verifica que el rol tiene el permiso asignado
- Inicia sesión nuevamente

### Las sesiones no persisten
- Verifica que PHP session está habilitado
- Verifica permisos de carpeta `tmp` de PHP

---

## 📚 Documentación Adicional

- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Guía detallada de instalación
- [database_scripts/](database_scripts/) - Scripts SQL
- [Comentarios en código](app/models/) - Documentación en línea

---

## 🔄 Actualizaciones y Mantenimiento

### Respaldar BD
```bash
# Windows
mysqldump -u root -p gestor_actividades > backup.sql

# Linux/Mac
mysqldump -u root -ppassword gestor_actividades > backup.sql
```

### Restaurar BD
```bash
mysql -u root -p gestor_actividades < backup.sql
```

---

## 📄 Licencia

Este proyecto es de código abierto.

---

## 👨‍💻 Contribuciones

Las contribuciones son bienvenidas. Para cambios importantes:
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 💬 Soporte

Para reportar problemas:
1. Verifica [SETUP_GUIDE.md](SETUP_GUIDE.md)
2. Ejecuta [verify_setup.php](verify_setup.php) para diagnosticar
3. Revisa los logs de PHP/MySQL

---

## 📞 Contacto

Para preguntas o sugerencias, contacta al equipo de desarrollo.
gero722025@gmail.com
Dev.: Erick Quiñonez.

---

**Última actualización:** Marzo 2026  
**Versión:** 2.0.0  
**Estado:** ✅ Production Ready  
**Nuevas características en v2.0:** Tablero Kanban con gestión de tareas, cronómetro, etiquetas, prioridades y permisos granulares por usuario y tablero
