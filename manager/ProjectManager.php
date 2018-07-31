<?php

namespace Absolute\Module\Project\Manager;

use Absolute\Core\Manager\BaseManager;
use Absolute\Module\File\Manager\FileManager;
use Absolute\Module\Category\Manager\CategoryManager;
use Absolute\Module\Team\Manager\TeamManager;
use Absolute\Module\Project\Entity\Project;
use Absolute\Module\User\Entity\User;
use Nette\Database\Context;

class ProjectManager extends BaseManager
{

    private $fileManager, $teamManager;
    public function __construct(Context $database, FileManager $fileManager, TeamManager $teamManager, CategoryManager $categoryManager)
    {
        parent::__construct($database);
        $this->fileManager=$fileManager;
        $this->teamManager=$teamManager;
        $this->categoryManager=$categoryManager;
        
    }

    /* INTERNAL METHODS */

    /* INTERNAL/EXTERNAL INTERFACE */

    public function _getById($id)
    {
        $resultDb = $this->database->table('project')->get($id);
        return $this->_getProject($resultDb);
    }

    public function getProject($db)
    {
        return $this->_getProject($db);
    }

    protected function _getProject($db)
    {
        if ($db == false)
        {
            return false;
        }
        $object = new Project($db->id, $db->name, $db->description, $db->status, $db->modules, $db->channel_id, $db->created);
        foreach ($db->related('project_user')->where('role', 'user') as $userDb)
        {
            $user = $this->_getUserShort($userDb->user);
            if ($user)
            {
                $object->addUser($user);
            }
        }
        foreach ($db->related('project_user')->where('role', 'manager') as $userDb)
        {
            $user = $this->_getUserShort($userDb->user);
            if ($user)
            {
                $object->addManager($user);
            }
        }
        foreach ($db->related('project_user')->where('role', 'owner') as $userDb)
        {
            $user = $this->_getUserShort($userDb->user);
            if ($user)
            {
                $object->addOwner($user);
            }
        }
        foreach ($db->related('project_team') as $teamDb)
        {
            $team = $this->teamManager->_getTeam($teamDb->team);
            if ($team)
            {
                $object->addTeam($team);
            }
        }
        foreach ($db->related('project_category') as $categoryDb)
        {
            $category = $this->categoryManager->_getCategory($categoryDb->category);
            if ($category)
            {
                $object->addCategory($category);
            }
        }
        if ($db->ref('file'))
        {
            $object->setImage($this->fileManager->_getFile($db->ref('file')));
        }
        return $object;
    }

    protected function _getUserShort($db)
    {
        if ($db == false)
        {
            return false;
        }
        $object = new User($db->id, $db->username, $db->role, $db->first_name, $db->last_name, $db->email, $db->phone, $db->created);
        if ($db->ref('file'))
        {
            $object->setImage($this->fileManager->_getFile($db->ref('file')));
        }
        return $object;
    }

    private function _getList()
    {
        $ret = array();
        $resultDb = $this->database->table('project')->order('name');
        foreach ($resultDb as $db)
        {
            $object = $this->_getProject($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserList($userId)
    {
        $ret = array();
        $projects = $this->database->query("SELECT project_id AS id FROM project_user WHERE user_id = ? UNION SELECT project_id AS id FROM project_team JOIN team_user WHERE user_id = ? UNION SELECT project_id AS id FROM project_category JOIN category_user WHERE user_id = ?", $userId, $userId, $userId)->fetchPairs("id", "id");
        $resultDb = $this->database->table('project')->where('id', $projects)->order('FIELD(status, "active","paused","canceled","done")');
        foreach ($resultDb as $db)
        {
            $object = $this->_getProject($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserManagerList($userId)
    {
        $ret = array();
        $projects = $this->database->query("SELECT project_id AS id FROM project_user WHERE user_id = ? AND role IN (?)", $userId, array('owner', 'manager'))->fetchPairs("id", "id");
        $resultDb = $this->database->table('project')->where('id', $projects)->order('FIELD(status, "active","paused","canceled","done")');
        foreach ($resultDb as $db)
        {
            $object = $this->_getProject($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _canUserView($id, $userId)
    {
        // Can view based on page assigned to users, teams or categories
        $db = $this->database->query("SELECT project_id AS id FROM project_user WHERE user_id = ? UNION SELECT project_id AS id FROM project_team JOIN team_user WHERE user_id = ? UNION SELECT project_id AS id FROM project_category JOIN category_user WHERE user_id = ?", $userId, $userId, $userId);
        $result = $db->fetchPairs("id", "id");
        if (array_key_exists($id, $result))
        {
            return true;
        }
        return false;
    }

    /* EXTERNAL METHOD */

    public function getById($id)
    {
        return $this->_getById($id);
    }

    public function getList()
    {
        return $this->_getList();
    }

    public function getUserList($userId)
    {
        return $this->_getUserList($userId);
    }

    public function getUserManagerList($userId)
    {
        return $this->_getUserManagerList($userId);
    }

    public function canUserView($id, $userId)
    {
        return $this->_canUserView($id, $userId);
    }

}
