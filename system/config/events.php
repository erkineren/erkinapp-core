<?php


use ErkinApp\Events\Events;
use ErkinApp\Events\RoutingEvent;

ErkinApp()->on(Events::ROUTING, function (RoutingEvent $event) {

    $classmaps = ErkinApp()->Container()->offsetGet('classmaps');
    if (class_exists('Doctrine\Common\Annotations\AnnotationReader')) {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        foreach ($classmaps['controllers'] as $class => $file) {
            $c = new ReflectionClass($class);
            foreach ($c->getMethods() as $method) {
                $ans = $reader->getMethodAnnotations($method);
                foreach ($ans as $an) {
                    /** Symfony\Component\Routing\Annotation\Route $an */
                    if ($an instanceof Symfony\Component\Routing\Annotation\Route) {
                        $event->map($an->getPath(), [$class, $method->getName()]);
                    }
                }
            }
        }
    }

});
