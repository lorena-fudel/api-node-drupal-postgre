<?php

namespace Drupal\conector_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\RequestException;

class HistorialController extends ControllerBase {

  public function mostrar() {
    // 1. Recuperar el token de la sesión (el mismo nombre que usas en LoginForm)
    $token = \Drupal::service('session')->get('mi_token_api');

    if (!$token) {
      return [
        '#markup' => $this->t('No tienes un token válido. Por favor, <a href="/api/entrar">inicia sesión</a>.'),
      ];
    }

    $client = \Drupal::httpClient();

    try {
      // 2. Llamada a la API
      $response = $client->get('http://api:3000/files/ver-historial', [
        'headers' => [
          'Authorization' => 'Bearer ' . $token,
          'Accept' => 'application/json',
        ],
      ]);

      // 3. EXTRAER EL CONTENIDO CORRECTAMENTE
      // Usamos (string) para convertir el stream de Guzzle en texto plano
      $contenido = (string) $response->getBody();

    return [
  '#type' => 'markup',
  '#markup' => '<h2>Contenido de hola.txt:</h2><pre>' . $contenido . '</pre>',
  '#cache' => [
    'max-age' => 0, // Esto obliga a Drupal a recargar de la API siempre
  ],
];

    } catch (RequestException $e) {
      // Si la API devuelve error (401, 403, 404), lo capturamos aquí
      return [
        '#markup' => $this->t('Error de la API: @message', ['@message' => $e->getMessage()]),
      ];
    } catch (\Exception $e) {
      // Cualquier otro error de PHP
      return [
        '#markup' => $this->t('Error inesperado: @message', ['@message' => $e->getMessage()]),
      ];
    }
  }


    
    public function saludar() {
    $token = \Drupal::service('session')->get('mi_token_api');

    if (!$token) {
      \Drupal::messenger()->addWarning('Debes loguearte primero.');
      return new \Symfony\Component\HttpFoundation\RedirectResponse('/api/entrar');
    }

    $client = \Drupal::httpClient();
    try {
      $response = $client->get('http://api:3000/files/saludar', [
        'headers' => ['Authorization' => 'Bearer ' . $token]
      ]);

      $data = json_decode($response->getBody());
      
      // Construimos un diseño sencillo con la foto y la hora
      $html = "
        <div style='text-align: center; border: 1px solid #ccc; padding: 20px; border-radius: 10px;'>
          <h1>" . $data->mensaje . "</h1>
          <p style='font-size: 1.5em; color: #007bff;'>🕒 Hora del servidor API: <strong>" . $data->hora . "</strong></p>
          <img src='" . $data->foto . "' alt='Foto API' style='max-width: 100%; height: auto; border-radius: 5px; margin-top: 15px;'>
        </div>
      ";

      return [
        '#markup' => $html,
        '#cache' => ['max-age' => 0], // Importante para que la hora se actualice al refrescar
      ];
    } catch (\Exception $e) {
      return ['#markup' => 'No se pudo conectar: ' . $e->getMessage()];
    }
  }


}