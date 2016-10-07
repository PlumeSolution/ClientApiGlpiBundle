<?php

namespace L3\Bundle\ClientApiGlpiBundle\Services;

/**
 * Implémentation du client pour l'api native de GLPI version >= 9.1.
 * Le module php json est requis pour l'utilisation de ce client.
 */
class ApiNativeClientRest extends ApiNativeClientAbstract {
    const HTTP_GET    = 'GET';
    const HTTP_POST   = 'POST';
    const HTTP_PUT    = 'PUT';
    const HTTP_DELETE = 'DELETE';
    
    private $api_url;
    
    /**
     * Consctructeur du service
     * 
     * @param string $api_url Lien vers le script de l'api de GLPI.
     */
    public function __construct($api_url) {
        $this->api_url = $api_url;
    }

    /**
     * Appel d'une fonction de l'api
     * 
     * @param integer $http_method Méthode HTTP à utiliser. (GET, POST, PUT, DELETE) (Utiliser les constantes HTTP_*)
     * @param string $api_function Fonction à executer sur l'api. ('initSession', '/initSession', '/Computer/4950', '/Computer/4950/NetworkPort/', 'https://assistance.univ-lille3.fr/apirest.php/NetworkPort/140320')
     * @param string $session Token obtenu lors de l'authentification. (Optionnel, mais nécessaire pour certaines fonctions)
     * @param array $params_query_string Paramètres à faire passer dans l'url. (Query string)
     * @param array $params_json Paramètres à faire passer dans le corps du message. (Format JSON, Méthodes HTTP : POST et PUT uniquement)
     * @param array $headers_http Paramètres à faire passer dans les headers HTTP. (Pour une utilisation normale, ce paramètre n'est pas nécessaire. Le header d'authentification est passé automatiquement quand la variable session est définie.)
     *
     * @return array Code de retour et résultat de la fonction appelée. (tableau associatif : ['code_retour' => integer, 'resultat' => array ])
     * @throws \Exception Exception retournée en cas d'erreur lors de l'exécution de la fonction.
     */
    public function call($http_method, $api_function, $session = null, $params_query_string = null, $params_json = null, $headers_http = null) {
        /* verif méthode HTTP */
        if (($http_method != ApiNativeClientRest::HTTP_GET) && ($http_method != ApiNativeClientRest::HTTP_POST) && ($http_method != ApiNativeClientRest::HTTP_PUT) && ($http_method != ApiNativeClientRest::HTTP_DELETE)) {
            throw new \Exception('Méthode HTTP invalide, utilisez les constantes HTTP_* de cette classe.');
        }
        
        /* ajout de l'url vers l'api dans le cas de l'utilisation du nom de la fonction uniquement */
        if (strpos($api_function, 'http') !== false) {
            $api_function = $this->api_url . '/' . $api_function;
        }
        
        /* si une session est passée en paramètre, ajout de celle-ci dans les headers HTTP */
        if ($session !== null) {
            $headers_http[] = 'Session-Token: ' . $session;
        }
        
        /* ajout du content type JSON dans les headers */
        $headers_http[] = 'Content-Type: application/json';
        
        /* contexte HTTP */
        $http_context = stream_context_create(array('http' => array(
            'method' => $http_method,
            'header' => $headers_http,
            'content' => json_encode($params_json)
        )));
        
        /* requête HTTP */
        $http_res = file_get_contents($api_function .'?' . http_build_query($params_query_string), false, $http_context);
        if ($http_res === false) {
            throw new \Exception("Erreur lors de la connexion à '$api_function'.");
        }
        
        /* decodage des données reçues */
        $resultat = json_decode($http_res, true);
        
        /* Gestion des erreurs de l'api via le code retour HTTP */
        $code_retour = $this->parserCodeRetourHTTP($http_response_header[0]);
        
        /* retour du resultat */
        return ['code_retour' => $code_retour, 'resultat' => $resultat];
    }

    /**
     * Fonction permettant d'extraire le code de retour HTTP depuis le status.
     * 
     * @param string $status Status de la requête. (HTTP/1.1 404 Not Found, HTTP/1.1 200 OK ...)
     * @return integer Code de retour HTTP. (200, 404, 500 ...)
     */
    private function parserCodeRetourHTTP($status) {
        $matches = [];
        
        /* recup du code de retour : HTTP/1.1 404 Not Found -> recup du 404 */
        preg_match('#HTTP/\d+\.\d+ (\d+)#', $status, $matches);
        
        return $matches[1];
    }

    /**
     * Permet de s'authentifier afin d'obtenir un token de session.
     * 
     * @param string $username Nom d'utilisateur.
     * @param string $password Mot de passe de l'utilisateur.
     * 
     * @return string Token de session.
     * @throws \Exception Exception retournée en cas d'erreur lors de l'exécution de la fonction.
     */
    public function login($username, $password) {
        $r = $this->call(ApiNativeClientRest::HTTP_GET, 'initSession', $headers_http = ['Authorization: Basic ' . base64_encode($username . ':' . $password)]);
        
        /* gestion des erreurs de connexion */
        if ($r['code_retour'] != 200) {
            return null;
        }
        
        /* retour du token de session */
        return $r['resultat']['session_token'];
    }

    /**
     * Permet de détruire une session.
     * 
     * @param string $session Token obtenu lors de l'appel à la fonction login.
     * 
     * @return bool True si la session a été détruite, False sinon.
     * @throws \Exception Exception retournée en cas d'erreur lors de l'exécution de la fonction.
     */
    public function logout($session) {
        $r = $this->call(ApiNativeClientRest::HTTP_GET, 'killSession', $session);
        
        if ($r['code_retour'] != 200) {
            return false;
        }
        
        return true;
    }
}
