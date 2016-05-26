<?php

namespace Anax\Comment;

/**
 * To attach comments-flow to a page or some content.
 *
 */
class CommentController implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;

    public function initialize()
    {
        $this->comment = new \Anax\Comment\Comments();
        $this->comment->setDI($this->di);
        $this->user = new \Anax\Users\User();
        $this->user->setDI($this->di);
        $this->tag = new \Anax\Comment\CommentTags();
        $this->tag->setDI($this->di);
        $this->activity = new \Anax\Comment\CommentActivity();
        $this->activity->setDI($this->di);
    }

    public function editAction($id)
    {
        if ($this->user->userCanEdit($this->user->getLoggedInId(), $id)) {
            $comment = $this->comment->find($id);
            $type    = $comment->type;
            $form = "";
            if ($type == "question") {
                $form    = new \Anax\HTMLForm\CFormEditQuestion($comment);
            } elseif ($type == "comment") {
                $parent = $this->comment->getParentId($id);
                if ($this->comment->commentHasParent($parent)) {
                    $parent = $this->comment->getParentId($parent);
                }
                $form = new \Anax\HTMLForm\CFormEditComment($comment, $parent);
            } elseif ($type == "answer") {
                $parent = $this->comment->getParentId($id);
                $form     = new \Anax\HTMLForm\CFormEditAnswer($comment, $parent);
            }
            $form->setDI($this->di);
            $form->check();
            $this->di->theme->setTitle("Redigera");
            $this->di->views->add('comment/add', [
                'content' => $form->getHTML(),
                'title'   => "Redigera",
            ]);
        } else {
            $url = $this->url->create("");
            $this->response->redirect($url);
        }
    }

    public function viewAction()
    {
        if ($this->user->loggedIn()) {
            $this->views->add('comment/banner', [
                'url' => $this->url->create('comment/add'),
            ], 'flash');
        }
        $allComments = $this->comment->getCommentsByType('question');

        foreach ($allComments as $comment) {
            $user            = $this->user->find($comment->creator);
            $amountOfAnswers = $this->comment->getAmountOfAnswerToQuestion($comment->id);
            $this->views->add('comment/comments', [
                'comment'         => $comment,
                'amountOfAnswers' => $amountOfAnswers,
                'tags'            => $this->tag->getAllTagsForQuestion($comment->id),
                'taggedUrl'       => $this->url->create("comment/tagged"),
                'timestamp'       => $comment->created,
                'user'            => json_decode(json_encode($user), true),
                'userUrl'         => $this->url->create("users/id"),
                'commentUrl'      => $this->url->create("comment/id"),
            ], 'main');
        }
    }

    public function viewNewestAction($type, $amount, $where)
    {
        $new   = $this->comment->getNewest($type, $amount);
        $url   = $this->url->create("comment/id");
        $this->views->add('comment/minimal', [
            'comments' => $new,
            'title'    => "Nya frågor",
            'url'      => $url,
        ], $where);
    }

    public function viewMostCommonTagsAction($amount, $where)
    {
        $tags = $this->tag->getMostCommon($amount);
        $this->views->add('comment/tag/minimal', [
            'tags'  => $tags,
            'url'   => $this->url->create("comment/tagged"),
            'title' => "Mest populära taggar",
        ], $where);
    }

    private function displayComment($comment)
    {
        $comment->content = $this->textFilter->doFilter($comment->content, 'shortcode, markdown');
        $user             = $this->user->find($comment->creator);
        
        $editUrl = null;
        if ($this->user->userCanEdit($this->user->getLoggedInId(), $comment->id)) {
            $editUrl = $this->url->create("comment/edit");
        }

        $this->views->add('comment/comment', [
            'id'           => $comment->id,
            'comment'      => $comment,
            'editUrl'      => $editUrl,
            'user'         => json_decode(json_encode($user), true),
            'userUrl'      => $this->url->create("users/id"),
            'voteUrl'      => $this->url->create("comment/v"),
            'timestamp'    => $comment->created,
        ]);
    }

    public function idAction($id = null)
    {
        $sort = null;
        if (isset($_GET["sort"]) && !empty($_GET["sort"])) {
            $sort = htmlspecialchars($_GET["sort"]);
        }

        $comment = $this->comment->find($id);
        $tags    = $this->tag->getAllTagsForQuestion($id);
        if ($comment->type == "question") {
            $editUrl = null;
            if ($this->user->userCanEdit($this->user->getLoggedInId(), $id)) {
                $editUrl = $this->url->create("comment/edit");
            }
            $this->theme->setTitle($comment->title);
            $user               = $this->user->find($comment->creator);
            $this->views->add('comment/question', [
                'commentUrl'    => $this->url->create("comment/comment"),
                'editUrl'       => $editUrl,
                'question'      => $this->textFilter->doFilter($comment->content, 'shortcode, markdown'),
                'questionId'    => $comment->id,
                'questionTitle' => htmlspecialchars($comment->title),
                'score'         => $comment->score,
                'taggedUrl'     => $this->url->create("comment/tagged"),
                'tags'          => $tags,
                'title'         => "Visa tråd",
                'timestamp'     => $comment->created,
                'user'          => json_decode(json_encode($user), true),
                'userUrl'       => $this->url->create("users/id"),
                'voteUrl'       => $this->url->create("comment/v"),
            ]);

            $comments = $this->comment->getResponseToQuestion($id, 'comment');
            foreach ($comments as $comment) {
                $this->displayComment($comment);
            }

            $answers            = $this->comment->getResponseToQuestion($id, 'answer');
            $answers            = $this->comment->sortComments($answers, $sort);
            $amountOfAnswers    = count($answers);

            $this->views->add('comment/answersHeader', [
                'amountOfAnswers'   => $amountOfAnswers,
                'sortByTime'        => $this->request->getCurrentUrl(false) . "?sort=time",
                'sortByScore'       => $this->request->getCurrentUrl(false) . "?sort=score",
                'sortByTimeClass'   => ($sort == "time" ? 'selected' : ''),
                'sortByScoreClass'  => ($sort == "score" ? 'selected' : ''),
            ]);

            foreach ($answers as $answer) {
                $user = $this->user->find($answer->creator);
                
                $acceptUrl = null;
                if ($this->user->getLoggedInId() == $comment->creator) {
                    $acceptUrl = $this->url->create("comment/a");
                }

                $editUrl = null;
                if ($this->user->userCanEdit($this->user->getLoggedInId(), $answer->id)) {
                    $editUrl = $this->url->create("comment/edit");
                }

                $this->views->add('comment/answer', [
                    'acceptUrl'     => $acceptUrl,
                    'accepted'      => $answer->accepted,
                    'answerId'      => $answer->id,
                    'answerContent' => $this->textFilter->doFilter($answer->content, 'shortcode, markdown'),
                    'editUrl'       => $editUrl,
                    'user'          => json_decode(json_encode($user), true),
                    'userUrl'       => $this->url->create("users/id"),
                    'commentUrl'    => $this->url->create("comment/comment"),
                    'score'         => $answer->score,
                    'voteUrl'       => $this->url->create("comment/v"),
                    'timestamp'     => $answer->created,
                ]);

                $comments = $this->comment->getResponseToQuestion($answer->id, 'comment');
                foreach ($comments as $comment) {
                    $this->displayComment($comment);
                }
            }

            if ($this->user->loggedIn()) {
                $form = new \Anax\HTMLForm\CFormAddAnswer([$this->user->getLoggedInId(), $id]);
                $form->setDI($this->di);
                $form->check();
                $this->di->views->add('comment/add', [
                    'content' => $form->getHTML(),
                    'title'   => "Svara på frågan",
                ]);
            } else {
                $this->views->add('default/error', [
                    'title'   => "Inte inloggad",
                ]);
            }
        }
    }

    public function commentAction($id)
    {
        if ($this->user->loggedIn()) {
            $comment = $this->comment->find($id);
            $redirect = "";
            if ($comment->type == "question") {
                $redirect = $this->url->create("comment/id/" . $id);
            } else {
                $redirect = $this->url->create("comment/id/" . $this->comment->getParentId($id));
            }

            $form = new \Anax\HTMLForm\CFormAddComment($this->user->getLoggedInId(), $id, $redirect);
            $form->setDI($this->di);
            $form->check();

            $this->theme->setTitle($comment->title);
            $tags               = $this->tag->getAllTagsForQuestion($id);
            $user               = $this->user->find($comment->creator);
            $answers            = $this->comment->getResponseToQuestion($id, 'answer');

            $this->views->add('comment/question', [
                'commentUrl'    => $this->url->create("comment/comment"),
                'question'      => $this->textFilter->doFilter($comment->content, 'shortcode, markdown'),
                'questionId'    => $comment->id,
                'questionTitle' => htmlspecialchars($comment->title),
                'score'         => $comment->score,
                'taggedUrl'     => $this->url->create("comment/tagged"),
                'tags'          => $tags,
                'title'         => "Visa tråd",
                'timestamp'     => $comment->created,
                'user'          => json_decode(json_encode($user), true),
                'userUrl'       => $this->url->create("users/id"),
                'voteUrl'       => $this->url->create("comment/v"),
            ]);

            $comments = $this->comment->getResponseToQuestion($id, 'comment');
            foreach ($comments as $comment) {
                $this->displayComment($comment);
            }

            $this->views->add('comment/add', [
                'content' => $form->getHTML(),
                'title'   => "Lägg till kommentar",
            ]);
        } else {
            $url = $this->url->create("users/login");
            $this->response->redirect($url);
            return;
        }
    }

    public function taggedAction($tag)
    {
        $tag = urldecode($tag);

        $questions = $this->comment->getQuestionsByTag($tag);

        $this->views->add('comment/tag/tagHeading', [
            'title'       => "Taggade frågor",
            'tag'         => $tag,
            'description' => "",
        ]);

        foreach ($questions as $question) {
            $user = $this->user->find($question->creator);
            $amountOfAnswers = $this->comment->getAmountOfAnswerToQuestion($question->id);
            $this->views->add('comment/comments', [
                'comment'         => $question,
                'user'            => json_decode(json_encode($user), true),
                'amountOfAnswers' => $amountOfAnswers,
                'userUrl'         => $this->url->create("users/id"),
                'commentUrl'      => $this->url->create("comment/id"),
                'taggedUrl'       => $this->url->create("comment/tagged"),
                'tags'            => $tags = $this->tag->getAllTagsForQuestion($question->id),
                'timestamp'       => $question->created,
                'title'           => "Frågor taggade: " . $tag,
            ], 'main');
        }
        $this->di->theme->setTitle("Frågor taggade med: ". $tag);
    }

    public function addAction()
    {
        if ($this->user->loggedIn()) {
            $id   = $this->user->getLoggedInId();
            $tags = $this->tag->getAllTags();
            $this->di->session();
            $form = new \Anax\HTMLForm\CFormAddQuestion($id, $tags);
            $form->setDI($this->di);
            $form->check();
            $this->di->theme->setTitle("Ställ en fråga");
            $this->di->views->add('comment/add', [
                'content' => $form->getHTML(),
                'title'   => "Ställ en fråga",
            ]);
        } else {
            $url = $this->url->create("users/login");
            $this->response->redirect($url);
            return;
        }
    }

    public function viewTagsAction()
    {
        $allTags = $this->tag->getMostCommon(10000);
        $this->di->views->add('comment/tag/overview', [
            'tags'  => $allTags,
            'url'   => $this->url->create("comment/tagged"),
            'title' => "Alla taggar",
        ], 'flash');
    }

    public function vAction($id, $type)
    {
        if ($this->user->loggedIn() && $this->user->userCanVote($id, $type, $this->user->getLoggedInId())) {
            $this->activity->vote($id, $this->user->getLoggedInId(), $type);
        }
        $this->response->redirect($_SERVER['HTTP_REFERER']);
    }

    public function aAction($id)
    {
        if ($this->user->loggedIn() && $this->user->userCanAccept($id, $this->user->getLoggedInId())) {
            $this->comment->accept($id, $this->user->getLoggedInId());
        }
        $this->response->redirect($_SERVER['HTTP_REFERER']);
    }
}
