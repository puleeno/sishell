<?php
/**
Plugin Name: Simple Shell for Development
Author: Puleeno Nguyen
Author URI: https://puleeno.com
Version: 1.0.0
Description: This shell use for personal purpose to development, backup or testing on your server
*/

class Sishell {
	protected $shell_functions = array('passthru', 'proc_open', 'popen', 'shell_exec', 'exec', 'system');
	protected $function_statuses = array();

	protected $allowed_function;
	protected $results = array();

	public function __construct() {
		$allowed_function = $this->detect_allow_function();
		if (isset($allowed_function['status'])) {
			$this->allowed_function = $allowed_function['name'];
		}
	}

	protected function detect_allow_function() {
		$disable_functions = explode(',', ini_get('disable_functions'));
		foreach($this->shell_functions as $function) {
			$this->function_statuses[] = array(
				'name' => $function,
				'status' => !in_array($function, $disable_functions)
			);
		}

		$allowed_functions = array_filter($this->function_statuses, function($function){
			return $function['status'];
		});
		return array_shift($allowed_functions);
	}

	public function execute_command() {
		if (isset($_POST['commands'])) {
			ob_start();
			$comamnds = explode("\n", $_POST['commands']);
			$results = array();
			foreach(array_filter($comamnds) as $command) {
				$results[] = call_user_func($this->allowed_function);
			}
			
			$ouput = ob_get_clean();

			// Output JSON only
			exit();
		}
	}

	public function show_command_statuses() {
		?>
		<ul class="function-status">
			<?php foreach($this->function_statuses as $function): ?>
				<li><?php echo $function['name']; ?>: <?php echo $function['status'] ? 'active' : 'disable'; ?></li>
			<?php endforeach; ?>
		<ul>
		<?php
	}

	public function show_outputs() {
	}

	public function write_js() {
		?>
		<script type="text/javascript">
			<!-- Ajax module: https://github.com/fdaciuk/ajax -->
			!function(e,t){"use strict";"function"==typeof define&&define.amd?define("ajax",t):"object"==typeof exports?exports=module.exports=t():e.ajax=t()}(this,function(){"use strict";function e(e){var r=["get","post","put","delete"];return e=e||{},e.baseUrl=e.baseUrl||"",e.method&&e.url?n(e.method,e.baseUrl+e.url,t(e.data),e):r.reduce(function(r,o){return r[o]=function(r,u){return n(o,e.baseUrl+r,t(u),e)},r},{})}function t(e){return e||null}function n(e,t,n,u){var c=["then","catch","always"],i=c.reduce(function(e,t){return e[t]=function(n){return e[t]=n,e},e},{}),f=new XMLHttpRequest,p=r(t,n,e);return f.open(e,p,!0),f.withCredentials=u.hasOwnProperty("withCredentials"),o(f,u.headers,n),f.addEventListener("readystatechange",a(i,f),!1),f.send(s(n)?JSON.stringify(n):n),i.abort=function(){return f.abort()},i}function r(e,t,n){if("get"!==n.toLowerCase()||!t)return e;var r=i(t),o=e.indexOf("?")>-1?"&":"?";return e+o+r}function o(e,t,n){t=t||{},u(t)||(t["Content-Type"]=s(n)?"application/json":"application/x-www-form-urlencoded"),Object.keys(t).forEach(function(n){t[n]&&e.setRequestHeader(n,t[n])})}function u(e){return Object.keys(e).some(function(e){return"content-type"===e.toLowerCase()})}function a(e,t){return function n(){t.readyState===t.DONE&&(t.removeEventListener("readystatechange",n,!1),e.always.apply(e,c(t)),t.status>=200&&t.status<300?e.then.apply(e,c(t)):e["catch"].apply(e,c(t)))}}function c(e){var t;try{t=JSON.parse(e.responseText)}catch(n){t=e.responseText}return[t,e]}function i(e){return s(e)?f(e):e}function s(e){return"[object Object]"===Object.prototype.toString.call(e)}function f(e,t){return Object.keys(e).map(function(n){if(e.hasOwnProperty(n)&&void 0!==e[n]){var r=e[n];return n=t?t+"["+n+"]":n,null!==r&&"object"==typeof r?f(r,n):p(n)+"="+p(r)}}).filter(Boolean).join("&")}function p(e){return encodeURIComponent(e)}return e});
		</script>
		<script type="text/javascript">
			(function(w, d, g, q, qa, ssf, e, s, p, a){
				var f = d[g](ssf);
				f[e](s, function(e){
					e.preventDefault();

					var formData = new FormData(e.target);

					var r = w.ajax({
					  method: 'POST',
					  url: e.target.baseURI,
					  data: new URLSearchParams(formData),
					}).then(function(response) {
						console.log(response);
					});
				});
			})(window, document, 'getElementById', 'querySelector', 'querySelectorAll', 'sishell-input', 'addEventListener', 'submit', 'ajax');
		</script>
		<?php
	}

	public function write_css() {
		?>
		<style>
			*{
				box-sizing: border-box;
			}
			body {
				height: 100%;
				width: 100%;
				padding: 0;
				margin: 0;
				position: absolute;
				overflow: hidden;
			}
			ul.function-status {
				width: 100%;
				list-style: none;
				padding: 0;
				margin: 0;
			}
			ul.function-status li {
				display: inline-block;
			}
			.sishell-control {
				width: 100%;
			}
			.sishell-wrap {
				display: flex;
				flex-direction: column;
				height: calc(100% - 20px);
				margin: 10px;
				padding: 3px;
				border: 1px solid #000;
			}
			.sishell-wrap .log-outputs {
				flex: 1;
			}
			.active-commands, .log-outputs {
				margin-bottom: 5px;
				padding: 3px;
			}
			.active-commands, .log-outputs, .input-wrap{
				border: 1px solid #000;
			}
			.field-command {
				display: flex;
			}
			.sishell-command {
				flex: 1;
				padding: 5px;
			}
			.submit-wrap {
				height: 100%;
			}
			.submit-wrap button {
				height: 40px;
			}
		</style>
		<?php
	}

	public function render_gui() {
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title>Sishell - a Simple Terminal</title>
			<?php $this->write_css(); ?>
		</head>
		<body>
			<div class="sishell-wrap">
				<div class="active-commands">
					<?php $this->show_command_statuses(); ?>
				</div>
				<div class="log-outputs">
					<?php $this->show_outputs(); ?>
				</div>
				<div class="input-wrap">
					<form id="sishell-input" action="" method="POST">
						<div class="input-field field-command">
							<textarea class="sishell-control sishell-command" type="text" name="command"></textarea>
							<div class="submit-wrap">
								<button>Run command</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<?php $this->write_js(); ?>
		</body>
		</html>
		<?php
	}

	public function run($is_wp = true) {
		if ($is_wp === false || (isset($_GET['action']) && $_GET['action'] === 'sishell')) {
			$this->execute_command();

			$this->render_gui();

			// Stop all scripts
			exit();
		}
	}
}

$sishell = new Sishell();

if (defined('ABSPATH')) {
	if (!is_admin()) {
		return add_action('plugins_loaded', array($sishell, 'run'));
	}
	return;
}

$sishell->run(false);
