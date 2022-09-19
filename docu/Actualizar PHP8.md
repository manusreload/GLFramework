# Actualizar framework a la version PHP8

1º Eliminar el archivo `composer.lock` para que se pueda instalar las dependencias sin restricciones.
2º Instalar la última version del framework (https://github.com/manusreload/GLFramework/tags)

```bash
composer require gestionlan/framework "~1.2.8.x-dev"
```

3º Comprobar que los paquetes de nuestro proyecto están en la última versión.


