<?php

namespace Anax\Users;

/**
 * Model for Users.
 *
 */
class UsersController implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;

    public function initialize()
    {
        $this->comment = new \Anax\Comment\Comments();
        $this->comment->setDI($this->di);
        $this->tag = new \Anax\Comment\CommentTags();
        $this->tag->setDI($this->di);
        $this->user = new \Anax\Users\User();
        $this->user->setDI($this->di);
        $this->activity = new \Anax\Comment\CommentActivity();
        $this->activity->setDI($this->di);
    }

    public function setupAction()
    {
        $this->user->setup();
        $this->comment->setup();
        $this->tag->setup();
        $this->activity->setup();
        $this->theme->setTitle("Återställ databas");
        $this->views->add('users/page', [
            'title'   => "Återställ databas",
            'content' => "Databasen har återställts",
        ]);
    }

    /**
     * List all users.
     *
     * @return void */
    public function listAction()
    {
        $allUsers = $this->user->findAll();
        $this->theme->setTitle("Alla användare");
        $this->views->add('users/list-all', [
            'users'   => $allUsers,
            'title'   => "Alla användare",
            'profile' => $this->url->create("users/id"),
        ], 'flash');
    }

    public function viewMostReputationAction($amount, $where)
    {
        $users = $this->user->findMostReputation($amount);
        $this->views->add('users/minimal', [
            'users' => $users,
            'url'   => $this->url->create('users/id'),
            'title' => "Högst rankade användare",
        ], $where);
    }

    private function prepare($user)
    {
        $user->about    = $this->textFilter->doFilter($user->about, "shortcode, markdown");
        return $user;
    }

    public function idAction($id = null)
    {
        $user = $this->user->find($id);
        if ($user != false) {
            $user = $this->prepare($user);
            $this->theme->setTitle("Användardetaljer");
            $this->user->setGravatarSize($user, 160);

            $profileUrl = null;
            if ($this->user->userCanEditProfile($this->user->getLoggedInId(), $id)) {
                    $profileUrl = $this->url->create("users/edit");
            }

            $this->views->add('users/view', [
                'user'       => $user,
                'profileUrl' => $profileUrl,
            ], 'featured-1');

            $this->views->add('users/about', [
                'user' => $user,
            ], 'featured-2');

            $contributionsAmount = $this->user->getContributionsAmount($id);
            $questions           = $this->comment->getCommentsByUser($id, "question");
            $answers             = $this->comment->getCommentsByUser($id, "answer");
            $comments            = $this->comment->getCommentsByUser($id, "comment");

            $this->views->add('users/activity', [
                'user'         => $user,
                'count'        => $contributionsAmount,
                'questions'    => count($questions),
                'answers'      => count($answers),
                'comments'     => count($comments),
            ], 'featured-3');

            $this->views->add('users/activity-lists', [
                'questions'  => $questions,
                'answers'    => $answers,
                'comments'   => $comments,
                'commentUrl' => $this->url->create("comment/id"),
            ], 'full');

        } else {
            $this->theme->setTitle("Användare ej hittad");
            $this->views->add('default/error', [
                'title'   => "Användare kunde inte hittas",
                'content' => "En användare med id du valt finns inte",
            ], 'main');
        }
    }

    public function editAction($id = null)
    {
        $this->theme->setTitle("Redigera profil");
        if ($this->user->loggedIn()) {
            $thisUser = null;
            if ($id == null) {
                $thisUser = $this->user->find($this->user->getLoggedInId());
            } elseif ($id == $this->user->getLoggedInId()) {
                $thisUser = $this->user->find($this->user->getLoggedInId());
            } else {
                $this->theme->setTitle("Ej tillåtet");
                $this->views->add('default/error', [
                    'title'   => "Du har inte tillåtelse att redigera profilen",
                    'content' => "Profilen du valt kan inte redigeras av det aktuella kontot",
                ], 'main');
                return;
            }

            if ($thisUser != null) {
                $this->di->session();
                $form = new \Anax\HTMLForm\CFormEditProfile($thisUser);
                $form->setDI($this->di);
                $form->check();
                $this->views->add('users/page', [
                    'title'   => "Redigera profil",
                    'content' => $form->getHTML(),
                ]);
            } else {
                $this->theme->setTitle("Användare ej hittad");
                $this->views->add('default/error', [
                    'title'   => "Användare kunde inte hittas",
                    'content' => "En användare med id du valt finns inte",
                ], 'main');
            }
        }
    }

    public function registerAction()
    {
        $url = $this->url->create('users/id');
        $this->di->session();
        $form = new \Anax\HTMLForm\CFormAddUser();
        $form->setDI($this->di);
        $form->check();
        $this->theme->setTitle("Skapa konto");
        $this->views->add('users/page', [
            'title'   => "Skapa konto",
            'content' => $form->getHTML(),
        ]);
    }

    public function loginAction()
    {
        $this->di->session();
        $form = new \Anax\HTMLForm\CFormLogin();
        $form->setDI($this->di);
        $form->check();
        $this->theme->setTitle("Logga in");
        $this->views->add('users/page', [
            'title'   => "Logga in",
            'content' => $form->getHTML(),
        ]);
    }

    public function logoutAction()
    {
        $this->user->logout();
        $this->response->redirect($this->url->create(""));
    }
}
