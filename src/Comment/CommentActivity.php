<?php

namespace Anax\Comment;

class CommentActivity extends \Anax\MVC\CDatabaseModel
{

    public function setup()
    {
        $this->db->dropTableIfExists('phpmvc10_comments_activity')->execute();
        $this->db->createTable(
            'phpmvc10_comments_activity',
            [
                'id'        => ['integer', 'primary key', 'not null', 'auto_increment'],
                'comment'   => ['integer'],
                'user'      => ['integer'],
                'type'      => ['varchar(16)'],
                'timestamp' => ['datetime'],
            ]
        )->execute();
    }

    public function getUsersVotesForComment($comment, $user)
    {
        $score = 0;
        $this->db->select()->from('phpmvc10_comments_activity')->where('comment = ? and user = ?');
        $this->db->execute([$comment, $user]);
        $result = $this->db->fetchAll();
        foreach ($result as $row) {
            if ($row->type == "up") {
                $score++;
            } elseif ($row->type == "down") {
                $score--;
            }
        }
        return $score;
    }

    public function vote($comment, $user, $type)
    {
        $now = gmdate('Y-m-d H:i:s');
        $this->db->insert(
            'phpmvc10_comments_activity',
            [
                'comment',
                'user',
                'type',
                'timestamp'
            ]
        );
        $this->db->execute([$comment, $user, $type, $now]);
    }
}
