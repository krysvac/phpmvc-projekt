<?php

namespace Anax\Users;

/**
 * Model for Users.
 *
 */
class User extends \Anax\MVC\CDatabaseModel
{
    public function setup()
    {
        $this->db->dropTableIfExists('phpmvc10_user')->execute();

        $this->db->createTable(
            'phpmvc10_user',
            [
                'id'       => ['integer', 'primary key', 'not null', 'auto_increment'],
                'username' => ['varchar(20)', 'unique', 'not null'],
                'email'    => ['varchar(80)'],
                'name'     => ['varchar(80)'],
                'location' => ['varchar(80)'],
                'about'    => ['text'],
                'password' => ['varchar(255)'],
                'created'  => ['datetime'],
                'updated'  => ['datetime'],
                'active'   => ['datetime'],
            ]
        )->execute();

        $this->db->insert(
            'phpmvc10_user',
            ['username', 'email', 'name', 'password', 'created', 'active', 'location', 'about']
        );

        $now = gmdate('Y-m-d H:i:s');

        $this->db->execute([
            'admin',
            'admin@example.com',
            'Administrator',
            password_hash('admin', PASSWORD_BCRYPT),
            $now,
            $now,
            'Stockholm, Sverige',
            "Tillfällig text som tas bort senare kanske",
        ]);

        $this->db->execute([
            'user1',
            'user1@example.com',
            'Barack Obama',
            password_hash('user1', PASSWORD_BCRYPT),
            $now,
            $now,
            'Hawaii, USA',
            "What's obamas last name?",
        ]);

        $this->db->execute([
            'user2',
            'user2@example.com',
            'Stefan Löfven',
            password_hash('user2', PASSWORD_BCRYPT),
            $now,
            $now,
            'Välfärd, Sverige',
            "Sitta nära varann på bussen. Nej tack!",
        ]);

        $this->db->execute([
            'user3',
            'user3@example.com',
            'Gudrun Schyman',
            password_hash('user3', PASSWORD_BCRYPT),
            $now,
            $now,
            'Säker zon, Sverige',
            "Nu förtrycker du mig!",
        ]);
    }

    public function login($data)
    {
        $this->db->select()->from('phpmvc10_user')->where("username = ?");
        $this->db->execute([$data["username"]]);
        $this->db->setFetchModeClass(__CLASS__);
        $user                 = $this->db->fetchAll()[0];
        $userInfo["id"]       = $user->id;
        $userInfo["username"] = $user->username;
        if (password_verify($data["password"], $user->password)) {
            $this->session->set('user', $userInfo);
            return true;
        } else {
            return false;
        }
    }

    private function getGravatarUrl($mail, $size = null)
    {
        $hash = md5(strtolower(trim($mail)));
        $link = "http://www.gravatar.com/avatar/" . $hash . "?d=identicon";
        if (!is_null($size)) {
            $link .= "&s=" . $size;
        }
        return $link;
    }

    public function setGravatarSize($user, $size)
    {
        $user->gravatar = $this->getGravatarUrl($user->email, $size);
    }

    public function getContributionsAmount($id)
    {
        return count($this->comment->getCommentsByUser($id));
    }

    private function getScoreOfContributions($id)
    {
        $score    = 0;
        $comments = $this->comment->getCommentsByUser($id);
        foreach ($comments as $comment) {
            $score += $comment->score;
        }
        return $score;
    }

    private function getScoreForCreations($id)
    {
        $score    = 0;
        $comments = $this->comment->getCommentsByUser($id);
        foreach ($comments as $comment) {
            if ($comment->type == "question") {
                $score = $score + 4;
            } elseif ($comment->type == "answer") {
                $score = $score + 2;
            } elseif ($comment->type == "comment") {
                $score = $score + 1;
            }
        }
        return $score;
    }

    private function getAcceptedAnswersScore($id)
    {
        $score  = 0;
        $amount = 0;
        $this->db->select()->from('phpmvc10_comments')->where('creator = ? and type = ?');
        $this->db->execute([$id, 'answer']);
        $answersByUser = $this->db->fetchAll();
        foreach ($answersByUser as $answer) {
            $this->db->select()->from('phpmvc10_comments_activity')->where('comment = ? and type = ?');
            $this->db->execute([$answer->id, 'accept']);
            $accepts = $this->db->fetchAll();
            $amount  = $amount + count($accepts);
        }
        $extra = $amount * 4;
        return $score + $extra;
    }

    private function getScoreForAcceptingAnswers($id)
    {
        $score = 0;
        $this->db->select()->from('phpmvc10_comments_activity')->where('user = ? and type = ?');
        $this->db->execute([$id, 'accept']);
        $answers = $this->db->fetchAll();
        $extra   = count($answers) * 3;
        return $score + $extra;
    }

    private function getReputation($id)
    {
        $this->comment = new \Anax\Comment\Comments();
        $this->comment->setDI($this->di);
        $reputation = 0;
        $reputation += $this->getScoreForCreations($id);
        $reputation += $this->getScoreOfContributions($id);
        $reputation += $this->getAcceptedAnswersScore($id);
        $reputation += $this->getScoreForAcceptingAnswers($id);
        return $reputation;
    }

    private function process($data)
    {
        $data->gravatar   = $this->getGravatarUrl($data->email);
        $data->reputation = $this->getReputation($data->id);
        return $data;
    }

    public function find($id)
    {
        $this->db->select()->from("phpmvc10_user")->where("id = ?");
        $this->db->execute([$id]);
        $data = $this->db->fetchAll()[0];

        if ($data != false) {
            $data = $this->process($data);
        }
        return $data;
    }

    public function findAll()
    {
        $this->db->select()->from("phpmvc10_user");
        $this->db->execute();
        $users = $this->db->fetchAll();
        foreach ($users as $user) {
            $user = $this->process($user);
        }
        return $users;
    }

    public function findMostReputation($amount)
    {
        $allUsers = $this->findAll();
        $allUsers = array_slice($allUsers, 0, $amount);

        foreach ($allUsers as $user) {
            $user->count = $this->getReputation($user->id);
        }

        usort($allUsers, function ($a, $b) {
            return $b->count - $a->count;
        });

        return $allUsers;
    }

    public function loggedIn()
    {
        if ($this->session->has("user")) {
            return true;
        } else {
            return false;
        }
    }

    public function getLoggedInName()
    {
        return $this->session->get("user")["username"];
    }

    public function getLoggedInId()
    {
        return $this->session->get("user")["id"];
    }

    public function logOut()
    {
        $this->session->get('user');
        $this->session->set('user', null);
    }

    public function userIsOwner($user, $comment)
    {
        $this->db->select()->from('phpmvc10_comments')->where("id = ?");
        $this->db->execute([$comment]);
        $comment = $this->db->fetchAll()[0];

        if ($comment->creator == $user) {
            return true;
        } else {
            return false;
        }
    }

    public function userIsOwnerOfParent($user, $child)
    {
        $this->db->select()->from('phpmvc10_comments')->where("id = ?");
        $this->db->execute([$child]);
        $childcomment = $this->db->fetchAll()[0];
        $parentid = $childcomment->parent;

        $this->db->select()->from('phpmvc10_comments')->where("id = ?");
        $this->db->execute([$parentid]);
        $parentcomment = $this->db->fetchAll()[0];
        $parentcreator = $parentcomment->creator;
        if ($parentcreator == $user) {
            return true;
        } else {
            return false;
        }
    }

    public function userCanVote($comment, $type, $user)
    {
        $this->activity = new \Anax\Comment\CommentActivity();
        $this->activity->setDI($this->di);
        if ($this->userIsOwner($user, $comment)) {
            return false;
        }
        $userScore = $this->activity->getUsersVotesForComment($comment, $user);
        if ($userScore == 0) {
            return true;
        } elseif ($userScore == 1 && $type == "down") {
            return true;
        } elseif ($userScore == -1 && $type == "up") {
            return true;
        }
        return false;
    }

    public function userCanAccept($comment, $user)
    {
        if ($this->userIsOwnerOfParent($user, $comment)) {
            return true;
        }
        return false;
    }

    public function userCanEdit($user, $comment)
    {
        if ($this->userIsOwner($user, $comment)) {
            return true;
        }
        return false;
    }

    public function userCanEditProfile($user, $profile)
    {
        if ($user == $profile) {
            return true;
        } else {
            return false;
        }
    }

    public function save($values = [])
    {
        $this->db->insert(
            'phpmvc10_user',
            ['username', 'email', 'name', 'password', 'created', 'active']
        );

        $this->db->execute([
            $values["username"],
            $values["email"],
            $values["name"],
            $values["password"],
            $values["created"],
            $values["active"],
        ]);

        return true;
    }

    public function update($values = [])
    {
        $this->db->update(
            'phpmvc10_user',
            [
                'username' => $values["username"],
                'email'    => $values["email"],
                'name'     => $values["name"],
                'location' => $values["location"],
                'about'    => $values["about"],
            ],
            "id = " . $values["id"] . ""
        );

        $this->db->execute();

        return true;
    }
}
