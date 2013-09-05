<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Controller;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PageNavigator
{
    private $request;
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * TODO: Use a RequestContext instead?
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function gotoProjectView($projectId)
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('{"ok":true}', 200, array('Content-Type' => 'application/json'));
        }

        return $this->redirectRoute('licenses_project_view', array('id' => $author->getProject()->getId()));
    }

    private function redirectRoute($routeName, $params, $absolute = false)
    {
        return new RedirectResponse(
            $this->router->generate($routeName, $params, $absolute)
        );
    }
}
