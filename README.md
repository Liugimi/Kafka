# MusicLearn - Sistema de Aprendizaje Musical con Notificaciones en Tiempo Real


## Descripción

MusicLearn es una plataforma educativa innovadora diseñada para el aprendizaje musical interactivo. Este proyecto implementa una aplicación web con un sistema de notificaciones en tiempo real mediante el patrón arquitectónico Publicador-Suscriptor y Apache Kafka, permitiendo a los usuarios recibir actualizaciones instantáneas sobre nuevas lecciones, invitaciones a ensambles y anuncios del sistema.

## Características

* Interfaz de usuario responsiva y moderna
* Sistema de notificaciones en tiempo real con Apache Kafka
* Secciones para lecciones musicales y ensambles colaborativos
* Panel de contacto y soporte técnico
* Arquitectura escalable basada en el patrón Publicador-Suscriptor

## Tecnologías Utilizadas

* **Frontend**:
  - HTML5
  - CSS3 (con gradientes, transiciones y diseño responsivo)
  - JavaScript (ES6+)

* **Backend**:
  - PHP 7.4+
  - Apache Kafka
  - Extensión RdKafka para PHP

## Requisitos del Sistema

* Servidor web (Apache/Nginx)
* PHP 7.4 o superior
* Extensión RdKafka para PHP
* Instancia de Apache Kafka (local o remota)
* Navegador moderno (Chrome, Firefox, Safari, Edge)

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/musiclearn.git
cd musiclearn
```

### 2. Configurar el servidor web

Configura tu servidor web (Apache/Nginx) para servir el proyecto desde el directorio raíz.

### 3. Instalar y configurar Kafka

#### a. Descargar e instalar Apache Kafka

```bash
# Descargar Kafka
wget https://downloads.apache.org/kafka/3.5.0/kafka_2.13-3.5.0.tgz
tar -xzf kafka_2.13-3.5.0.tgz
cd kafka_2.13-3.5.0

# Iniciar ZooKeeper
bin/zookeeper-server-start.sh config/zookeeper.properties

# En otra terminal, iniciar el servidor Kafka
bin/kafka-server-start.sh config/server.properties
```

#### b. Crear los tópicos necesarios

```bash
bin/kafka-topics.sh --create --bootstrap-server localhost:9092 --replication-factor 1 --partitions 3 --topic musiclearn.lecciones
bin/kafka-topics.sh --create --bootstrap-server localhost:9092 --replication-factor 1 --partitions 3 --topic musiclearn.ensambles
bin/kafka-topics.sh --create --bootstrap-server localhost:9092 --replication-factor 1 --partitions 3 --topic musiclearn.usuarios
bin/kafka-topics.sh --create --bootstrap-server localhost:9092 --replication-factor 1 --partitions 3 --topic musiclearn.analytics```

### 4. Instalar la extensión RdKafka para PHP

bash
# En sistemas basados en Debian/Ubuntu
sudo apt-get install librdkafka-dev
sudo pecl install rdkafka

# Añadir a php.ini
echo "extension=rdkafka.so" | sudo tee -a /etc/php/7.4/cli/php.ini
echo "extension=rdkafka.so" | sudo tee -a /etc/php/7.4/apache2/php.ini


### 5. Configurar la conexión Kafka

Edita el archivo kafka_integration.php para configurar la conexión con tu broker Kafka:

php
// Configuración de Kafka
$kafkaConfig = [
    'brokers' => 'localhost:9092', // Cambia esto según tu configuración
    'topics' => [
        'musiclearn.lecciones',
        'musiclearn.ensambles',
        'musiclearn.usuarios',
        'musiclearn.analytics'
    ]
];


## Estructura del Proyecto


musiclearn/
├── index.html            # Página principal
├── styles.css            # Estilos CSS
├── script.js             # Lógica de cliente con integración Kafka
├── kafka_integration.php # Integración backend con Kafka
├── lecciones.php         # Página de lecciones (implementar)
├── ensamble.php          # Página de ensambles (implementar)
├── perfil.php            # Página de perfil (implementar)
├── cerrarsesion.php      # Script de cierre de sesión
├── MusicLearn.png        # Logo de la aplicación
└── README.md             # Documentación


## Uso del Sistema

### Ejecución del Cliente

1. Accede a la aplicación mediante tu navegador web
2. Haz clic en "Suscribirse a notificaciones" para activar las notificaciones en tiempo real
3. Explora las secciones "Lecciones" y "Ensamble" para acceder al contenido educativo

### Ejecución del Consumidor Kafka

Para iniciar el procesamiento de mensajes en segundo plano:

bash
php -f kafka_consumer.php


## Implementación del Patrón Publicador-Suscriptor

### Componentes clave:

1. *Publicadores (Producers)*:
   - Sistemas que generan eventos como nuevas lecciones o invitaciones a ensambles
   - Implementados en kafka_integration.php con funciones como sendKafkaMessage()

2. *Broker (Kafka)*:
   - Intermediario central que gestiona tópicos y mensajes
   - Garantiza la entrega y persistencia de los eventos

3. *Suscriptores (Consumers)*:
   - Cliente web que recibe y muestra notificaciones en tiempo real
   - Implementado en script.js con la función initKafkaNotifications()

4. *Tópicos (Topics)*:
   - Canales temáticos donde se publican mensajes específicos
   - Incluye musiclearn.lecciones, musiclearn.ensambles, musiclearn.usuarios y musiclearn.analytics

## Personalización

### Colores y Estilos

Los colores principales del tema se pueden modificar en el archivo styles.css:

css
:root {
    --color-primary: #5D43A4;
    --color-secondary: #393DC5;
    --color-dark: #171945;
    --color-black: #000;
    --color-white: #fff;
}


### Configuración de Notificaciones

Para ajustar la frecuencia y comportamiento de las notificaciones, edita estos parámetros en script.js:

javascript
// Simular recepción de mensajes de Kafka periódicamente
setInterval(() => {
    // Tu código aquí
}, 10000); // Cambiar este valor para modificar la frecuencia (en milisegundos)


## Posibles Problemas y Soluciones

| Problema | Solución |
|----------|----------|
| No se muestran notificaciones | Verifica que Kafka esté en ejecución y que los tópicos estén creados correctamente |
| Error de conexión RdKafka | Asegúrate de que la extensión esté instalada y configurada en php.ini |
| El menú desplegable no funciona | Verifica que estés incluyendo Font Awesome para los iconos |
| La estilización no se aplica | Asegúrate de que los archivos CSS estén correctamente vinculados en el HTML |

## Contribuir

Las contribuciones son bienvenidas. Para contribuir:

1. Haz un Fork del repositorio
2. Crea una rama para tu funcionalidad (git checkout -b feature/nueva-funcionalidad)
3. Realiza tus cambios y haz commit (git commit -m 'Añadir nueva funcionalidad')
4. Sube tus cambios (git push origin feature/nueva-funcionalidad)
5. Abre un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## Contacto y Soporte

Para soporte técnico, contacta a cualquiera de los desarrolladores:

- José Antonio Ávila Tolosa - javilatolosa@ucundinamarca.edu.co - 3114047030
- Luis Miguel Ortegón Vargas - lmiguelortegon@ucundinamarca.edu.co - 3213823224
- Juan Pablo Ramírez Lozano - jpabloramirez@ucundinamarca.edu.co - 3192752702

---

&copy; MusicLearn 2025 - Desarrollado como proyecto educativo para la Universidad de Cundinamarca
