<?php

namespace L3\Bundle\ClientApiGlpiBundle\Services;

/**
 * Classe abstraite définissant les methodes de base pour un client à l'api native de GLPI pour les versions >= 9.1
 */
abstract class ApiNativeClientAbstract {

    /**
     * Consctructeur du service
     * 
     * @param string $api_url Lien vers le script de l'api de GLPI.
     */
    abstract public function __construct($api_url);

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
    abstract public function call($http_method, $api_function, $session = null, $params_query_string = null, $params_json = null, $headers_http = null);

    /**
     * Permet de s'authentifier afin d'obtenir un token de session.
     * 
     * @param string $username Nom d'utilisateur.
     * @param string $password Mot de passe de l'utilisateur.
     * 
     * @return string Token de session.
     */
    abstract public function login($username, $password);

    /**
     * Permet de détruire une session.
     * 
     * @param string $session Token obtenu lors de l'appel à la fonction login.
     * 
     * @return bool True si la session a été détruite, False sinon.
     */
    abstract public function logout($session);
}
