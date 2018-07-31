<?php

namespace Absolute\Module\Project\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Module\Project\Presenter\ProjectBasePresenter;

class DefaultPresenter extends ProjectBasePresenter
{

    /** @var \Absolute\Module\Project\Manager\ProjectCRUDManager @inject */
    public $projectCRUDManager;

    /** @var \Absolute\Module\Project\Manager\ProjectManager @inject */
    public $projectManager;

    public function startup()
    {
        parent::startup();
    }

    public function renderDefault($resourceId)
    {
        switch ($this->httpRequest->getMethod())
        {
            case 'GET':
                if ($resourceId != null)
                    $this->_getRequest($resourceId);
                else
                    $this->_getListRequest();
                break;
            case 'POST':
                $this->_postRequest($resourceId);
                break;
            case 'PUT':
                $this->_putRequest($resourceId);
                break;
            case 'DELETE':
                $this->_deleteRequest($resourceId);
            default:

                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getRequest($id)
    {
        $project = $this->projectManager->getById($id);
        if (!$project)
        {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            return;
        }
        $this->jsonResponse->payload = $project->toJson();
        $this->httpResponse->setCode(Response::S200_OK);
    }

    private function _getListRequest()
    {
        $projects = $this->projectManager->getList();
        $this->httpResponse->setCode(Response::S200_OK);

        $this->jsonResponse->payload = array_map(function($n)
        {
            return $n->toJson();
        }, $projects);
    }

    private function _putRequest($id)
    {
        if (!isset($id))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $post = json_decode($this->httpRequest->getRawBody(), true);
        $this->jsonResponse->payload = [];
        $ret = $this->projectCRUDManager->update($id, $post);
        if (!$ret)
        {
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        }
        else
        {
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _postRequest()
    {
        $post = json_decode($this->httpRequest->getRawBody());
        $ret = $this->projectCRUDManager->create($post->name, $post->description, $post->status, $post->modules, $post->image);
        if (!$ret)
        {
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        }
        else
        {
            $this->httpResponse->setCode(Response::S201_CREATED);
        }
    }

    private function _deleteRequest($id)
    {
        if (!isset($id))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $this->projectCRUDManager->delete($id);
        $this->httpResponse->setCode(Response::S200_OK);
    }

}
