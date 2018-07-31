<?php

namespace Absolute\Module\Project\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Core\Presenter\BaseRestPresenter;

class UserPresenter extends ProjectBasePresenter
{

    /** @var \Absolute\Module\User\Manager\UserManager @inject */
    public $userManager;

    /** @var \Absolute\Module\Project\Manager\ProjectManager @inject */
    public $projectManager;

    public function startup()
    {
        parent::startup();
    }

    //LABEL

    public function renderDefault($resourceId, $subResourceId)
    {
        switch ($this->httpRequest->getMethod())
        {
            case 'GET':
                if (!isset($resourceId))
                    $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
                else
                {
                    if (isset($subResourceId))
                        $this->_getUserRequest($resourceId, $subResourceId);
                    else
                        $this->_getUserListRequest($resourceId);
                }
                break;
            case 'PUT':
                $this->_putUserRequest($resourceId, $subResourceId);
                break;
            case 'POST':
                $this->_postUserRequest($resourceId, $subResourceId);
                break;
            case 'DELETE':
                $this->_deleteUserRequest($resourceId, $subResourceId);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getUserListRequest($idProject)
    {
        $ret = $this->userManager->getProjectList($idProject);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
        {
            $this->jsonResponse->payload = array_map(function($n)
            {
                return $n->toJson();
            }, $ret);
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _getUserRequest($projectId, $userId)
    {
        $ret = $this->userManager->getProjectItem($projectId, $userId);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
        {
            $this->jsonResponse->payload = $ret->toJson();
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _putUserRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }

        $post = json_decode($this->httpRequest->getRawBody(), true);
        $ret = $this->userManager->userProjectUpdate($urlId, $urlId2, $post);
        if (!$ret)
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        else
            $this->httpResponse->setCode(Response::S201_CREATED);
    }

    private function _postUserRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $post = json_decode($this->httpRequest->getRawBody());
        $ret = $this->userManager->userProjectCreate($urlId, $urlId2, $post->role, $post->starred);
        if (!$ret)
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        else
            $this->httpResponse->setCode(Response::S201_CREATED);
    }

    private function _deleteUserRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->userManager->userProjectDelete($urlId, $urlId2);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
            $this->httpResponse->setCode(Response::S200_OK);
    }

}
