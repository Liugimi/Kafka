<?php
/**
 * Archivo: kafka_integration.php
 * Descripción: Integración de Apache Kafka con MusicLearn para notificaciones en tiempo real
 */

// Configuración de Kafka
$kafkaConfig = [
    'brokers' => 'localhost:9092', // En producción sería la dirección real del broker
    'topics' => [
        'musiclearn.lecciones',
        'musiclearn.ensambles',
        'musiclearn.usuarios',
        'musiclearn.analytics'
    ]
];

/**
 * Función para enviar un mensaje a un topic de Kafka
 * 
 * @param string $topic Nombre del topic
 * @param array $data Datos a enviar
 * @return bool Éxito o fracaso
 */
function sendKafkaMessage($topic, $data) {
    global $kafkaConfig;
    
    try {
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', $kafkaConfig['brokers']);
        
        $producer = new RdKafka\Producer($conf);
        $topic = $producer->newTopic($topic);
        
        // Convertir datos a JSON para enviar
        $messagePayload = json_encode([
            'data' => $data,
            'timestamp' => time(),
            'source' => 'musiclearn_web'
        ]);
        
        // Enviar mensaje (RD_KAFKA_PARTITION_UA hace que Kafka distribuya automáticamente)
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $messagePayload);
        $producer->poll(0);
        
        // Asegurar que el mensaje se envía antes de continuar
        for ($i = 0; $i < 3; $i++) {
            $result = $producer->flush(10000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log('Error en Kafka Producer: ' . $e->getMessage());
        return false;
    }
}

/**
 * Registrar evento de usuario (inicios de sesión, actividades, etc.)
 * 
 * @param string $userId ID del usuario
 * @param string $eventType Tipo de evento
 * @param array $eventData Datos adicionales del evento
 */
function logUserEvent($userId, $eventType, $eventData = []) {
    $messageData = [
        'userId' => $userId,
        'eventType' => $eventType,
        'eventData' => $eventData,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'userAgent' => $_SERVER['HTTP_USER_AGENT']
    ];
    
    sendKafkaMessage('musiclearn.analytics', $messageData);
}

/**
 * Enviar notificación a un usuario específico
 * 
 * @param string $userId ID del usuario destinatario
 * @param string $title Título de la notificación
 * @param string $message Mensaje de la notificación
 * @param string $type Tipo de notificación (info, warning, error)
 */
function sendUserNotification($userId, $title, $message, $type = 'info') {
    $notificationData = [
        'recipientId' => $userId,
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'requiresAction' => false,
        'link' => null
    ];
    
    sendKafkaMessage('musiclearn.usuarios', $notificationData);
}

/**
 * Enviar notificación sobre una nueva lección disponible
 * 
 * @param int $lessonId ID de la lección
 * @param string $lessonTitle Título de la lección
 * @param array $targetUserIds IDs de usuarios a notificar (opcional)
 */
function notifyNewLesson($lessonId, $lessonTitle, $targetUserIds = null) {
    $lessonData = [
        'eventType' => 'newLesson',
        'lessonId' => $lessonId,
        'lessonTitle' => $lessonTitle,
        'targetUsers' => $targetUserIds
    ];
    
    sendKafkaMessage('musiclearn.lecciones', $lessonData);
}

/**
 * Enviar invitación a un ensamble
 * 
 * @param int $ensembleId ID del ensamble
 * @param string $ensembleTitle Título del ensamble
 * @param array $invitedUserIds IDs de usuarios invitados
 * @param string $message Mensaje de invitación
 */
function sendEnsembleInvitation($ensembleId, $ensembleTitle, $invitedUserIds, $message) {
    $invitationData = [
        'eventType' => 'invitation',
        'ensembleId' => $ensembleId,
        'ensembleTitle' => $ensembleTitle,
        'invitedUsers' => $invitedUserIds,
        'message' => $message,
        'expiresAt' => time() + (7 * 24 * 60 * 60) // 7 días
    ];
    
    sendKafkaMessage('musiclearn.ensambles', $invitationData);
    
    // También enviamos notificaciones individuales a cada usuario
    foreach ($invitedUserIds as $userId) {
        sendUserNotification(
            $userId,
            "Invitación a ensamble: $ensembleTitle",
            $message,
            'invitation'
        );
    }
}

/**
 * Consumidor de Kafka para procesar notificaciones en tiempo real
 * Este método se ejecutaría en un proceso separado o worker
 */
function startKafkaConsumer() {
    global $kafkaConfig;
    
    $conf = new RdKafka\Conf();
    $conf->set('metadata.broker.list', $kafkaConfig['brokers']);
    $conf->set('group.id', 'musiclearn_notification_group');
    $conf->set('auto.offset.reset', 'latest');
    
    $consumer = new RdKafka\KafkaConsumer($conf);
    $consumer->subscribe($kafkaConfig['topics']);
    
    echo "Iniciando consumidor Kafka para notificaciones en tiempo real...\n";
    
    while (true) {
        $message = $consumer->consume(10000);
        
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                processKafkaMessage($message);
                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                // Fin de partición, no hay más mensajes por ahora
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                // Timeout, esperar más mensajes
                break;
            default:
                echo "Error en consumidor Kafka: " . $message->errstr() . "\n";
                break;
        }
    }
}

/**
 * Procesar un mensaje recibido de Kafka
 * 
 * @param RdKafka\Message $message Mensaje de Kafka
 */
function processKafkaMessage($message) {
    $data = json_decode($message->payload, true);
    
    switch ($message->topic_name) {
        case 'musiclearn.lecciones':
            processLessonEvent($data);
            break;
        case 'musiclearn.ensambles':
            processEnsembleEvent($data);
            break;
        case 'musiclearn.usuarios':
            processUserNotification($data);
            break;
        case 'musiclearn.analytics':
            // Los eventos de analytics normalmente se almacenan para análisis posterior
            storeAnalyticsEvent($data);
            break;
    }
}

/**
 * Ejemplo de uso:
 * 
 * // Registrar actividad de usuario
 * logUserEvent('user123', 'login', ['browser' => 'Chrome', 'device' => 'desktop']);
 * 
 * // Notificar nueva lección
 * notifyNewLesson(42, 'Teoría musical avanzada');
 * 
 * // Invitar usuarios a un ensamble
 * sendEnsembleInvitation(15, 'Jazz Fusion', ['user123', 'user456'], 
 *     'Te invito a unirte a nuestro ensamble de Jazz Fusion el próximo sábado');
 */