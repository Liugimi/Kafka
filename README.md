# MusicLearn - Sistema de Aprendizaje Musical con Notificaciones en Tiempo Real

![MusicLearn Logo](MusicLearn.png)

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
