<?php
namespace tests;

use Germania\TwigRequestHandler\TwigRequestHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

use Twig\Environment as TwigEnvironment;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;

class TwigRequestHandlerTest extends \PHPUnit\Framework\TestCase
{
    use ProphecyTrait;

    public $response_factory;
    public $request_factory;

    public $twig;

    public function setUp() : void
    {
        $this->response_factory = new ResponseFactory();
        $this->request_factory = new ServerRequestFactory();
    }

    public function testInstantiation()
    {
        $twig_mock = $this->prophesize( TwigEnvironment::class );
        $twig = $twig_mock->reveal();

        $sut = new TwigRequestHandler( $twig, $this->response_factory);
        $this->assertInstanceOf(RequestHandlerInterface::class, $sut);

        return $sut;
    }

    /**
     * @depends testInstantiation
     */
    public function testRequestHandler( $sut )
    {
        $render_result = 'foobar';
        $template = 'template.twig';
        $template_attr = 'template';
        $context = array("foo" => "bar");
        $context_attr = "context";
        $status = 400;
        $content_type = "text/xml";

        $twig_mock = $this->prophesize( TwigEnvironment::class );
        $twig_mock->render(Argument::type('string'), Argument::type('array'))->willReturn($render_result);
        $twig = $twig_mock->reveal();


        # These do not work?!
        // $request_mock = $this->prophesize(ServerRequestInterface::class);
        // $request_mock->getAttribute('template')->willReturn($template);
        // $request_mock->getAttribute('context')->willReturn($context);
        // $request = $request_mock->reveal();

        $request = $this->request_factory
                        ->createServerRequest("GET", "/")
                        ->withAttribute($template_attr, $template)
                        ->withAttribute($context_attr, $context);

        $sut->setTemplateAttributeName($template_attr)
            ->setContextAttributeName($context_attr)
            ->setResponseStatusCode($status)
            ->setResponseContentType($content_type)
            ->setTwig($twig);

        $response = $sut->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals($content_type, $response->getHeaderLine('Content-Type'));
        $this->assertEquals($status, $response->getStatusCode());
    }


    /**
     * @depends testInstantiation
     * @dataProvider provideInvalidAttributes
     */
    public function testRequestHandlerExceptions( $template_attr, $template, $context_attr, $context, $sut )
    {
        $render_result = 'foobar';

        $twig_mock = $this->prophesize( TwigEnvironment::class );
        $twig_mock->render(Argument::type('string'), Argument::type('array'))->willReturn($render_result);
        $twig = $twig_mock->reveal();

        $request = $this->request_factory
                        ->createServerRequest("GET", "/")
                        ->withAttribute($template_attr, $template)
                        ->withAttribute($context_attr, $context);

        $sut->setTemplateAttributeName($template_attr)
            ->setContextAttributeName($context_attr)
            ->setTwig($twig);

        $this->expectException(\RuntimeException::class);
        $sut->handle($request);
    }

    public function provideInvalidAttributes()
    {
        $render_result = 'foobar';

        $template_attr = 'template';
        $template = 'template.twig';

        $context_attr = "context";
        $context = array("foo" => "bar");


        return array(
            [ $template_attr, null,      $context_attr, $context],
            [ $template_attr, 99,        $context_attr, $context],
            [ $template_attr, false,     $context_attr, $context],

            [ $template_attr, $template, $context_attr, null],
            [ $template_attr, $template, $context_attr, 99],
            [ $template_attr, $template, $context_attr, "str"],
        );
    }



    /**
     * @depends testInstantiation
     */
    public function testTwigInterceptors( $sut )
    {
        $twig_mock = $this->prophesize( TwigEnvironment::class );
        $twig = $twig_mock->reveal();

        $res = $sut->setTwig($twig);
        $this->assertSame($res, $sut);

        return $sut;
    }

    /**
     * @depends testInstantiation
     */
    public function testResponseFactoryInterceptors( $sut )
    {
        $res = $sut->setResponseFactory($this->response_factory);
        $this->assertSame($res, $sut);
    }

    /**
     * @depends testInstantiation
     */
    public function testResponseStatusCodeInterceptors( $sut )
    {
        $res = $sut->setResponseStatusCode(400);
        $this->assertSame($res, $sut);
    }

    /**
     * @depends testInstantiation
     */
    public function testResponseContentTypeInterceptors( $sut )
    {
        $res = $sut->setResponseContentType("text/xml");
        $this->assertSame($res, $sut);
    }

    /**
     * @depends testInstantiation
     */
    public function testAttributeNameInterceptors( $sut )
    {
        $res = $sut->setTemplateAttributeName("foo");
        $this->assertSame($res, $sut);

        $res = $sut->setContextAttributeName("foo");
        $this->assertSame($res, $sut);

        return $sut;
    }

}

