<?php

namespace Absolute\Module\Project\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Core\Presenter\BaseRestPresenter;

class TeamPresenter extends ProjectBasePresenter
{

    /** @var \Absolute\Module\Team\Manager\TeamManager @inject */
    public $teamManager;

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
                        $this->_getTeamRequest($resourceId, $subResourceId);
                    }
                    else
                    {
                        $this->_getTeamListRequest($resourceId);
                    }
                }
                break;
            case 'POST':
                $this->_postTeamRequest($resourceId, $subResourceId);
                break;
            case 'DELETE':
                $this->_deleteTeamRequest($resourceId, $subResourceId);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getTeamListRequest($idProject)
    {
        $ret = $this->teamManager->getProjectList($idProject);
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

    private function _getTeamRequest($projectId, $teamId)
    {
        $ret = $this->teamManager->getProjectItem($projectId, $teamId);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
        {
            $this->jsonResponse->payload = $ret->toJson();
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _postTeamRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->teamManager->teamProjectCreate($urlId, $urlId2);
        if (!$ret)
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        else
            $this->httpResponse->setCode(Response::S201_CREATED);
    }

    private function _deleteTeamRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->teamManager->teamProjectDelete($urlId, $urlId2);
        if (!$ret)
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        else
            $this->httpResponse->setCode(Response::S200_OK);
    }

}
