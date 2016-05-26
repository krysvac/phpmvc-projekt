<?php

namespace Anax\HTMLForm;

/**
 * Anax base class for wrapping sessions.
 *
 */
class CFormEditQuestion extends \Mos\HTMLForm\CForm
{
    use \Anax\DI\TInjectionaware,
        \Anax\MVC\TRedirectHelpers;

    private $id;

    /**
     * Constructor
     *
     */
    public function __construct($question)
    {
        $this->id = $question->id;

        parent::__construct([], [
            'title' => [
                'type'       => 'text',
                'label'      => 'Titel: ',
                'required'   => true,
                'validation' => ['not_empty'],
                'value'      => $question->title,
            ],
            'content' => [
                'type'       => 'textarea',
                'label'      => 'Innehåll: ',
                'required'   => true,
                'validation' => ['not_empty'],
                'value'      => $question->content,
            ],
            'tags' => [
                'type'       => 'text',
                'label'      => 'Taggar: ',
                'value'      => $question->tags,
            ],
            'submit' => [
                'type'      => 'submit',
                'callback'  => [$this, 'callbackSubmit'],
            ],
        ]);
    }

    /**
     * Customise the check() method.
     *
     * @param callable $callIfSuccess handler to call if function returns true.
     * @param callable $callIfFail    handler to call if function returns true.
     */
    public function check($callIfSuccess = null, $callIfFail = null)
    {
        return parent::check([$this, 'callbackSuccess'], [$this, 'callbackFail']);
    }

   /**
     * Callback for submit-button.
     *
     */
    public function callbackSubmit()
    {
        $comment = new \Anax\Comment\Comments();
        $comment->setDI($this->di);

        $saved = $comment->updateQuestion([
            'id'      => $this->id,
            'title'   => $this->Value('title'),
            'content' => $this->Value('content'),
            'tags'    => $this->Value('tags'),
        ]);

        if ($saved == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Callback What to do if the form was submitted?
     *
     */
    public function callbackSuccess()
    {
        $this->redirectTo($this->di->url->create("comment/id/" . $this->id));
    }

    /**
     * Callback for submit-button.
     *
     */
    public function callbackSubmitFail()
    {
        $this->AddOutput("<p><i>DoSubmitFail(): Form was submitted but it failed</i></p>");
        return false;
    }

    /**
     * Callback What to do when form could not be processed?
     *
     */
    public function callbackFail()
    {
        $this->AddOutput("<p><i>Form was submitted and the Check() method returned false.</i></p>");
    }
}
