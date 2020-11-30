<?php
namespace Germania\TwigRequestHandler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment as TwigEnvironment;

class TwigRequestHandler implements RequestHandlerInterface
{

    /**
     * @var ResponseFactoryInterface
     */
    public $response_factory;


    /**
     * @var TwigEnvironment|null
     */
    public $twig;


    /**
     * Default response status code
     * @var integer
     */
    public $response_status_code = 200;


    /**
     * Default response content type
     * @var integer
     */
    public $response_content_type = "text/html";


    /**
     * Request attribute name for the Twig template
     * @var string
     */
    public $template_attribute_name = 'template';


    /**
     * Request attribute name for the Twig template context variables
     * @var string
     */
    public $context_attribute_name = 'context';


    /**
     * @param TwigEnvironment          $twig             Twig Environment
     * @param ResponseFactoryInterface $response_factory PSR-17 Response Factory
     */
    public function __construct(TwigEnvironment $twig, ResponseFactoryInterface $response_factory)
    {
        $this->setTwig($twig);
        $this->setResponseFactory($response_factory);
    }



    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $context = $request->getAttribute($this->context_attribute_name, null);

        if ($context instanceOf \ArrayObject) {
            $context = $context->getArrayCopy();
        }

        if (!is_array($context)) {
            $context_type = is_object($context) ? get_class($context) : gettype($context);
            $msg = sprintf(
                "Expected Request attribute '%s' to be array or ArrayObject, got '%s'.",
                $this->context_attribute_name,
                $context_type
            );
            throw new \RuntimeException($msg);
        }

        $template = $request->getAttribute($this->template_attribute_name, null);
        if (!is_string($template) or empty($template)) {
            $msg = sprintf(
                "Expected Request attribute '%s' to be non-empty string, got '%s'.",
                $this->template_attribute_name,
                gettype($template)
            );
            throw new \RuntimeException($msg);
        }


        $html = $this->twig->render($template, $context);

        $response = $this->response_factory
                    ->createResponse($this->response_status_code)
                    ->withHeader('Content-Type', $this->response_content_type);

        $response->getBody()->write($html);

        return $response;
    }



    /**
     * Sets the Twig Environment.
     *
     * @param TwigEnvironment $twig
     */
    public function setTwig(TwigEnvironment $twig) : self
    {
        $this->twig = $twig;
        return $this;
    }


    /**
     * Sets the Response Factory.
     *
     * @param ResponseFactoryInterface $response_factory PSR-17 ResponseFactory
     */
    public function setResponseFactory(ResponseFactoryInterface $response_factory) : self
    {
        $this->response_factory = $response_factory;
        return $this;
    }


    /**
     * Sets the Status code for generated response.
     *
     * @param int $response_status_code HTTP Status Code
     */
    public function setResponseStatusCode(int $response_status_code) : self
    {
        $this->response_status_code = $response_status_code;
        return $this;
    }


    /**
     * Sets the content-type for generated response.
     *
     * @param string $response_content_type Response Content-type
     */
    public function setResponseContentType(string $response_content_type) : self
    {
        $this->response_content_type = $response_content_type;
        return $this;
    }


    /**
     * Sets the request attribute that carries the template.
     *
     * @param string $attr Request attribute name
     */
    public function setTemplateAttributeName(string $attr) : self
    {
        $this->template_attribute_name = $attr;
        return $this;
    }


    /**
     * Sets the request attribute that carries the context array.
     *
     * @param string $attr Request attribute name
     */
    public function setContextAttributeName(string $attr) : self
    {
        $this->context_attribute_name = $attr;
        return $this;
    }
}
