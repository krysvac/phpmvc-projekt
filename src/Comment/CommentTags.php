<?php

namespace Anax\Comment;

/**
 * Model for Comment.
 *
 */
class CommentTags extends \Anax\MVC\CDatabaseModel
{

    public function setup()
    {
    }

    private function getCount($tag)
    {
        $this->db->select('*')
            ->from('phpmvc10_comments');

        $this->db->execute();

        $allComments = $this->db->fetchAll();
        $count = 0;

        foreach ($allComments as $comment) {
            $tags = explode(",", $comment->tags);

            if (in_array(strtolower($tag), $tags)) {
                $count++;
            }
        }
        return $count;
    }

    public function getAllTags()
    {
        $this->db->select()
            ->from('phpmvc10_comments');

        $this->db->execute();

        $allTags = $this->db->fetchAll();

        $allTagsArray = array();

        foreach ($allTags as $tags) {
            if (explode(",", $tags->tags) != "" && $tags->type == "question") {
                $allTagsArray = array_merge($allTagsArray, explode(",", $tags->tags));
            }
        }

        return array_unique($allTagsArray);
    }

    public function getAllTagsForQuestion($id)
    {
        $this->db->select()
            ->from('phpmvc10_comments')->where("id = ?");

        $this->db->execute([$id]);

        $allTags = $this->db->fetchAll();

        $allTagsArray = array();

        foreach ($allTags as $tags) {
            if (explode(",", $tags->tags) != "" && $tags->type == "question") {
                $allTagsArray = array_merge($allTagsArray, explode(",", $tags->tags));
            }
        }

        return array_unique($allTagsArray);
    }

    public function getMostCommon($amount)
    {
        $this->db->select()
            ->from('phpmvc10_comments');

        $this->db->execute();

        $allTags = $this->db->fetchAll();

        $allTagsArray = array();

        foreach ($allTags as $tags) {
            if (explode(",", $tags->tags) != "" && $tags->type == "question") {
                $allTagsArray = array_merge($allTagsArray, explode(",", $tags->tags));
            }
        }

        $amounts = array_count_values($allTagsArray);
        
        asort($amounts);
        $amounts = array_reverse($amounts, true);
        $ret = array_slice($amounts, 0, $amount);

        return $ret;
    }
}
