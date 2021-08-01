<?php
namespace BGStudios\Component\Mvc;

use LynkCMS\Component\Http\JsonResult;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Controller extends ContainerAwareController {
	public function __construct() {}
	public function init() {}
	public function renderContent($content) {
		$response = new Response($content);
		$response->setStatusCode(Response::HTTP_OK);
		$response->headers->set('Content-Type', 'text/html');
		return $response;
	}
	public function render($file, $data = Array()) {
		$__rendered_content = '';
		if (file_exists($file) && !is_dir($file)) {
			ob_start();
			$__render_file_return = include $file;
			$__rendered_content = ob_get_contents();
			ob_end_clean();
			if (is_string($__render_file_return))
				$__rendered_content .= $__render_file_return;
		}
		return $this->renderContent($__rendered_content);
	}
	public function renderJSON($data) {
		$response = new JsonResponse();
		$response->setStatusCode(Response::HTTP_OK);
		$data = $data instanceof JsonResult ? $data->export() : $data;
		$response->setData($data);
		return $response;
	}
	public function renderJSONString($str) {
		$response = new Response();
		$response->setStatusCode(Response::HTTP_OK);
		$response->setContent($str);
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	public function renderJSONP($data, $callback) {
		$response = $this->renderJSON($data);
		$response->setCallback($callback);
		return $response;
	}
	public function renderJSONPString($str, $callback) {
		$response = $this->renderJSONString($str);
		$response->setCallback($callback);
		return $response;
	}
	public function redirect($url) {
		return new RedirectResponse($url);
	}
}