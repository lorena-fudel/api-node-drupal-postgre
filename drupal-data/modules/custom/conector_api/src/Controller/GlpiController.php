<?php

namespace Drupal\conector_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GlpiController extends ControllerBase {

  protected $httpClient;

  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('http_client'));
  }

  public function buscarUsuarioPorEmail($email) {
    $base_url   = getenv('GLPI_BASE_URL');
    $app_token  = getenv('GLPI_APP_TOKEN');
    $user_token = getenv('GLPI_USER_TOKEN');

    try {
      // 1. Iniciar Sesión
      $res_session = $this->httpClient->get($base_url . '/initSession', [
        'headers' => [
          'App-Token'     => $app_token,
          'Authorization' => 'user_token ' . $user_token,
        ],
      ]);
      $session_token = json_decode($res_session->getBody())->session_token;

      // 2. BUSCAR POR EMAIL (Campo 5 según tu Postman)
      $query = [
        'criteria[0][field]'      => 5, // 5 es el ID del campo Email
        'criteria[0][searchtype]' => 'contains',
        'criteria[0][value]'      => $email,
      ];

      $res_search = $this->httpClient->get($base_url . '/search/User', [
        'headers' => [
          'App-Token'     => $app_token,
          'Session-Token' => $session_token,
        ],
        'query' => $query,
      ]);

      $search_results = json_decode($res_search->getBody(), TRUE);

      // 3. Cerrar Sesión inmediatamente
      $this->httpClient->get($base_url . '/killSession', [
        'headers' => ['App-Token' => $app_token, 'Session-Token' => $session_token],
      ]);

      // 4. Procesar resultados
      if (empty($search_results['data'])) {
        return ['#markup' => "<h3>No se encontró al usuario con email: $email</h3>"];
      }

      $user = $search_results['data'][0];

      return [
        '#type' => 'container',
        '#attributes' => ['class' => ['glpi-user-info']],
        'title' => ['#markup' => "<h2>Datos de GLPI para: $email</h2>"],
        'list' => [
          '#theme' => 'item_list',
          '#items' => [
            "Nombre de usuario (Login): " . $user[1],
            "Nombre Completo: " . $user[34],
            "Correo electrónico: " . $user[5],
            "ID Interno GLPI: " . $user[2],
          ],
        ],
      ];

    } catch (GuzzleException $e) {
      return ['#markup' => "Error de conexión: " . $e->getMessage()];
    }
  }

  /**
 * Obtiene todas las tareas asignadas a un ID de usuario concreto.
 */
  /**
 * Obtiene todas las tareas asignadas a un ID de usuario concreto.
 */
    public function mostrarTareasUsuario($id_usuario) {
    $base_url   = getenv('GLPI_BASE_URL');
    $app_token  = getenv('GLPI_APP_TOKEN');
    $user_token = getenv('GLPI_USER_TOKEN');

    try {
      // 1. Iniciar Sesión
      $res_session = $this->httpClient->get($base_url . '/initSession', [
        'headers' => [
          'App-Token'     => $app_token,
          'Authorization' => 'user_token ' . $user_token,
        ],
      ]);
      $session_token = json_decode($res_session->getBody())->session_token;

      // 2. BUSCAR TAREAS - Usamos el campo 5 (Técnico) según tu JSON
      $query = [
        'criteria[0][field]'      => 5, // <--- TU NÚMERO REAL PARA TÉCNICO
        'criteria[0][searchtype]' => 'equals',
        'criteria[0][value]'      => $id_usuario,
      ];

      $res_tasks = $this->httpClient->get($base_url . '/search/TicketTask', [
        'headers' => [
          'App-Token'     => $app_token,
          'Session-Token' => $session_token,
        ],
        'query' => $query,
      ]);

      $task_results = json_decode($res_tasks->getBody(), TRUE);

      // 3. Cerrar Sesión
      $this->httpClient->get($base_url . '/killSession', [
        'headers' => ['App-Token' => $app_token, 'Session-Token' => $session_token],
      ]);

      // 4. Procesar resultados
      if (empty($task_results['data'])) {
        return ['#markup' => "<h3>No hay tareas asignadas al técnico ID: $id_usuario</h3>"];
      }

      $items = [];
      foreach ($task_results['data'] as $task) {
        // Mapeamos según tu JSON: 1=Descripción, 3=Fecha, 7=Estado
        $descripcion = isset($task[1]) ? strip_tags($task[1]) : 'Sin descripción';
        $fecha       = $task[3] ?? 'Sin fecha';
        $estado_id   = $task[7] ?? 'N/A';
        
        // Creamos una línea bonita para cada tarea
        $items[] = "<strong>[$fecha]</strong> (Estado: $estado_id) - $descripcion";
      }

      return [
        '#theme' => 'item_list',
        '#title' => $this->t('Tareas de GLPI para el técnico @id', ['@id' => $id_usuario]),
        '#items' => $items,
      ];

    } catch (GuzzleException $e) {
      return ['#markup' => "Error: " . $e->getMessage()];
    }
  }
}