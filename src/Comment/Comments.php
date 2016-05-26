<?php

namespace Anax\Comment;

class Comments extends \Anax\MVC\CDatabaseModel
{
    public function setup()
    {
        $this->db->dropTableIfExists("phpmvc10_comments")->execute();

        $this->db->createTable(
            "phpmvc10_comments",
            [
                "id"      => ["integer", "primary key", "not null", "auto_increment"],
                "creator" => ["integer"],
                "created" => ["datetime"],
                "updated" => ["datetime"],
                "deleted" => ["datetime"],
                "title"   => ["varchar(150)"],
                "content" => ["text"],
                "type"    => ["varchar(80)"],
                "parent"  => ["integer"],
                "tags"    => ["text"],
            ]
        )->execute();

        $this->db->insert(
            "phpmvc10_comments",
            [
                "title",
                "content",
                "creator",
                "created",
                "type",
                "tags"
            ]
        );

        $now = date("Y-m-d H:i:s");

        $this->db->execute([
            "Varför släpper inte Valve HL3?",
            "Jag tycker att valve är jättebra, men när kommer de släppa spelet??",
            1,
            $now,
            "question",
            "valve,half-life,spel",
        ]);
        $this->db->execute([
            "Varför är Mario Italiensk?",
            "Företaget och personen som kom på idén är Japanska, borde inte han vara Japansk då?",
            2,
            $now,
            "question",
            "nintendo,super mario,italiensk,japansk",
        ]);
        $this->db->execute([
            "Hur mycket pengar har Notch tjänat?",
            "Har precis sett att han sålt mojang, men hur mycket fick han för det?",
            3,
            $now,
            "question",
            "minecraft,notch,pengar",
        ]);
        $this->db->execute([
            "När kommer nästa Call Of Duty?",
            "Hörde att det ska komma ett nytt, men när?",
            4,
            $now,
            "question",
            "cod,nytt spel, activision",
        ]);

        $this->db->insert(
            "phpmvc10_comments",
            [
                "creator",
                "content",
                "created",
                "parent",
                "type"
            ]
        );

        $this->db->execute([4, "De vill inte", $now, 1, "answer"]);
        $this->db->execute([3, "Bra fråga, de ville väl ha det så.", $now, 2, "answer"]);
        $this->db->execute([2, "Ca 4,5 - 5 miljarder kronor", $now, 3, "answer"]);
        $this->db->execute([1, "Senare.", $now, 4, "answer"]);
        $this->db->execute([3, "Eller?", $now, 2, "comment"]);
        $this->db->execute([2, "Innan skatt ca 15 miljarder", $now, 3, "comment"]);
        $this->db->execute([1, "Kanske snart? ;)", $now, 4, "comment"]);
    }

    private function getVotesForComment($id)
    {
        $this->db->select()->from("phpmvc10_comments_activity")->where("comment = ?");
        $this->db->execute([$id]);
        $this->db->setFetchModeClass(__CLASS__);
        return $this->db->fetchAll();
    }

    private function getScoreForComment($id)
    {
        $score = 0;
        $votes = $this->getVotesForComment($id);
        foreach ($votes as $vote) {
            if ($vote->type == "up") {
                $score = $score + 1;
            } elseif ($vote->type == "down") {
                $score = $score - 1;
            }
        }
        return $score;
    }

    private function addScore($comment)
    {
        $comment->score = $this->getScoreForComment($comment->id);
    }

    private function addInfo($comment)
    {
        if ($comment->type == "answer") {
            $parent               = $this->find($comment->parent);
            $comment->parentTitle = $parent->title;
        } elseif ($comment->type == "comment") {
            $parent = $this->find($comment->parent);

            if ($parent->type == "answer") {
                $parent = $this->find($parent->parent);
            }

            $comment->contentShort = substr($comment->content, 0, 64) . " ...";
            $comment->topParent    = $parent->id;
            $comment->parentTitle  = $parent->title;
        }
    }

    public function find($id)
    {
        $this->db->select()->from("phpmvc10_comments")->where("id = ?");
        $this->db->execute([$id]);
        $comment = $this->db->fetchAll()[0];
        $this->addScore($comment);
        return $comment;
    }

    public function getCommentsByType($type)
    {
        $this->db->select()->from("phpmvc10_comments")->where("type = ?")->orderBy("created DESC");
        $this->db->execute([$type]);
        $comments = $this->db->fetchAll();
        foreach ($comments as $comment) {
            $comment = $this->addScore($comment);
        }
        return $comments;
    }

    public function getCommentsByUser($id, $type = null)
    {
        $query  = "creator = ?";
        $params = [$id];
        if ($type != null) {
            $query .= " and type = ?";
            array_push($params, $type);
        }
        $this->db->select()->from("phpmvc10_comments")->where($query);
        $this->db->execute($params);
        $this->db->setFetchModeClass(__CLASS__);
        $comments = $this->db->fetchAll();
        foreach ($comments as $comment) {
            $this->addScore($comment);
            $this->addInfo($comment);
        }
        return $comments;
    }

    public function sortComments($comments, $key)
    {
        if ($key == "score") {
            usort($comments, function ($a, $b) {
                return $b->score - $a->score;
            });
            foreach ($comments as $comment) {
                if ($this->checkCommentAccepted($comment->id)) {
                    $topComment = $comment;
                    $index = array_search($comment, $comments);
                    unset($comments[$index]);
                    array_unshift($comments, $topComment);
                }
            }
        }
        return $comments;
    }

    public function getNewest($type, $amount)
    {
        $this->db->select()->from("phpmvc10_comments")->where("type = ?")->orderBy("created DESC")->limit($amount);
        $this->db->execute([$type]);
        $this->db->setFetchModeClass(__CLASS__);
        return $this->db->fetchAll();
    }

    public function getResponseToQuestion($qid, $type)
    {
        $this->db->select()->from("phpmvc10_comments")->where("parent = ? and type = ?");
        $this->db->execute([$qid, $type]);
        $this->db->setFetchModeClass(__CLASS__);
        $comments = $this->db->fetchAll();
        foreach ($comments as $comment) {
            $this->addScore($comment);
            if ($comment->type == "answer") {
                if ($this->checkCommentAccepted($comment->id)) {
                    $comment->accepted = "yes";
                } else {
                    $comment->accepted = "no";
                }
            }
        }
        return $comments;
    }

    public function commentHasParent($id)
    {
        $parent = $this->getParentId($id);
        if ($parent == null) {
            return false;
        }
        return true;
    }

    public function getParentId($id)
    {
        $this->db->select()->from("phpmvc10_comments")->where("id = ?");
        $this->db->execute([$id]);
        return $this->db->fetchAll()[0]->parent;
    }

    private function getAnswerIdsFromQuestion($id)
    {
        $ids = array();
        $this->db->select()->from("phpmvc10_comments")->where("parent = ? and type = ?");
        $this->db->execute([$id, "answer"]);
        $this->db->setFetchModeClass(__CLASS__);
        $answers = $this->db->fetchAll();
        foreach ($answers as $answer) {
            array_push($ids, $answer->id);
        }
        return $ids;
    }

    public function getAmountOfAnswerToQuestion($id)
    {
        return count($this->getAnswerIdsFromQuestion($id));
    }

    private function checkCommentAccepted($id)
    {
        $this->db->select()->from("phpmvc10_comments_activity")->where("comment = ? and type = ?");
        $this->db->execute([$id, "accept"]);
        $res = $this->db->fetchAll();
        if (count($res) > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function questionHasAcceptedAnswer($id)
    {
        $answers = $this->getAnswerIdsFromQuestion($id);
        foreach ($answers as $aid) {
            if ($this->checkCommentAccepted($aid)) {
                return true;
            }
        }
        return false;
    }

    private function getAcceptedAnswerIdFromQuestion($id)
    {
        $answers = $this->getAnswerIdsFromQuestion($id);
        foreach ($answers as $aid) {
            if ($this->checkCommentAccepted($aid)) {
                return $aid;
            }
        }
        return -1;
    }

    private function saveDeAccept($id)
    {
        $this->db->delete(
            "phpmvc10_comments_activity",
            "comment = ? and type = ?"
        );
        $this->db->execute([$id, "accept"]);
    }

    private function saveAccept($id, $uid)
    {
        $now = gmdate("Y-m-d H:i:s");
        $this->db->insert(
            "phpmvc10_comments_activity",
            [
                "comment",
                "user",
                "type",
                "timestamp"
            ]
        );
        $this->db->execute([$id, $uid, "accept", $now]);
    }

    public function accept($aid, $uid)
    {
        if ($this->checkCommentAccepted($aid)) {
            $this->saveDeAccept($aid);
        } else {
            $parent = $this->find($aid)->parent;
            if ($this->questionHasAcceptedAnswer($parent)) {
                $acceptedAnswer = $this->getAcceptedAnswerIdFromQuestion($parent);
                $this->saveDeAccept($acceptedAnswer);
            }
            $this->saveAccept($aid, $uid);
        }
    }

    public function getQuestionsByTag($tag)
    {
        $questions = array();

        $this->db->select()->from("phpmvc10_comments");
        $this->db->execute();

        $result = $this->db->fetchAll();

        foreach ($result as $row) {
            $tags = explode(",", $row->tags);

            if (in_array(strtolower($tag), $tags)) {
                $this->addScore($row);
                $questions[] = $row;
            }
        }
        return $questions;
    }

    private function commentHasTags($id)
    {
        $this->db->select()->from("phpmvc10_comments")->where("tags IS NOT NULL");
        $this->db->execute();
        $result = $this->db->fetchAll();
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function saveComment($values = [])
    {
        $this->db->insert(
            "phpmvc10_comments",
            [
                "title",
                "content",
                "creator",
                "created",
                "type",
                "tags"
            ]
        );

        $this->db->execute([
            $values["title"],
            $values["content"],
            $values["creator"],
            $values["created"],
            $values["type"],
            $values["tags"],
        ]);

        return true;
    }

    public function saveCommentA($values = [])
    {
        $this->db->insert(
            "phpmvc10_comments",
            [
                "content",
                "creator",
                "parent",
                "created",
                "type"
            ]
        );

        $this->db->execute([
            $values["content"],
            $values["creator"],
            $values["parent"],
            $values["created"],
            $values["type"],
        ]);

        return true;
    }

    public function updateQuestion($values = [])
    {
        $this->db->update(
            'phpmvc10_comments',
            [
                'title'   => $values["title"],
                'content' => $values["content"],
                'tags'    => $values["tags"],
            ],
            "id = " . $values["id"] . ""
        );

        $this->db->execute();

        return true;
    }

    public function updateComment($values = [])
    {
        $this->db->update(
            'phpmvc10_comments',
            [
                'content' => $values["content"],
            ],
            "id = " . $values["id"] . ""
        );

        $this->db->execute();

        return true;
    }
}
