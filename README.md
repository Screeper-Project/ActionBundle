Graille-Labs - Screeper Project - Action bundle
=====================
**DEVELOPMENT IN PROGRESS**

![Screeper](http://img4.hostingpics.net/pics/8388841405135647.png)


Usage
------------

### AddAction :
* $command correspond a la commande a éxécuté, formatter, les paramètres correspondent a des paramètre entre %...%.
* $parameters répertorie la valeur de chaque paramètres de la manière suivante : array('param1' => array("value" => value1), 'param2' => array("value" => value2)....)
Si le paramètre est un pseudo ou un UUID, il peut etre référencé a un joueur (si le module PlayerBundle a été activé et configuré) afin d'éviter les problème lié aux changements de pseudo, le paramètre spécifié sera alors de type Player
et alors, value sera de la forme array('value' => PlayerEntity, 'type' => 'pseudo' ou 'uuid').
* $options définie les options de la commande :
    * 'date_execution' : date d'éxécution de la commande (par defaut : le plus tôt possible)
    * 'reboot' : Permet d'autorisé ou non le reboot de la commande en cas d'échec (par défaut : Reboot activé)
    * 'server' : Permet de spécifié le serveur d'éxécution de l'action (par defaut : le serveur 'default').
    * 'description' : Permet de fournir un commentaire, une description de la commande.


