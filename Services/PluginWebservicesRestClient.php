<?php

namespace L3\Bundle\ClientApiGlpiBundle\Services;

/**
 * Classe regroupant les fonctions de base pour interroger 
 * le plugin webservices de GLPI avec le protocole REST.
 * 
 * Ce service nécessite les modules PHP 'json' et 'curl'.
 * 
 * La documentation des fonctions est disponible sur le site du plugin :
 * https://forge.glpi-project.org/projects/webservices/wiki/En_devguide#Provided-Methods
 * 
 * ATTENTION !
 * Les arguments des fonctions sont passés dans l'url, ce qui limite donc la taille
 * et le nombre d'arguments à la taille maximum d'une url supporté par le module php
 * curl ET par le serveur web.
 * 
 */
class PluginWebservicesRestClient extends PluginWebservicesClientAbstract {
    private $api_url;
    
    /**
     * Contructeur du service.
     * 
     * @param string $api_url Lien vers le script 'rest.php' du plugin webservices de GLPI. (ex: https://.../plugins/webservices/rest.php)
     */
    public function __construct($api_url) {
        $this->api_url = $api_url;
    }

    /**
     * Appel d'une fonction REST du plugin webservices.
     *
     * @param string $method Méthode à appeler. (par exemple: 'glpi.test')
     * @param array $args Arguments à passer à la méthode.
     * @param string $session Token obtenu lors de l'authentification. (Optionnel, mais nécessaire pour certaines fonctions)
     *
     * @return array Résultat de la fonction appelée.
     * @throws \Exception Exception retournée en cas d'erreur lors de l'exécution de la fonction.
     */
    public function call($method, $args, $session=null) {
        /* ajout de la méthode */
        $args['method'] = $method;
        
        /* ajout de la session */
        if ($session !== null) {
            $args['session'] = $session;
        }
        
        /* envois de la requête */
        $http_res = file_get_contents($this->api_url . '?' . http_build_query($args), false);
        if (!$http_res) {
            throw new \Exception("Pas de réponse pour '$this->api_url'.");
        }
        
        /* réception de la réponse */
        $resultat = json_decode($http_res, true);
        if (!is_array($resultat)) {
            throw new \Exception('Type de la réponse incorrect. (json attendu)');
        }
        
        /* gestion des erreurs */
        if (isset($http_res['faultCode'])) {
            throw new \Exception($http_res['faultString'], $http_res['faultCode']);
        }
        
        return $resultat;
    }
    
    /**
     * Permet de s'authentifier afin d'obtenir un session nécessaire pour l'execution de certaines fonctions.
     * 
     * !!! 
     * Les informations de l'utilisateur concernent un !UTILISATEUR NATIF GLPI! donc ce n'est pas compatible avec
     * les comptes CASsifiés, ET ce n'est pas non plus le nom d'utilisateur/mot de passe du plugin webservices.
     * !!!
     * 
     * @param string $username Nom d'utilisateur.
     * @param string $password Mot de passe de l'utilisateur.
     * 
     * @return string Session.
     */
    public function login($username, $password) {
        return $this->call('glpi.doLogin', array(
            'login_name' => $username,
            'login_password' => $password
        ))['session'];
    }
    
    /**
     * Permet de se déconnecter du plugin webservices.
     * 
     * @param string $session Token obtenu lors de l'appel à la fonction login.
     * 
     * @return bool Retourne toujours True, car il n'y a aucune gestion d'erreur de logout du côté du webservice.
     */
    public function logout($session) {
        $this->call('glpi.doLogout', array(
            'session' => $session
        ));
        
        return true;
    }
}
