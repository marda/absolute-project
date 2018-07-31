<?php

namespace Absolute\Module\Project\Entity;

use Absolute\Core\Entity\BaseEntity;

class Project extends BaseEntity 
{

  private $id;
  private $name;
  private $modules;
  private $created;
  private $status;
  private $description;
  private $channelId;

  private $users = [];
  private $teams = [];
  private $categories = [];
  private $managers = [];
  private $owners = [];
  private $image = null;

	public function __construct($id, $name, $description, $status, $modules, $channelId, $created) 
  {
    $this->id = $id;
		$this->name = $name;
    $this->modules = unserialize($modules);
    $this->channelId = $channelId;
    $this->created = $created;
    $this->description = $description;
    $this->status = $status;
	}

  public function getId() 
  {
    return $this->id;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function getName() 
  {
    return $this->name;
  }

  public function getModules() 
  {
    return $this->modules;
  }

  public function getCreated() 
  {
    return $this->created;
  }

  public function getUsers() 
  {
    return $this->users;
  }

  public function getManagers() 
  {
    return $this->managers;
  }

  public function getOwners() 
  {
    return $this->owners;
  }

  public function getChannelId()
  {
    return $this->channelId;
  }

  public function getTeams()
  {
    return $this->teams;
  }

  public function getCategories()
  {
    return $this->categories;
  }

  public function getUsersAll()
  {
    return array_merge($this->owners, $this->managers, $this->users);
  }

  public function getImage() 
  {
    return $this->image;
  }

  // SETTERS

  public function setImage($image) 
  {
    $this->image = $image;
  }

  // IS?

  public function isModuleActivated($module) 
  {
    if (in_array($module, $this->modules)) 
    {
      return true;
    }
    return false;
  }

  public function isUserOwner($userId)
  {
    if (array_key_exists($userId, $this->owners)) 
    {
      return true;
    }
    return false;
  }

  public function isUserManager($userId) 
  {
    if (array_key_exists($userId, $this->owners) || array_key_exists($userId, $this->managers)) 
    {
      return true;
    }
    return false;
  }

  public function isUserInProject($userId) 
  {
    if (array_key_exists($userId, $this->owners) || array_key_exists($userId, $this->managers) || array_key_exists($userId, $this->users)) 
    {
      return true;
    }
    return false;
  }

  // ADDERS

  public function addUser($user) 
  {
    $this->users[$user->getId()] = $user;
  }

  public function addTeam($team) 
  {
    $this->teams[$team->getId()] = $team;
  }

  public function addCategory($category) 
  {
    $this->categories[$category->getId()] = $category;
  }

  public function addManager($user) 
  {
    $this->managers[$user->getId()] = $user;
  }

  public function addOwner($user) 
  {
    $this->owners[$user->getId()] = $user;
  }

  // OTHER METHODS  

  public function toJson() 
  {
    return array(
      "id" => $this->id,
      "name" => $this->name,
      "status" => $this->status,
    );
  }

  public function toJsonString() 
  {
    return json_encode(array(
      "id" => $this->id,
      "name" => $this->name,
      "status" => $this->status,
      "default" => $this->default,
    ));
  }
}

