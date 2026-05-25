<?php
declare(strict_types=1);

class CFrontController
{
    public function dispatch(string $controllerName, string $actionName, array $params = []): mixed
    {
        $controllerName = trim($controllerName);
        $actionName = trim($actionName);

        if ($controllerName === '' || $actionName === '') {
            return ['errore' => 'Controller non trovato.'];
        }

        // TODO: when the View layer is available, map URL segments/query params
        // to controller/action names before calling this method.
        $className = str_starts_with($controllerName, 'C') ? $controllerName : 'C' . $controllerName;
        $controllerFile = __DIR__ . '/' . $className . '.php';

        if (!is_file($controllerFile)) {
            return ['errore' => 'Controller non trovato.'];
        }

        require_once $controllerFile;

        if (!class_exists($className)) {
            return ['errore' => 'Controller non trovato.'];
        }

        if (!method_exists($className, $actionName)) {
            return ['errore' => 'Metodo non consentito.'];
        }

        $controller = new $className();
        // TODO: after View implementation, add robust GET/POST to method-parameter
        // mapping and validation rules.
        return $controller->$actionName(...array_values($params));
    }
}
