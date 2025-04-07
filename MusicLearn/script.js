// Funcionalidad del menú toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const body = document.body;

    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });

    // Cierra el menú cuando se hace clic fuera de él
    document.addEventListener('click', function(event) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });

    // Inicializar el sistema de notificaciones Kafka
    initKafkaNotifications();
});

// Sistema de notificaciones en tiempo real con Kafka
function initKafkaNotifications() {
    const notificationContainer = document.getElementById('notification-container');
    const subscribeButton = document.getElementById('subscribe-button');
    let isSubscribed = false;

    // Simular conexión a Kafka
    const kafkaConnect = () => {
        console.log('Conectando al broker Kafka...');
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({status: 'connected'});
            }, 1000);
        });
    };

    // Simular la suscripción a un tema de Kafka
    const subscribeToTopic = (topic) => {
        console.log(`Suscrito al tema: ${topic}`);
        displayNotification('Sistema', `Te has suscrito exitosamente a notificaciones de ${topic}`);
        
        // Simular recepción de mensajes de Kafka periódicamente
        setInterval(() => {
            const eventTypes = ['nuevaLeccion', 'invitacionEnsamble', 'recordatorio', 'actualizacion'];
            const randomEvent = eventTypes[Math.floor(Math.random() * eventTypes.length)];
            
            // Generar una notificación basada en el tipo de evento
            let message = '';
            switch(randomEvent) {
                case 'nuevaLeccion':
                    message = 'Nueva lección disponible: ' + ['Teoría musical básica', 'Escalas mayores', 'Acordes en piano', 'Técnicas de guitarra'][Math.floor(Math.random() * 4)];
                    break;
                case 'invitacionEnsamble':
                    message = 'Has sido invitado a unirte al ensamble: ' + ['Jazz Fusion', 'Rock Clásico', 'Pop Contemporáneo', 'Música Latina'][Math.floor(Math.random() * 4)];
                    break;
                case 'recordatorio':
                    message = 'Recordatorio: Tienes una sesión programada en ' + Math.floor(Math.random() * 3 + 1) + ' horas';
                    break;
                case 'actualizacion':
                    message = 'MusicLearn se ha actualizado con nuevas funciones';
                    break;
            }
            
            displayNotification(randomEvent, message);
        }, 10000); // Cada 10 segundos
    };

    // Mostrar una notificación en la UI
    const displayNotification = (type, message) => {
        // Eliminar mensaje de "no hay notificaciones" si existe
        const noNotifications = document.querySelector('.no-notifications');
        if (noNotifications) {
            noNotifications.remove();
        }

        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = 'notification';
        
        const title = document.createElement('div');
        title.className = 'notification-title';
        title.textContent = type;
        
        const content = document.createElement('div');
        content.className = 'notification-content';
        content.textContent = message;
        
        const time = document.createElement('div');
        time.className = 'notification-time';
        time.textContent = new Date().toLocaleTimeString();
        
        notification.appendChild(title);
        notification.appendChild(content);
        notification.appendChild(time);
        
        notificationContainer.prepend(notification);
        
        // Mantener solo las últimas 5 notificaciones
        const notifications = document.querySelectorAll('.notification');
        if (notifications.length > 5) {
            notificationContainer.removeChild(notifications[notifications.length - 1]);
        }
    };

    // Evento para el botón de suscripción
    subscribeButton.addEventListener('click', async function() {
        if (!isSubscribed) {
            subscribeButton.textContent = 'Conectando...';
            subscribeButton.disabled = true;
            
            try {
                // Conectar a Kafka
                await kafkaConnect();
                
                // Suscribirse a varios temas
                const topics = ['musiclearn.lecciones', 'musiclearn.ensambles', 'musiclearn.usuarios'];
                topics.forEach(topic => subscribeToTopic(topic));
                
                // Actualizar estado y UI
                isSubscribed = true;
                subscribeButton.textContent = 'Suscrito a notificaciones';
                subscribeButton.classList.add('subscribed');
            } catch (error) {
                console.error('Error al conectar con Kafka:', error);
                displayNotification('Error', 'No se pudo conectar al sistema de notificaciones');
                subscribeButton.textContent = 'Reintentar suscripción';
                subscribeButton.disabled = false;
            }
        } else {
            // Si ya está suscrito, mostrar mensaje
            displayNotification('Sistema', 'Ya estás suscrito a las notificaciones');
        }
    });

    // Simular algunas notificaciones iniciales para demostración
    setTimeout(() => {
        displayNotification('Bienvenida', '¡Bienvenido a MusicLearn! Descubre todas nuestras lecciones disponibles.');
    }, 3000);
}

// Función para manejar las peticiones a la API de Kafka
async function kafkaProducer(topic, message) {
    // En una implementación real, esta función enviaría datos al backend
    console.log(`Enviando mensaje "${message}" al topic "${topic}"`);
    
    // Simular una petición HTTP a un backend que se comunica con Kafka
    try {
        // En una implementación real, esto sería una llamada fetch a un endpoint
        await new Promise(resolve => setTimeout(resolve, 500));
        return { success: true, topic, timestamp: new Date().toISOString() };
    } catch (error) {
        console.error('Error al enviar mensaje a Kafka:', error);
        return { success: false, error: 'No se pudo enviar el mensaje' };
    }
}

// Funcionalidad para interactuar con las secciones principales
function navigateTo(section) {
    console.log(`Navegando a: ${section}`);
    // En una implementación real, esto podría navegar a otra página
    // o cargar el contenido de forma dinámica
    
    // Registrar actividad del usuario en Kafka para análisis
    kafkaProducer('musiclearn.analytics', JSON.stringify({
        eventType: 'navigation',
        section: section,
        timestamp: new Date().toISOString(),
        userId: document.getElementById('nombre-usuario').textContent.trim()
    }));
}