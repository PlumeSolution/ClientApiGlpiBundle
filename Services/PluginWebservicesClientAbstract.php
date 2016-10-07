<?php

namespace L3\Bundle\ClientApiGlpiBundle\Services;

/**
 * Classe abstraite définissant les fonctions nécessaires à l'implémentation
 * d'un client pour le plugin webservices de GLPI.
 */
abstract class PluginWebservicesClientAbstract {
    
    /**
     * Consctructeur du service
     * 
     * @param string $api_url Lien vers le script du plugin webservices de GLPI.
     */
    abstract public function __construct($api_url);
    
    /**
     * Appel d'une méthode du plugin webservices
     * 
     * @param string $method Méthode à appeler. (par exemple: 'glpi.test')
     * @param array $args Arguments à passer à la méthode.
     * @param string $session Token obtenu lors de l'authentification. (Optionnel, mais nécessaire pour certaines fonctions)
     *
     * @return array Résultat de la fonction appelée.
     * @throws \Exception Exception retournée en cas d'erreur lors de l'exécution de la fonction.
     */
    abstract public function call($method, $args, $session=null);
    
    /**
     * Permet de s'authentifier afin d'obtenir une session.
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
