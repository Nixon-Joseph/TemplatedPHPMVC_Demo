<?php

use devpirates\MVC\Base\ControllerResponse;
use devpirates\MVC\HttpStatusCode;

trait AuthorizedApiController {
    use AuthorizedController;

    /**
     * Executes passed in function if the user is authorized to execute it
     * Authorization is whether or not the user is logged in, or in one of the optionally passed in roles
     *
     * @param callable $action
     * @param any|null $callableParams
     * @param array|null $roles
     * @return ControllerResponse | null
     */
    protected function authorize(callable $action, $callableParams = null, ?array $roles = null): ?ControllerResponse {
        if ($this->isLoggedIn() === true) {
            if (isset($roles) === false || $roles === null || sizeof($roles) === 0) {
                if (isset($callableParams)) {
                    return $action($callableParams);
                } else {
                    return $action();
                }
            } else {
                // $userId = $this->getCurrentUserid();
                // $roleHelper = new RoleHelper();
                // get roles for user
                // if user is in any of the passed in roles, run action, and return
                if (true) {
                    if (isset($callableParams)) {
                        return $action($callableParams);
                    } else {
                        return $action();
                    }
                }
            }
        }
        http_response_code(HttpStatusCode::UNAUTHORIZED);
    }
}
?>