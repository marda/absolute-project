<?php

namespace Absolute\Module\Project\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Core\Presenter\BaseRestPresenter;

class TodoPresenter extends ProjectBasePresenter
{

    /** @var \Absolute\Module\Todo\Manager\TodoManager @inject */
    public $todoManager;

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
                    {
                        $this->_getTodoRequest($resourceId, $subResourceId);
                    }
                    else
                    {
                        $this->_getTodoListRequest($resourceId);
                    }
                }
                break;
            case 'POST':
                $this->_postTodoRequest($resourceId, $subResourceId);
                break;
            case 'DELETE':
                $this->_deleteTodoRequest($resourceId, $subResourceId);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getTodoListRequest($idProject)
    {
        $ret = $this->todoManager->getProjectList($idProject);
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

    private function _getTodoRequest($projectId, $todoId)
    {
        $ret = $this->todoManager->getProjectItem($projectId, $todoId);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
        {
            $this->jsonResponse->payload = $ret->toJson();
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _postTodoRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->todoManager->todoProjectCreate($urlId, $urlId2);
        if (!$ret)
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        else
            $this->httpResponse->setCode(Response::S201_CREATED);
    }

    private function _deleteTodoRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->todoManager->todoProjectDelete($urlId, $urlId2);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
            $this->httpResponse->setCode(Response::S200_OK);
    }

}
