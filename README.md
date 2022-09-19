# GLFramework
## GestionLan PHP Framework


- [GLFramework](#glframework)
  - [GestionLan PHP Framework](#gestionlan-php-framework)
    - [Tecnología utilizada en el desarrollo.](#tecnología-utilizada-en-el-desarrollo)
    - [Creación de una aplicación.](#creación-de-una-aplicación)
    - [Archivo "config.yml"](#archivo-configyml)
      - [Listado de los módulos internos](#listado-de-los-módulos-internos)
    - [Controlador](#controlador)
    - [Vista](#vista)
    - [Modelo](#modelo)
      - [Ejemplo de Modelo:](#ejemplo-de-modelo)

En este documento se explica el funcionamiento y como utilizar este conjunto de funciones para realizar software.


### Tecnología utilizada en el desarrollo.

Para que este framework funcione es necesario que se ejecute bajo un entorno PHP. El framework tiene varias rutas de entrada, y en función de lo que se desee hacer se utilizan unas u otras. Bajo PHP se han utilizado varias librerías gestionadas por "composer". Entre otras destacan:

- Twig: Se utiliza en el renderizado de las vistas.
- Yaml: Para los archivos de configuración.
- AltoRouter: Para el enrutamiento de las peticiones.

El framework también incluye un sistema de módulos, que junto con los eventos permiten redefinir partes de lo que ya se ha creado. Estos módulos tienen que ser habilitados por la propia aplicación en su archivo de configuración.

### Creación de una aplicación.

Para comenzar a crear una aplicación es necesario tener instalado "composer" (https://getcomposer.org/). Nos vamos al repositorio del código fuente (https://gitlab.com/gestionlan/framework) y nos descargamos la carpeta "example"


### Archivo "config.yml"

Manual del formato Yaml: http://symfony.com/doc/current/components/yaml/yaml_format.html
En este archivo se define la configuración de la aplicación.


```yaml
app:
  basepath: [Ruta base de la aplicación:/beta,]
  index: [Nombre del archivo de inicio:index,home]
  name: [Nombre de la aplicación:Demo]
  banner: [Banner de la aplicación:/img/banner.png]
  favicon: [Icono de navegador:/images/logo.jpg]

  controllers: [Directorio de los controladores:pages,controller]
  model: [Directorio de los modelos:model]
  views: [Directorio de las vistas:views]
  upload: [Directorio de subida de archivos:uploads]


  routes: [Rutas especiales a controladores]
    - [NombreClaseControlador]: [[URL de destino], [METODO:GET|POST,GET,POST]]
    - MyNameSpace\home: ["/home/example/[i:id]", GET]
    - home: ["/home/example/[i:id]", GET]
    - sub_home: ["/home/sub/example/[i:id]"]

database: [Configuración de la base de datos]
  hostname: 127.0.0.1
  username: root
  password:
  database: dbdemo
modules: [Módulos activos para la aplicación]
  internal: [Modulos internos de framework]
    - admin
    - group_permissions
  modules:  [Modulos de la aplicación, se encuentran en la carpeta modules]

```


#### Listado de los módulos internos

- admin: Conjunto de utilidades para la administración interna de la aplicación.
- group_permissions: Añade la posibilidad de asignar páginas por grupos de usuarios


### Controlador

Para definir un controlador es necesario crear un archivo que siga un cierto patrón. Crearemos un archivo sobre una de las carpetas que hemos definido como controlador en el archivo de configuración. En este archivo vamos a definir una clase con el siguiente nombre: `[< nombre directorio >_]< nombre del archivo >` y extenderá a la clase Controller o a alguna de sus descendientes como puede ser AuthController. Podemos situarla con un espacio de nombres, el framework se encarga de detectar estos matices.

### Vista

La vista se programa con plantillas Twig, y existen algunas variables globales a las cuales se pueden acceder en las plantillas.

- `this` Es el controlador que se esta ejecutando. Se pueden acceder a todas las propiedades públicas con `{{ this.public_var }}`
- `config` Es un array con la configuración del framework, se puede acceder como: `{{ config.app.name }}`
- `_GET` Array con las varibles de petición $_GET
- `_POST` Array con las varibles de petición $_POST
- `_REQUEST` Array con las varibles de petición $_REQUEST
- `render` Objeto que contiene el motor de renderizado para las vistas Twig.
- `manager` Objeto de la clase ModuleManager. Ser puede utilizar para ver si hay módulos activos: `{% if manager.exists("admin") %}`


### Modelo

Aquí se define como se tratan los datos de la base de datos. Las columnas se definen en una lista, en la que se indica el índice y los campos de la tabla. También se define el nombre de la tabla.

#### Ejemplo de Modelo:

```php
class User extends Model
{
    var $id;
    var $user_name;
    var $password;
    var $privilegios;
    var $nombre;
    var $email;
    var $admin;
    protected $table_name = "user";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'user_name' => "varchar(20)",
            'password' => "varchar(200)",
            'privilegios' => "text",
            'admin' => "int(11)",
            'nombre' => "text",
            'email' => "text",
        )
    );
    public function getByUserPassword($user, $password)
    {
        return $this->db->select_first("SELECT * FROM {$this->table_name} WHERE user_name = '$user' AND password = '$password'");
    }
    public function encrypt($pass)
    {
        return md5($pass);
    }
    public function getPages()
    {
        $pages = new Page();
        $userPages = new UserPage();
        $sql = "SELECT * FROM " . $userPages->getTableName() . " as up
        LEFT JOIN {$pages->getTableName()} as p ON up.id_page = p.id
        WHERE up.id_user = " . $this->id;
        return $this->db->select($sql);
    }
}

```


Para comprobar los cambios en las tablas, acceda a http://example.com/install.php Allí se indican los cambio que se van a realizar en la base de datos. Pulse en el enlace de abajo para realizar estos cambios en la base de datos.