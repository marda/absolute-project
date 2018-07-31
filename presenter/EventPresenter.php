<?php

namespace Absolute\Module\Project\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Core\Presenter\BaseRestPresenter;

class EventPresenter extends ProjectBasePresenter
{

    /** @var \Absolute\Module\Event\Manager\EventManager @inject */
    public $eventManager;

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
                        $this->_getEventRequest($resourceId, $subResourceId);
                    }
                    else
                    {
                        $this->_getEventListRequest($resourceId);
                    }
                }
                break;
            case 'POST':
                $this->_postEventRequest($resourceId, $subResourceId);
                break;
            case 'DELETE':
                $this->_deleteEventRequest($resourceId, $subResourceId);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getEventListRequest($idProject)
    {
        $ret = $this->eventManager->getProjectList($idProject);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
        {
            $this->jsonResponse->payload = array_map(function($n)
            {
                return $n->toCalendarJson();
            }, $ret);
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _getEventRequest($projectId, $eventId)
    {
        $ret = $this->eventManager->getProjectItem($projectId, $eventId);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
        {
            $this->jsonResponse->payload = $ret->toCalendarJson();
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _postEventRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->eventManager->eventProjectCreate($urlId, $urlId2);
        if (!$ret)
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        else
            $this->httpResponse->setCode(Response::S201_CREATED);
    }

    private function _deleteEventRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->eventManager->eventProjectDelete($urlId, $urlId2);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
            $this->httpResponse->setCode(Response::S200_OK);
    }

}
