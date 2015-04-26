<?php

namespace Core;

use Di;

class Template {

	/**
	 * @var string
	 */
	private $template;

	/**
	 * @var \Core\Router 
	 */
	private $router;

	/**
	 * @var \Twig_Environment 
	 */
	private $twig = NULL;

	/**
	 * @var array
	 */
	private $twigFunctions;

	public function __construct() {
		$this->router = Di::get('Router');
		$this->template = \Conf::get('nc.template', false);
		$this->twigFunctions = array(
			'url' => array($this, 'fnUrl'),
		);
	}

	/**
	 * @param string $template
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}

	/**
	 * @return string
	 */
	public function getTemplate() {
		return $this->template;
	}

	public function addTwigFunctions($functions) {
		$this->twigFunctions += $functions;
	}

	private function getTemplatePath() {
		$path = WEB . 'View';
		return $this->template ? $path . '/' . $this->template : $path;
	}

	public function render(Response $response = NULL) {
		$debug = ob_get_clean();
		if ($debug && \Conf::get('nc.debug')) {
			echo('<pre class="debug_dump">' . $debug . '</pre>');
		}

		if (!$response) {
			$response = $this->router->getResponse();
		}

		if ($response->getType() == 'text/html') {
			echo $this->renderView($response);
		} else
			echo $response->getData();
	}

	public function renderView(Response $response = NULL) {
		if (!$this->twig)
			$this->twig = $this->prepareTwig();
		$template = ($response->getPath() ? $response->getPath() : $this->getTemplatePath());
		$this->twig->getLoader()->setPaths(ABSPATH . $template . '/');
		return $this->twig->render($response->getTemplate() . '.html.twig', $response->getData());
	}

	private function prepareTwig() {
		$loader = new \Twig_Loader_Filesystem(ABSPATH);
		$twig = new \Twig_Environment($loader, array('cache' => ABSPATH . '/core/cache/twig/', 'debug' => \Conf::get("nc.debug")));

		foreach ($this->twigFunctions as $name => $method) {
			$function = new \Twig_SimpleFunction($name, $method);
			$twig->addFunction($function);
		}

		$twig->addExtension(new \Twig_Extension_Core());
		if (\Conf::get("nc.debug"))
			$twig->addExtension(new \Twig_Extension_Debug());
		$twig->addGlobal('Me', Di::get('Me'));
		return $twig;
	}

	public function fnUrl($route, $args = array(), $escaped = true) {
		if ($route === 'site')
			return $this->router->getUrl();
		if ($route === 'template')
			return $this->router->getUrl() . '/' . $this->getTemplatePath();
		if ($route === 'assets')
			return $this->router->getUrl() . '/core/assets/' . (is_string($args) ? $args : '');

		return rtrim($this->router->url($route, $args, $escaped), '/');
	}

}
