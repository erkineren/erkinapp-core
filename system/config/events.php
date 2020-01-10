<?php


use ErkinApp\Event\Events;
use ErkinApp\Event\RoutingEvent;

return function () {


    ErkinApp()->on(Events::ROUTING, function (RoutingEvent $event) {

//        $classmaps = ErkinApp()->Container()->getClassMaps();
//        if (class_exists('Doctrine\Common\Annotations\AnnotationReader')) {
//            $reader = new AnnotationReader();
//
//            foreach ($classmaps['controllers'] as $class => $file) {
//                $c = new ReflectionClass($class);
//                foreach ($c->getMethods() as $method) {
//                    $ans = $reader->getMethodAnnotations($method);
//                    foreach ($ans as $an) {
//                        /** Symfony\Component\Routing\Annotation\Route $an */
//                        if ($an instanceof Symfony\Component\Routing\Annotation\Route) {
//                            $event->map($an->getPath(),
//                                [$class, $method->getName()],
//                                $an->getRequirements(),
//                                $an->getOptions(),
//                                $an->getHost(),
//                                $an->getSchemes(),
//                                $an->getMethods(),
//                                $an->getCondition());
//                        }
//                    }
//                }
//            }
//        }

    });
};
