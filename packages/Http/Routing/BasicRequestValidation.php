<?php

declare(strict_types=1);

namespace WpPack\Http\Routing;

use Exception;
use WP_Error;
use WP_REST_Request;

class BasicRequestValidation implements RequestValidationInterface
{
    public static function validate(WP_REST_Request $request)
    {
        if(is_user_logged_in()){
            return true;
        }
        $validate = new static;
        try {          
            // $validate->validateIsNotBrowser();
            $auth = $validate->getUsernameAndAppPassordFromBasicAuthentication($request);    
            $user = $validate->getUser($auth['username'], $auth['app_password']);
        } catch (Exception $e) {
            
            return new WP_Error('validation-error', $e->getMessage(), [
                'status' => $e->getCode(),
            ]);
        }

        if (!$auth || !$user || !$user->exists()) {
            return false;
        }

        return true;
    }

    public function getUser($username, $appPassword)
    {
        $wpUser = get_user_by('login', $username);

        if (!$wpUser || !$wpUser->exists()) {
            throw new Exception('User not found', 401);
        }

        return $wpUser;
    }

    /**
     * 
     * @param \WP_REST_Request $request 
     * @return string[] 
     * @throws \Exception 
     */
    public function getUsernameAndAppPassordFromBasicAuthentication(WP_REST_Request $request)
    {

        if(!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])){
            throw new Exception('Invalid authorization header', 401);
        }

        return [
            'username' => $_SERVER['PHP_AUTH_USER'],
            'app_password' => $_SERVER['PHP_AUTH_PW']
        ];
    }

        /**
     * 
     * @return array|false 
     */
    public function validateIsNotBrowser(){
        require_once ABSPATH . '/wp-admin/includes/dashboard.php';
        // array(10) {
        //     ["name"]=>     string(6) "Chrome"
        //     ["version"]=>     string(9) "103.0.0.0"
        //     ["platform"]=>     string(7) "Windows" - A user-friendly platform name, if it can be determined
        //     ["update_url"]=>     string(29) "https://www.google.com/chrome"
        //     ["img_src"]=>     string(43) "http://s.w.org/images/browsers/chrome.png?1"
        //     ["img_src_ssl"]=>     string(44) "https://s.w.org/images/browsers/chrome.png?1"
        //     ["current_version"]=>     string(2) "18"
        //     ["upgrade"]=>     bool(false)
        //     ["insecure"]=>     bool(false)- Whether the browser is deemed insecure
        //     ["mobile"]=>     bool(false)
        //   }
        $browser = wp_check_browser_version();

        if(!$browser || (in_array($browser['name'], ['', 'unknown']) && $browser['insecure'] === false)){
            return true;
        }

        throw new Exception('User-Agent not allowed or insecure connection', 401);
    }
}
