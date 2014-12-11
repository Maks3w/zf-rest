<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Rest\Listener;

use Exception;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class ExceptionsListener extends AbstractListenerAggregate
{
    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onError'), 100);
    }

    /**
     * Listen to the dispatch event
     *
     * @param MvcEvent $e
     *
     * @return null|ApiProblem
     */
    public function onError(MvcEvent $e)
    {
        if (!(($exception = $e->getParam('exception')) instanceof Exception)) {
            return null;
        }

        return new ApiProblemResponse(new ApiProblem(
            $this->getHttpStatusCodeFromException($exception),
            $exception->getMessage()
        ));
    }

    /**
     * Ensure we have a valid HTTP status code for an ApiProblem
     *
     * @param Exception $e
     *
     * @return int
     */
    protected function getHttpStatusCodeFromException(Exception $e)
    {
        $code = $e->getCode();
        if (!is_int($code)
            || $code < 100
            || $code >= 600
        ) {
            return 500;
        }
        return $code;
    }
}
