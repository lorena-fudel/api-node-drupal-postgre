<?php

namespace Drupal\conector_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Controlador para gestionar la integración con GLPI.
 */
class GlpiController extends ControllerBase {

  protected $httpClient;

  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('http_client'));
  }

  /**
   * Marcador de posición para buscar usuario por email.
   */
  public function buscarUsuarioPorEmail(Request $request) {
    return new JsonResponse(['status' => 'Pendiente']);
  }

  /**
   * Marcador de posición para mostrar tareas de usuario.
   * (Este es el que te está dando el error ahora)
   */
  public function mostrarTareasUsuario($user_id = NULL) {
    return ['#markup' => $this->t('Listado de tareas para el ID: @id', ['@id' => $user_id])];
  }

  /**
   * FUNCIÓN PRINCIPAL: Listado de trabajadores con filtros.
   */
  public function listarTrabajadoresIac(Request $request) {
    $base_url   = getenv('GLPI_BASE_URL');
    $app_token  = getenv('GLPI_APP_TOKEN');
    $user_token = getenv('GLPI_USER_TOKEN');

    $nombre = mb_strtoupper($request->query->get('nombre', ''), 'UTF-8');
    $dept   = mb_strtoupper($request->query->get('dept', ''), 'UTF-8');
    
    $limit  = 20;
    $page   = (int) $request->query->get('page', 0);
    $start  = $page * $limit;

    try {
      $res_session = $this->httpClient->get($base_url . '/initSession', [
        'headers' => ['App-Token' => $app_token, 'Authorization' => 'user_token ' . $user_token]
      ]);
      $session_token = json_decode($res_session->getBody())->session_token;

      $query = [
        'forcedisplay[0]' => 2,
        'forcedisplay[1]' => 34,
        'forcedisplay[2]' => 80,
        'forcedisplay[3]' => 13,
        'forcedisplay[4]' => 5,
        'range'           => "$start-" . ($start + $limit),
        'get_full_count'  => 'true',
      ];

      $i = 0;
      if (!empty($nombre)) {
        $query["criteria[$i][field]"] = 1;
        $query["criteria[$i][searchtype]"] = 'contains';
        $query["criteria[$i][value]"] = $nombre;
        $i++;
      }
      if (!empty($dept)) {
        if ($i > 0) $query["criteria[$i][link]"] = 'AND';
        $query["criteria[$i][field]"] = 13;
        $query["criteria[$i][searchtype]"] = 'contains';
        $query["criteria[$i][value]"] = $dept;
      }

      $res_users = $this->httpClient->get($base_url . '/search/User', [
        'headers' => ['App-Token' => $app_token, 'Session-Token' => $session_token],
        'query' => $query,
      ]);

      $total = 0;
      if ($h = $res_users->getHeader('Content-Range')) {
        $total = (int) end(explode('/', $h[0]));
      }

      $data = json_decode($res_users->getBody(), TRUE);
      $this->httpClient->get($base_url . '/killSession', ['headers' => ['App-Token' => $app_token, 'Session-Token' => $session_token]]);

      // --- RENDERIZADO ---
      $build['filtro_form'] = \Drupal::formBuilder()->getForm('\Drupal\conector_api\Form\TrabajadorFilterForm');

      $build['resumen'] = [
        '#markup' => '<div class="messages messages--status">' . 
                     $this->t('Total: @total trabajadores.', ['@total' => $total]) . 
                     '</div>',
      ];

      $build['tabla'] = [
        '#type' => 'table',
        '#header' => [$this->t('ID'), $this->t('Nombre'), $this->t('Centro (80)'), $this->t('Depto (13)'), $this->t('Email')],
        '#rows' => [],
        '#empty' => $this->t('No hay resultados.'),
      ];

      if (!empty($data['data'])) {
        foreach ($data['data'] as $u) {
          $build['tabla']['#rows'][] = [$u[2], $u[34], $u[80], $u[13] ?: '---', $u[5]];
        }
      }

      $p_params = ['nombre' => $nombre, 'dept' => $dept];
      $pager_links = [];

      if ($page > 0) {
        $p_params['page'] = $page - 1;
        $pager_links[] = Link::fromTextAndUrl('« Anterior', Url::fromRoute('conector_api.lista_trabajadores', [], ['query' => $p_params]))->toRenderable();
      }

      $pager_links[] = ['#markup' => '<span style="padding: 0 15px;"> ' . $this->t('Página @p', ['@p' => $page + 1]) . ' </span>'];

      if (($start + $limit) < $total) {
        $p_params['page'] = $page + 1;
        $pager_links[] = Link::fromTextAndUrl('Siguiente »', Url::fromRoute('conector_api.lista_trabajadores', [], ['query' => $p_params]))->toRenderable();
      }

      $build['paginador'] = [
        '#type' => 'container',
        '#attributes' => ['style' => 'text-align:center; margin-top:20px;'],
        'links' => $pager_links,
      ];

      $build['#cache']['max-age'] = 0;
      return $build;

    } catch (\Exception $e) {
      return ['#markup' => "Error: " . $e->getMessage()];
    }
  }
}