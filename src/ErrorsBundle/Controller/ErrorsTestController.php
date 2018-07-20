<?php

namespace Gamma\ErrorsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ErrorsTestController.
 *
 * @Route("/errors/test")
 */
class ErrorsTestController extends Controller
{
    /**
     * Produces E_NOTICE level error.
     *
     * @Route("/notice", name="errors.notice")
     *
     * @return Response
     */
    public function noticeAction()
    {
        $key = 'test_key_'.mt_rand(10, 10000);

        $arr = [];
        $var = $arr[$key];

        return new Response('Notice produced');
    }

    /**
     * Priduces E_WARNING level error.
     *
     * @Route("/warning", name="errors.warning")
     *
     * @return Response
     */
    public function warningAction()
    {
        $fileName = 'test_file_name_'.mt_rand(10, 10000);

        $fh = fopen($fileName, 'ab');

        return new Response('Warning produced');
    }

    /**
     * Priduces E_ERROR level error.
     *
     * @Route("/fatal", name="errors.fatal")
     *
     * @return Response
     */
    public function fatalAction()
    {
        $methodName = 'testMethod'.mt_rand(10, 10000);

        $obj = new \stdClass();
        $obj->$methodName();

        return new Response('Fatal error produced');
    }
}
