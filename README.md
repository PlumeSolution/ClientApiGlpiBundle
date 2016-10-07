Bundle Symfony 2.7 pour faciliter l'utilisation des webservices de GLPI.

Installation
====

L'installation se fait via composer. Dans un premier temps, il faut ajouter le repository au composer.json à la racine du projet symfony :

```
"repositories": [{
   "type": "composer",
   "url": "https://packagist.univ-lille3.fr/"
}]
```

Ensuite il faut executer la commande :

```
   composer require "l3/client-api-glpi-bundle"
```

Et enfin, ajouter le bundle à AppKernel.php

```
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new L3\Bundle\ClientApiGlpiBundle\L3ClientApiGlpiBundle(),
        );

        // ...
    }

    // ...
}
```

Configuration
====

Dans votre application, il faut ajouter un service afin de pouvoir utiliser les fonctions du Bundle :

### Pour utiliser le protocole XML-RPC : ###

```
parameters:
    # Lien vers le script 'xmlrpc.php' du plugin 'Webservices' :
    webservice_xmlrpc_url: https://assistance.univ-lille3.fr/plugins/webservices/xmlrpc.php
    
services:
    # Client pour le plugin 'Webservices' de GLPI (protocole XML-RPC) :
    webservice_xmlrpc_client:
        class: L3\Bundle\ClientApiGlpiBundle\Services\PluginWebservicesXmlRpcClient
        arguments: ["%webservice_xmlrpc_url%"]
```

### Pour utiliser le protocole REST : ###

```
parameters:
    # Lien vers le script 'rest.php' du plugin 'Webservices' :
    webservice_rest_url: https://assistance.univ-lille3.fr/plugins/webservices/rest.php
    
services:
    # Client pour le plugin 'Webservices' de GLPI (protocole REST) :
    webservice_rest_client:
        class: L3\Bundle\ClientApiGlpiBundle\Services\PluginWebservicesRestClient
        arguments: ["%webservice_rest_url%"]
```

Utilisation
====

```
        /* initialisation du service */
        $api = $this->get('webservice_rest_client');
        
        /* login sur l'api (récupération d'une session) */
        $session = $api->login('test_login', 'test_password');

        /* execution d'une requête non authentifiée et sans arguments */
        $test = $api->call('glpi.test', null);

        /* execution d'une requête authentifiée sans arguments */
        $mes_informations = $api->call('glpi.getMyInfo', null, $session);

        /* execution d'une requête authentifiée avec arguments */
        $tickets = $api->call('glpi.listTickets', [
            'limit' => 10,
            'status' => 'all',
            'startdate' => '2016-01-01',
            'enddate' => '2016-02-01'
            ], $session);

        /* logout de l'api (destruction de la session) */
        $api->logout($session);
```