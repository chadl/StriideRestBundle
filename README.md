StriideRestBundle
=================

The intent here is to provide a convenient service interface for REST calls

Example:
--------

$payload = $this->get('striide_rest.rest.service')->get("http://rest.com/get/4");

Routing
-------

StriideRestBundle:
    resource: "@StriideRestBundle/Controller/"
    type:     annotation
    prefix:   /
