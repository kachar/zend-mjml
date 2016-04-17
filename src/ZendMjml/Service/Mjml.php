<?php

namespace ZendMjml\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;
use ZendMjml\Exception\TemplateNotFoundException;

class Mjml
{
    protected $client;

    protected $renderer;

    protected $transport;

    /**
     * @param  GuzzleHttp\ClientInterface $client
     * @param  Zend\Mail\Transport\TransportInterface $transport
     * @param  Zend\View\Renderer\RendererInterface $client
     */
    public function __construct(ClientInterface $client, TransportInterface $transport, RendererInterface $renderer)
    {
        $this->client = $client;
        $this->transport = $transport;
        $this->renderer = $renderer;
    }

    /**
     * Creates a Zend\Mail\Message object and adds the provided content and default headers
     *
     * @param  string $content
     * @return Zend\Mail\Message
     */
    public function composeEmail($content)
    {
        $headers = new Headers();
        $headers->addHeaderLine('Content-Transfer-Encoding: 8bit');
        $headers->addHeaderLine('Content-Type: text/html; charset="UTF-8"');

        $mail = new Message();
        $mail->setHeaders($headers);
        $mail->setBody($content);
        return $mail;
    }

    /**
     * Renders MJML markup to HTML markup
     * Replaces provided variables `after` the html is built
     *
     * @param  string $mjml
     * @param  array $variables
     * @return string
     */
    public function renderMjml($mjml, $variables = [])
    {
        $html = $this->mjmlToHtml($mjml);
        return $this->replaceVariables($html, $variables);
    }

    /**
     * Renders MJML markup to HTML markup from a provided ViewModel
     *
     * When the template extension is `.phtml` will trigger default Zend PHP Renderer
     * to render the template as native php script.
     * Replaces provided variables `before` the MJML is built
     *
     * When the template extension is `.mjml` will load the contents of the file
     * and convert it to HTML.
     *
     * Convertion to MJML is the last step in the render process.
     *
     * @param  Zend\View\Model\ModelInterface $view
     * @throws ZendMjml\Exception\TemplateNotFoundException
     * @return string
     */
    public function renderView(ModelInterface $view)
    {
        $template = $view->getTemplate();
        $templatePath = $this->renderer->resolver($template);
        if (false === $templatePath) {
            throw new TemplateNotFoundException("The template file '{$template}' cannot be found.");
        }

        $extension = pathinfo($template, PATHINFO_EXTENSION);
        $markup = '';
        if ('pmjml' == $extension) {
            $markup = $this->renderer->render($view);
        }
        if ('mjml' == $extension) {
            $markup = file_get_contents($templatePath);
        }
        return $this->renderMjml($markup, (array) $view->getVariables());
    }

    /**
     * Sends provided message using the pre-defined transport adapter
     *
     * @param  Zend\Mail\Message $email
     * @throws Zend\Mail\Exception\RuntimeException
     * @return null
     */
    public function sendEmail(Message $email)
    {
        return $this->transport->send($email);
    }

    /**
     * Converts provided MJML markup to HMTL
     *
     * @param  string $mjml
     * @param  boolean $saveTemplate
     * @throws GuzzleHttp\Exception\TransferException
     * @return string|bool
     */
    protected function mjmlToHtml($mjml, $saveTemplate = false)
    {
        try {
            $result = $this->client->post('render', [
                'body' => [
                    'src' => $mjml,
                    'save' => $saveTemplate,
                ],
            ])->json();
            if (!isset($result['result'])) {
                return false;
            }
            return $result['result'];
        } catch (TransferException $e) {
            throw $e;
        }
        return false;
    }

    /**
     * Replaces variables in the provided template string7
     *
     * When the passed variables contains objects the ones that implement __toString
     * method will be replaced in the final content
     *
     * @param  string $template
     * @param  array $variables
     * @return string
     */
    protected function replaceVariables($template, array $variables = [])
    {
        $data = [];
        foreach ($variables as $name => $value) {
            if (is_scalar($value)) {
                $data["{{ $name }}"] = $value;
            }
            if (is_object($value) && method_exists($value, '__toString')) {
                $data["{{ $name }}"] = (string) $value;
            }
        }
        return str_replace(array_keys($data), array_values($data), $template);
    }
}
