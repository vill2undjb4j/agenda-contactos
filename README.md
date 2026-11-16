
# MI AGENDA DE CONTACTOS

## 1. Descripción del proyecto

Mi Agenda de Contactos es una aplicación web desarrollada
para registrar, consultar, buscar, editar y eliminar contactos.

El sistema permite crear cuentas de usuario e iniciar sesión
para administrar los contactos personales de forma organizada.

Cada contacto puede contener:
- Nombre completo
- Cédula
- Teléfono
- Correo electrónico
- Ciudad

## 2. Tecnologías utilizadas

- HTML5: estructura de la aplicación.
- CSS3: diseño y presentación visual.
- PHP: programación del sistema.
- MySQL/MariaDB: base de datos.
- PDO: conexión a la base de datos.
- XAMPP: servidor local Apache y MySQL.
- Visual Studio Code: editor de código.

## 3. Requisitos para su ejecución

- Tener instalado XAMPP.
- Contar con PHP y MySQL/MariaDB.
- Tener habilitadas las extensiones PDO MySQL y mbstring.
- Utilizar un navegador web.
- Tener disponibles Apache y MySQL.

## 4. Pasos para instalar la aplicación

1. Instalar XAMPP.
2. Copiar la carpeta agenda_contactos dentro de htdocs.
3. Iniciar Apache y MySQL desde XAMPP.
4. Abrir phpMyAdmin desde el navegador.
5. Crear una base de datos llamada agenda_contactos.
6. Importar el archivo agenda_contactos.sql.
7. Revisar la configuración de conexion.php.
8. Configurar el puerto MySQL correspondiente.
   En el entorno de desarrollo se utilizó el puerto 3307.
9. Abrir la siguiente dirección:
   http://localhost/agenda_contactos/registro.php
10. Registrar un usuario nuevo.
11. Iniciar sesión desde login.php.
12. Comenzar a administrar los contactos.

## 5. Funcionalidades del sistema

- Registro de usuarios.
- Inicio de sesión.
- Cierre de sesión.
- Registro de contactos.
- Búsqueda por nombre, teléfono y cédula.
- Edición de contactos.
- Eliminación de contactos.
- Validación de formularios.
- Administración individual de contactos por usuario.

## 6. Estructura del proyecto

agenda_contactos/
- conexion.php
- registro.php
- login.php
- index.php
- editar.php
- eliminar.php
- logout.php
- estilos.css
- agenda_contactos.sql
- README.md

## 7. Seguridad

El sistema utiliza sesiones PHP para controlar el acceso,
consultas preparadas mediante PDO y contraseñas almacenadas
mediante funciones de hash.

## 8. Tipo de proyecto

Proyecto académico de desarrollo web con PHP y MySQL.
