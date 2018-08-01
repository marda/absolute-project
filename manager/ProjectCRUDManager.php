<?php

namespace Absolute\Module\Project\Manager;

use Absolute\Core\Manager\BaseCRUDManager;
use Absolute\Module\File\Manager\FileCRUDManager;
use Absolute\Module\Project\Manager\ProjectManager;
use Nette\Database\Context;

class ProjectCRUDManager extends BaseCRUDManager
{

    private $fileCRUDManager;

    public function __construct(
    Context $database, FileCRUDManager $fileCRUDManager
    )
    {
        parent::__construct($database);
        $this->fileCRUDManager = $fileCRUDManager;
    }

    // OTHER METHODS
    // CONNECT METHODS

    public function connectUsers($users, $managers, $owners, $projectId)
    {
        if($users==null){
            $users=[];
            $u=$this->database->table('project_user')->select('id')->where('project_id', $projectId)->where('role','user');
            foreach ($u as $user)
                $users[]=$user->id;
        }
        if($owners==null){
            $owners=[];
            $o=$this->database->table('project_user')->select('id')->where('project_id', $projectId)->where('role','owner');
            foreach ($o as $owner)
                $owners[]=$owner->id;
        }
        if($managers==null){
            $managers=[];
            $u=$this->database->table('project_user')->select('id')->where('project_id', $projectId)->where('role','manager');
            foreach ($u as $manager)
                $managers[]=$manager->id;
        }
        
        
        $users = array_unique(array_filter($users));
        $managers = array_unique(array_filter($managers));
        $owners = array_unique(array_filter($owners));
        $notDelete = array_merge($users, $managers, $owners);
        $updated = [];
        // DELETE REMOVED
        $this->database->table('project_user')->where('project_id', $projectId)->where('user_id NOT IN ?', $notDelete)->delete();
        // UPDATE EXISTING
        $result = $this->database->table('project_user')->where('project_id', $projectId);
        foreach ($result as $db)
        {
            if ($db->role == "manager" && in_array($db->user_id, $users))
            {
                $this->database->table('project_user')->where('project_id', $projectId)->where('user_id', $db->user_id)->update(array(
                    "role" => "user"
                ));
            }
            if ($db->role == "manager" && in_array($db->user_id, $owners))
            {
                $this->database->table('project_user')->where('project_id', $projectId)->where('user_id', $db->user_id)->update(array(
                    "role" => "owner"
                ));
            }
            if ($db->role == "user" && in_array($db->user_id, $managers))
            {
                $this->database->table('project_user')->where('project_id', $projectId)->where('user_id', $db->user_id)->update(array(
                    "role" => "manager"
                ));
            }
            if ($db->role == "user" && in_array($db->user_id, $owners))
            {
                $this->database->table('project_user')->where('project_id', $projectId)->where('user_id', $db->user_id)->update(array(
                    "role" => "owner"
                ));
            }
            if ($db->role == "owner" && in_array($db->user_id, $users))
            {
                $this->database->table('project_user')->where('project_id', $projectId)->where('user_id', $db->user_id)->update(array(
                    "role" => "user"
                ));
            }
            if ($db->role == "owner" && in_array($db->user_id, $managers))
            {
                $this->database->table('project_user')->where('project_id', $projectId)->where('user_id', $db->user_id)->update(array(
                    "role" => "manager"
                ));
            }
            if (($key = array_search($db->user_id, $managers)) !== false)
            {
                unset($managers[$key]);
            }
            if (($key = array_search($db->user_id, $users)) !== false)
            {
                unset($users[$key]);
            }
            if (($key = array_search($db->user_id, $owners)) !== false)
            {
                unset($owners[$key]);
            }
        }
        // INSERT NEW
        $data = [];
        foreach ($users as $user)
        {
            $data[] = [
                "user_id" => $user,
                "project_id" => $projectId,
                "role" => 'user'
            ];
        }
        foreach ($managers as $user)
        {
            $data[] = [
                "user_id" => $user,
                "project_id" => $projectId,
                "role" => 'manager'
            ];
        }
        foreach ($owners as $user)
        {
            $data[] = [
                "user_id" => $user,
                "project_id" => $projectId,
                "role" => 'owner'
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_user")->insert($data);
        }
        return true;
    }

    public function connectTeams($teams, $projectId)
    {
        $teams = array_unique(array_filter($teams));
        // DELETE
        $this->database->table('project_team')->where('project_id', $projectId)->delete();
        // INSERT NEW
        $data = [];
        foreach ($teams as $team)
        {
            $data[] = [
                "team_id" => $team,
                "project_id" => $projectId,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_team")->insert($data);
        }
        return true;
    }

    public function connectEvents($events, $projectId)
    {
        $events = array_unique(array_filter($events));
        // DELETE
        $this->database->table('project_event')->where('project_id', $projectId)->delete();
        // INSERT NEW
        $data = [];
        foreach ($events as $event)
        {
            $data[] = [
                "event_id" => $event,
                "project_id" => $projectId,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_event")->insert($data);
        }
        return true;
    }

    public function connectGroups($groups, $projectId)
    {
        $groups = array_unique(array_filter($groups));
        // DELETE
        $this->database->table('project_group')->where('project_id', $projectId)->delete();
        // INSERT NEW
        $data = [];
        foreach ($groups as $group)
        {
            $data[] = [
                "group_id" => $group,
                "project_id" => $projectId,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_group")->insert($data);
        }
        return true;
    }

    public function connectLabels($labels, $projectId)
    {
        $labels = array_unique(array_filter($labels));
        // DELETE
        $this->database->table('project_label')->where('project_id', $projectId)->delete();
        // INSERT NEW
        $data = [];
        foreach ($labels as $label)
        {
            $data[] = [
                "label_id" => $label,
                "project_id" => $projectId,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_label")->insert($data);
        }
        return true;
    }

    public function connectNotes($notes, $projectId)
    {
        $notes = array_unique(array_filter($notes));
        // DELETE
        $this->database->table('project_note')->where('project_id', $projectId)->delete();
        // INSERT NEW
        $data = [];
        foreach ($notes as $note)
        {
            $data[] = [
                "note_id" => $note,
                "project_id" => $projectId,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_note")->insert($data);
        }
        return true;
    }

    public function connectPages($pages, $projectId)
    {
        $pages = array_unique(array_filter($pages));
        // DELETE
        $this->database->table('project_page')->where('project_id', $projectId)->delete();
        // INSERT NEW
        $data = [];
        foreach ($pages as $page)
        {
            $data[] = [
                "page_id" => $page,
                "project_id" => $projectId,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_page")->insert($data);
        }
        return true;
    }

    public function connectTodos($todos, $projectId)
    {
        $todos = array_unique(array_filter($todos));
        // DELETE
        $this->database->table('project_todo')->where('project_id', $projectId)->delete();
        // INSERT NEW
        $data = [];
        foreach ($todos as $todo)
        {
            $data[] = [
                "todo_id" => $todo,
                "project_id" => $projectId,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_todo")->insert($data);
        }
        return true;
    }

    public function connectCategories($categories, $projectId)
    {
        $categories = array_unique(array_filter($categories));
        // DELETE
        $this->database->table('project_category')->where('project_id', $projectId)->delete();
        // INSERT NEW
        $data = [];
        foreach ($categories as $category)
        {
            $data[] = [
                "category_id" => $category,
                "project_id" => $projectId,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("project_category")->insert($data);
        }
        return true;
    }

    public function addContactStar($contactId, $projectId)
    {
        return $this->database->table('project_user')->where('user_id', $contactId)->where('project_id', $projectId)->update(array('starred' => true));
    }

    public function removeContactStar($contactId, $projectId)
    {
        return $this->database->table('project_user')->where('user_id', $contactId)->where('project_id', $projectId)->update(array('starred' => false));
    }

    // CUD METHODS

    public function create($name, $description, $status, $modules, $image)
    {
        if (isset($image))
        {
            $fileId = $this->fileCRUDManager->createFromBase64($image, "", "/images/projects/");
            $fileId = (!$fileId) ? null : $fileId;
        }
        else
        {
            $fileId = null;
        }
        
        $result = $this->database->table('project')->insert(array(
            'name' => $name,
            'modules' => serialize($modules),
            'file_id' => $fileId,
            'status' => $status,
            'description' => $description,
            'created' => new \DateTime(),
        ));
        return $result;
    }

    public function delete($id)
    {
        $this->database->table('project_event')->where('project_id', $id)->delete();
        $this->database->table('project_note')->where('project_id', $id)->delete();
        $this->database->table('project_todo')->where('project_id', $id)->delete();
        $this->database->table('project_page')->where('project_id', $id)->delete();
        $this->database->table('project_group')->where('project_id', $id)->delete();
        $this->database->table('project_category')->where('project_id', $id)->delete();
        $this->database->table('project_team')->where('project_id', $id)->delete();
        $this->database->table('project_user')->where('project_id', $id)->delete();
        return $this->database->table('project')->where('id', $id)->delete();
    }

    public function update($projectId, $post)
    {
        if(!isset($post['users']))
            $post['users']=null;
        
        if(!isset($post['owners']))
            $post['owners']=null;
        
        if(!isset($post['managers']))
            $post['managers']=null;
        
        $this->connectUsers ($post['users'], $post['managers'], $post['owners'], $projectId);
        
        if(isset($post['categories']))
            $this->connectCategories ($post['categories'], $projectId);
        
        if(isset($post['events']))
            $this->connectEvents ($post['events'], $projectId);
        
        if(isset($post['groups']))
            $this->connectGroups ($post['groups'], $projectId);
        
        if(isset($post['labels']))
            $this->connectLabels ($post['labels'], $projectId);
        
        if(isset($post['notes']))
            $this->connectNotes ($post['notes'], $projectId);
        
        if(isset($post['pages']))
            $this->connectPages ($post['pages'], $projectId);
        
        if(isset($post['teams']))
            $this->connectTeams ($post['teams'], $projectId);
        
        if(isset($post['todos']))
            $this->connectTodos ($post['todos'], $projectId);
                
        if (isset($post['image']))
        {
            $fileId = $this->fileCRUDManager->createFromBase64($post['image'], "", "/images/projects/");
            $fileId = (!$fileId) ? null : $fileId;
        }
        else
            $fileId = null;
        
        unset($post['owners']);
        unset($post['managers']);
        unset($post['users']);
        unset($post['categories']);
        unset($post['events']);
        unset($post['groups']);
        unset($post['labels']);
        unset($post['notes']);
        unset($post['pages']);
        unset($post['teams']);
        unset($post['todos']);
        
        unset($post['id']);
        unset($post['created']);
        unset($post['image']);
        
        if($fileId != null)
            $post['file_id']=$fileId;
        
        if(isset($post['modules']))
            $post['modules']=serialize($post['modules']);
        
        return $this->database->table('project')->where('id', $projectId)->update($post);
    }

    public function updateChannelId($id, $channelId)
    {
        return $this->database->table('project')->where('id', $id)->update(array(
                    'channel_id' => $channelId,
        ));
    }

    public function deleteContact($userId, $id)
    {
        return $this->database->table('project_user')->where('project_id', $id)->where('user_id', $userId)->delete();
    }

}
