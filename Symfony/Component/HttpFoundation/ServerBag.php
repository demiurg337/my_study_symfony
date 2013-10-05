<?php
namespace Symfony\Component\HttpFoundation;

require_once 'ParameterBag.php';


class ServerBag extends ParameterBag
{
    public function getHeaders()
    {
        $headers = array();
        /*
        Список CONTENT заголовков которые нужно сохранять в объект
        */
        $contentHeaders =  array('CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true);
        foreach($this->parameters as $key => $val) {
            /*
            Для взятия HTTP_ заголовков
            */
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $val;
            }
            /*
            дЛЯ взятия CONTENT_ заголовков
            запоминается только определенный список заголовков
            */
            elseif (isset($contentHeaders[$key])) {
                $headers[$key] = $val;
            }
        }

        /*
        Когда http авторизация
        */
        if (isset($this->parameters['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER']= $this->parameters['PHP_AUTH_USER'];        
            $headers['PHP_AUTH_PW'] = isset($this->parameters['PHP_AUTH_PW']) ? $this->parameters['PHP_AUTH_PW'] : '';
        }
        else {
            /*
            Кокойто костиль для HTTP аутентификации
            */


            /*
             * php-cgi under Apache does not pass HTTP Basic user/pass to PHP by default
             * For this workaround to work, add these lines to your .htaccess file:
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             *
             * A sample .htaccess file:
             * RewriteEngine On
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             * RewriteCond %{REQUEST_FILENAME} !-f
             * RewriteRule ^(.*)$ app.php [QSA,L]
             */

            /*
            далее не розобрано, поскольку не понятно что написано вверху
            +++типа дайджест аутентифакация+++
            */
            $authorizationHeader = null;
            if (isset($this->parameters['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $this->parameters['HTTP_AUTHORIZATION'];
            } elseif (isset($this->parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $this->parameters['REDIRECT_HTTP_AUTHORIZATION'];
            }

            if (null !== $authorizationHeader) {
                if (0 === stripos($authorizationHeader, 'basic')) {
                    // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
                    $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)));
                    if (count($exploded) == 2) {
                        list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                    }
                } elseif (empty($this->parameters['PHP_AUTH_DIGEST']) && (0 === stripos($authorizationHeader, 'digest'))) {
                    // In some circumstances PHP_AUTH_DIGEST needs to be set
                    $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
                    $this->parameters['PHP_AUTH_DIGEST'] = $authorizationHeader;
                }
            }
            //___/\____
        }

        /*
        Задает заголовок который кабы сигнализирует
        что пользователь авторизирован
        (вроди)
        */
        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['AUTHORIZATION'] = 'Basic '.base64_decode($headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW']); 
        }
        elseif (isset($headers['PHP_AUTH_DIGEST'])) {
            $headers['AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
        }

        return $headers;
    }    
}
